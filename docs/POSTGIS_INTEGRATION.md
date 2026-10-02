# Integração PostGIS - INUTO GTerra

## Visão Geral

Este documento descreve a integração do PostGIS para funcionalidades geográficas avançadas na plataforma INUTO GTerra.

## Pré-requisitos

- PostgreSQL 14+ com extensão PostGIS 3.3+
- Laravel 12 com suporte a PostgreSQL
- Pacote PHP `phppgadmin` ou `pgAdmin` para administração

## Configuração

### 1. Instalação da Extensão PostGIS

```sql
-- Criar extensão PostGIS
CREATE EXTENSION IF NOT EXISTS postgis;
CREATE EXTENSION IF NOT EXISTS postgis_topology;

-- Verificar instalação
SELECT PostGIS_Version();
```

### 2. Configuração Laravel (.env)

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=gterra_nacional
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### 3. Instalação do Driver PostgreSQL

```bash
composer require doctrine/dbal
```

## Migrações com PostGIS

### Exemplo de Migration com Geometria

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('map_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('map_layer_id')->constrained('map_layers')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            
            // Campo geométrico PostGIS
            $table->geometry('geometry', 'GEOMETRY', 4326)->nullable();
            
            // Campos adicionais
            $table->json('properties')->nullable();
            $table->timestamps();
            
            // Índice espacial
            $table->spatialIndex('geometry');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('map_features');
    }
};
```

## Operações Geográficas

### 1. Inserir Geometria

```php
use Illuminate\Support\Facades\DB;

// Inserir ponto
DB::table('map_features')->insert([
    'name' => 'Ponto de Interesse',
    'geometry' => DB::raw("ST_GeomFromText('POINT(-8.8395 13.2359)', 4326)"),
]);

// Inserir polígono
DB::table('map_features')->insert([
    'name' => 'Área de Interesse',
    'geometry' => DB::raw("ST_GeomFromText('POLYGON((-8.8395 13.2359, -8.8400 13.2360, -8.8405 13.2355, -8.8395 13.2359))', 4326)"),
]);
```

### 2. Consultas Espaciais

```php
// Buscar features dentro de um raio
$features = DB::table('map_features')
    ->whereRaw("ST_DWithin(geometry, ST_GeomFromText('POINT(-8.8395 13.2359)', 4326), 1000)")
    ->get();

// Buscar features que intersectam uma área
$features = DB::table('map_features')
    ->whereRaw("ST_Intersects(geometry, ST_GeomFromText('POLYGON((...))', 4326))")
    ->get();

// Calcular área de um polígono
$area = DB::table('map_features')
    ->selectRaw("ST_Area(geometry::geography) as area_m2")
    ->where('id', $id)
    ->first();
```

### 3. Funções Geográficas Úteis

```php
// Converter para GeoJSON
$geojson = DB::table('map_features')
    ->selectRaw("ST_AsGeoJSON(geometry) as geojson")
    ->where('id', $id)
    ->first();

// Buffer ao redor de uma geometria
$buffered = DB::table('map_features')
    ->selectRaw("ST_Buffer(geometry, 0.01) as buffered")
    ->where('id', $id)
    ->first();

// Centroide de uma geometria
$centroid = DB::table('map_features')
    ->selectRaw("ST_Centroid(geometry) as centroid")
    ->where('id', $id)
    ->first();
```

## Integração com OpenLayers

### 1. API Endpoint para GeoJSON

```php
// routes/api.php
Route::get('/map-features/{layer}/geojson', function ($layer) {
    $features = DB::table('map_features')
        ->where('map_layer_id', $layer)
        ->selectRaw("id, name, ST_AsGeoJSON(geometry) as geometry, properties")
        ->get();

    $geojson = [
        'type' => 'FeatureCollection',
        'features' => $features->map(function ($feature) {
            return [
                'type' => 'Feature',
                'geometry' => json_decode($feature->geometry),
                'properties' => array_merge(
                    json_decode($feature->properties, true),
                    ['id' => $feature->id, 'name' => $feature->name]
                ),
            ];
        })->toArray(),
    ];

    return response()->json($geojson);
});
```

### 2. Integração OpenLayers

```javascript
// resources/js/map.js
import Map from 'ol/Map';
import View from 'ol/View';
import TileLayer from 'ol/layer/Tile';
import VectorLayer from 'ol/layer/Vector';
import VectorSource from 'ol/source/Vector';
import GeoJSON from 'ol/format/GeoJSON';

const map = new Map({
    target: 'map',
    layers: [
        new TileLayer({
            source: new OSM()
        })
    ],
    view: new View({
        center: [0, 0],
        zoom: 2
    })
});

// Carregar features do servidor
fetch('/api/map-features/1/geojson')
    .then(response => response.json())
    .then(data => {
        const vectorSource = new VectorSource({
            features: new GeoJSON().readFeatures(data)
        });

        const vectorLayer = new VectorLayer({
            source: vectorSource
        });

        map.addLayer(vectorLayer);
    });
```

## Modelos Eloquent com PostGIS

### Trait para Operações Geográficas

```php
<?php

namespace App\Traits;

trait HasGeometry
{
    public function scopeWithinRadius($query, $longitude, $latitude, $radiusMeters)
    {
        return $query->whereRaw(
            "ST_DWithin(geometry, ST_GeomFromText('POINT(? ?)', 4326), ?)",
            [$longitude, $latitude, $radiusMeters]
        );
    }

    public function scopeIntersects($query, $wktGeometry)
    {
        return $query->whereRaw(
            "ST_Intersects(geometry, ST_GeomFromText('?', 4326))",
            [$wktGeometry]
        );
    }

    public function getAreaAttribute()
    {
        return DB::table($this->getTable())
            ->selectRaw("ST_Area(geometry::geography) as area")
            ->where('id', $this->id)
            ->first()
            ->area;
    }

    public function getCentroidAttribute()
    {
        return DB::table($this->getTable())
            ->selectRaw("ST_AsGeoJSON(ST_Centroid(geometry)) as centroid")
            ->where('id', $this->id)
            ->first()
            ->centroid;
    }
}
```

## Performance e Otimização

### 1. Índices Espaciais

```php
// Criar índice espacial
Schema::table('map_features', function (Blueprint $table) {
    $table->spatialIndex('geometry');
});
```

### 2. Materialized Views

```sql
-- Criar materialized view para consultas frequentes
CREATE MATERIALIZED VIEW mv_features_summary AS
SELECT 
    map_layer_id,
    COUNT(*) as total_features,
    ST_Union(geometry) as union_geometry
FROM map_features
GROUP BY map_layer_id;

-- Atualizar materialized view
REFRESH MATERIALIZED VIEW mv_features_summary;
```

## Backup e Restauração

### Backup com PostGIS

```bash
pg_dump -h localhost -U postgres -F c -b -v -f gterra_backup.dump gterra_nacional
```

### Restauração

```bash
pg_restore -h localhost -U postgres -d gterra_nacional -v gterra_backup.dump
```

## Considerações de Segurança

1. **Validação de Geometria:** Sempre validar geometrias antes de inserir
2. **Permissões:** Limitar acesso a funções espaciais sensíveis
3. **Sanitização:** Sanitizar inputs WKT para evitar injeção SQL

## Troubleshooting

### Erro: extension "postgis" does not exist

```sql
-- Verificar se a extensão está disponível
SELECT * FROM pg_available_extensions WHERE name = 'postgis';

-- Instalar se necessário
CREATE EXTENSION postgis;
```

### Erro: function st_geomfromtext does not exist

```sql
-- Verificar se a extensão está instalada no schema correto
SELECT * FROM pg_extension WHERE extname = 'postgis';

-- Reinstalar se necessário
DROP EXTENSION IF EXISTS postgis;
CREATE EXTENSION postgis;
```

## Próximos Passos

1. Migrar dados existentes para PostGIS
2. Implementar validações de geometria
3. Criar endpoints REST para operações espaciais
4. Otimizar consultas espaciais
5. Implementar cache para consultas frequentes

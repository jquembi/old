<div class="w-full" wire:ignore>
    {{-- Map --}}
    <div id="mapa-huila" class="w-full relative" style="height: 520px;">
        {{-- Info panel (hover) --}}
        <div id="mapa-info-panel" style="display:none; position:absolute; top:12px; right:12px; z-index:1000; width:260px; pointer-events:none;">
            <div class="bg-white/95 dark:bg-slate-800/95 backdrop-blur-sm rounded-xl border border-slate-200 dark:border-slate-700/60 shadow-xl overflow-hidden">
                <div id="mapa-info-header" class="px-4 py-2.5 border-b border-slate-200 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-700/30">
                    <p id="mapa-info-title" class="text-sm font-bold text-slate-800 dark:text-white"></p>
                    <p id="mapa-info-subtitle" class="text-[10px] text-slate-400 mt-0.5"></p>
                </div>
                <div id="mapa-info-body" class="p-4 space-y-2.5">
                </div>
            </div>
        </div>
    </div>

    {{-- Legend + Stats bar --}}
    <div class="flex flex-wrap items-center justify-between gap-4 px-6 py-3 bg-slate-50 dark:bg-slate-700/40 border-t border-slate-200 dark:border-slate-700/60">
        <div class="flex flex-wrap items-center gap-4 text-xs">
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-500/30 inline-block border border-blue-500"></span> Limites Municipais</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span> Legal</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span> Ilegal</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500 inline-block"></span> Em Conflito</span>
        </div>
        <div class="flex items-center gap-6 text-xs text-slate-500 dark:text-slate-400">
            <span><strong class="text-slate-700 dark:text-slate-200">{{ count($municipios) }}</strong> municipios</span>
            <span><strong class="text-slate-700 dark:text-slate-200">{{ count($croquis) }}</strong> croquis</span>
            <span>Passe o mouse sobre uma area para detalhes</span>
        </div>
    </div>
</div>

@script
<script>
    const mapEl = document.getElementById('mapa-huila');
    if (mapEl && !mapEl._leaflet_id) {
        const map = L.map('mapa-huila', { zoomControl: false }).setView($wire.mapCenter, $wire.mapZoom);

        L.control.zoom({ position: 'topright' }).addTo(map);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap',
            maxZoom: 19,
            minZoom: 6
        }).addTo(map);

        L.control.scale({ imperial: false, position: 'bottomleft' }).addTo(map);

        const condicaoColors = { 'legal': '#10b981', 'ilegal': '#ef4444', 'em_conflito': '#f59e0b' };
        const condicaoLabels = { 'legal': 'Legal', 'ilegal': 'Ilegal', 'em_conflito': 'Em Conflito' };

        // Color palette for municipalities
        const munColors = ['#3b82f6', '#8b5cf6', '#06b6d4', '#f97316', '#ec4899', '#14b8a6', '#6366f1', '#84cc16'];

        // Panel elements
        const panel = document.getElementById('mapa-info-panel');
        const infoTitle = document.getElementById('mapa-info-title');
        const infoSubtitle = document.getElementById('mapa-info-subtitle');
        const infoBody = document.getElementById('mapa-info-body');

        function showMunicipioInfo(m, color) {
            infoTitle.textContent = m.nome;
            infoSubtitle.textContent = m.sede ? 'Sede: ' + m.sede : 'Municipio';
            infoBody.innerHTML =
                '<div class="grid grid-cols-2 gap-2">' +
                    infoStat('Populacao', (m.populacao || 0).toLocaleString('pt-BR'), 'hab.') +
                    infoStat('Area', m.area_km2 ? m.area_km2.toLocaleString('pt-BR') : '—', 'km²') +
                    infoStat('Croquis', m.croquis_count || 0) +
                    infoStat('Terrenos Oc.', m.terrenos_ociosos_count || 0) +
                    infoStat('Instrumentos', m.instrumentos_count || 0) +
                    infoStat('Pareceres', m.pareceres_count || 0) +
                '</div>';
            document.getElementById('mapa-info-header').style.borderLeftColor = color;
            document.getElementById('mapa-info-header').style.borderLeft = '3px solid ' + color;
            panel.style.display = 'block';
        }

        function showCroquiInfo(c) {
            const color = condicaoColors[c.condicao] || '#94a3b8';
            infoTitle.textContent = c.proprietario;
            infoSubtitle.textContent = c.municipio_nome + (c.numero_registro ? ' · ' + c.numero_registro : '');
            const tipo = c.tipo_geometria === 'Point' ? 'Ponto' : 'Terreno/Lote';
            let body = '<div class="grid grid-cols-2 gap-2">' +
                infoStat('Tipo', tipo) +
                infoStat('Condicao', condicaoLabels[c.condicao] || c.condicao);
            if (c.area_m2) body += infoStat('Area', c.area_m2.toLocaleString('pt-BR', {maximumFractionDigits:1}), 'm²');
            if (c.endereco) body += infoStat('Endereco', c.endereco);
            body += '</div>';
            infoBody.innerHTML = body;
            document.getElementById('mapa-info-header').style.borderLeft = '3px solid ' + color;
            panel.style.display = 'block';
        }

        function hideInfo() {
            panel.style.display = 'none';
        }

        function infoStat(label, value, unit) {
            return '<div class="bg-slate-50 dark:bg-slate-700/40 rounded-lg px-2.5 py-2 text-center">' +
                '<p class="text-[9px] text-slate-400 uppercase tracking-wider mb-0.5">' + label + '</p>' +
                '<p class="text-xs font-bold text-slate-700 dark:text-white">' + value + (unit ? ' <span class="font-normal text-slate-400">' + unit + '</span>' : '') + '</p>' +
            '</div>';
        }

        // ===== MUNICIPALITY BOUNDARIES (polygons) =====
        const municipios = $wire.municipios;
        const boundaryLayer = L.layerGroup();
        const municipioMarkers = L.layerGroup();

        municipios.forEach(function(m, idx) {
            const color = munColors[idx % munColors.length];

            // Render boundary polygon if available
            if (m.boundary && m.boundary.coordinates) {
                const latlngs = m.boundary.coordinates[0].map(c => [c[1], c[0]]);
                const poly = L.polygon(latlngs, {
                    color: color,
                    weight: 2,
                    fillColor: color,
                    fillOpacity: 0.08,
                    dashArray: '6 4',
                    className: 'mun-boundary'
                });

                poly.on('mouseover', function(e) {
                    this.setStyle({ fillOpacity: 0.25, weight: 3, dashArray: null });
                    showMunicipioInfo(m, color);
                });
                poly.on('mouseout', function(e) {
                    this.setStyle({ fillOpacity: 0.08, weight: 2, dashArray: '6 4' });
                    hideInfo();
                });

                poly.addTo(boundaryLayer);
            }

            // Seat marker (point)
            if (m.geometry?.coordinates) {
                const marker = L.circleMarker([m.geometry.coordinates[1], m.geometry.coordinates[0]], {
                    radius: 7, fillColor: color, color: '#fff', weight: 2, opacity: 1, fillOpacity: .9
                });

                // Tooltip always visible for municipality name
                marker.bindTooltip(m.nome, {
                    permanent: true,
                    direction: 'right',
                    offset: [10, 0],
                    className: 'mun-label'
                });

                marker.on('mouseover', function() { showMunicipioInfo(m, color); });
                marker.on('mouseout', function() { hideInfo(); });

                marker.addTo(municipioMarkers);
            }
        });
        boundaryLayer.addTo(map);
        municipioMarkers.addTo(map);

        // ===== CROQUIS =====
        const croquis = $wire.croquis;
        const croquisLayer = L.layerGroup();

        croquis.forEach(function(c) {
            if (!c.geometry) return;
            const color = condicaoColors[c.condicao] || '#94a3b8';

            let layer;
            if (c.geometry.type === 'Point' && c.geometry.coordinates) {
                layer = L.circleMarker([c.geometry.coordinates[1], c.geometry.coordinates[0]], {
                    radius: 5, fillColor: color, color: '#fff', weight: 1.5, opacity: 1, fillOpacity: .85
                });
            } else if (c.geometry.type === 'Polygon' && c.geometry.coordinates) {
                const latlngs = c.geometry.coordinates[0].map(coord => [coord[1], coord[0]]);
                layer = L.polygon(latlngs, {
                    color: color, weight: 2, fillColor: color, fillOpacity: 0.2
                });
            }

            if (layer) {
                layer.on('mouseover', function(e) {
                    if (this.setStyle) this.setStyle({ fillOpacity: 0.5, weight: 3 });
                    showCroquiInfo(c);
                });
                layer.on('mouseout', function(e) {
                    if (c.geometry.type === 'Point') {
                        if (this.setStyle) this.setStyle({ fillOpacity: 0.85, weight: 1.5 });
                    } else {
                        if (this.setStyle) this.setStyle({ fillOpacity: 0.2, weight: 2 });
                    }
                    hideInfo();
                });
                layer.addTo(croquisLayer);
            }
        });
        croquisLayer.addTo(map);

        // Layer control
        L.control.layers(null, {
            'Limites Municipais': boundaryLayer,
            'Sedes': municipioMarkers,
            'Croquis': croquisLayer
        }, { position: 'topleft', collapsed: true }).addTo(map);

        // Fit bounds
        const allPoints = [];
        municipios.forEach(m => {
            if (m.boundary?.coordinates) {
                m.boundary.coordinates[0].forEach(c => allPoints.push([c[1], c[0]]));
            } else if (m.geometry?.coordinates) {
                allPoints.push([m.geometry.coordinates[1], m.geometry.coordinates[0]]);
            }
        });
        croquis.forEach(c => {
            if (!c.geometry?.coordinates) return;
            if (c.geometry.type === 'Point') {
                allPoints.push([c.geometry.coordinates[1], c.geometry.coordinates[0]]);
            } else if (c.geometry.type === 'Polygon') {
                c.geometry.coordinates[0].forEach(coord => allPoints.push([coord[1], coord[0]]));
            }
        });
        if (allPoints.length) map.fitBounds(L.latLngBounds(allPoints), { padding: [40, 40] });
    }
</script>
@endscript

@push('styles')
<style>
    .mun-label {
        background: rgba(255,255,255,.85) !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 6px !important;
        padding: 2px 8px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        color: #334155 !important;
        box-shadow: 0 1px 4px rgba(0,0,0,.08) !important;
    }
    .mun-label::before { display: none !important; }
</style>
@endpush

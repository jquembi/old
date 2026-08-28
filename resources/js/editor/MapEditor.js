/**
 * MapEditor.js — Editor GIS Colaborativo em Tempo Real
 *
 * Editor de mapa completo com:
 * - Desenho de polígonos, linhas e pontos
 * - Selecção, modificação e eliminação de features
 * - Medição de área e comprimento
 * - Auto-save imediato a cada operação
 * - Sincronização em tempo real via polling (3s)
 * - Painel de propriedades inline
 * - Exportação GeoJSON
 */

import OlMap        from 'ol/Map';
import View         from 'ol/View';
import TileLayer    from 'ol/layer/Tile';
import VectorLayer  from 'ol/layer/Vector';
import VectorSource from 'ol/source/Vector';
import XYZ          from 'ol/source/XYZ';
import OSM          from 'ol/source/OSM';
import GeoJSON      from 'ol/format/GeoJSON';
import { fromLonLat, toLonLat, getPointResolution } from 'ol/proj';
import { Style, Fill, Stroke, Circle as CircleStyle, Text as TextStyle } from 'ol/style';
import { getArea, getLength } from 'ol/sphere';
import Feature      from 'ol/Feature';
import { fromExtent } from 'ol/geom/Polygon';

import { Toolbar }       from './Toolbar.js';
import { LayerPanel }    from './LayerPanel.js';
import { PropertyPanel } from './PropertyPanel.js';
import { ContextMenu }   from './ContextMenu.js';
import { ExportManager } from './ExportManager.js';
import { alertModal }    from './ModalManager.js';
import {
    fetchLayers,
    fetchFeatures,
    ensureLayer,
    createFeature,
    updateFeature,
    deleteFeature,
    syncFeatures,
    fetchMunicipioInfo,
    exportGeoJson as apiExportGeoJson,
} from './GeometryService.js';

// Patch canvas getContext to set willReadFrequently for 2d contexts (OL hit-detection)
if (!HTMLCanvasElement.prototype.__origGetContext) {
    HTMLCanvasElement.prototype.__origGetContext = HTMLCanvasElement.prototype.getContext;
    HTMLCanvasElement.prototype.getContext = function (type, attrs) {
        if (type === '2d') {
            attrs = Object.assign({ willReadFrequently: true }, attrs);
        }
        return this.__origGetContext(type, attrs);
    };
}

export class MapEditor {
    constructor(options = {}) {
        this.municipioId   = options.municipioId;
        this.tipo          = options.tipo ?? 'terrenos_ociosos';
        this.readonly      = options.readonly ?? false;
        this.syncInterval  = options.syncInterval ?? 3000;
        this.municipioNome = options.municipioNome ?? '';
        this.provinciaNome = options.provinciaNome ?? '';
        this.mapEl         = document.getElementById(options.container ?? 'map-editor');
        this.activeLayerId = null;
        this.municipioInfo = null;   // fetched from backend
        this._municipioBbox = null;  // EPSG:3857 extent

        this.layers       = [];
        this.vectorLayers = new Map();

        this._VectorLayer  = VectorLayer;
        this._VectorSource = VectorSource;

        this.vectorSource = new VectorSource();
        this.vectorSource.on('addfeature',    () => this._updateCountBadge());
        this.vectorSource.on('removefeature', () => this._updateCountBadge());
        this._knownFeatureIds = new Set();
        this._lastSyncTime = null;
        this._syncTimer    = null;
        this._destroyed    = false;
        this._saving       = false;

        this._geoFormat = new GeoJSON({
            dataProjection: 'EPSG:4326',
            featureProjection: 'EPSG:3857',
        });

        this._waitForContainer().then(() => {
            this._initMap();
            this._initPanels();
            this._loadData();
        });
    }

    /**
     * Aguarda até que o container do mapa tenha dimensões > 0.
     * Com position:fixed no root, o container tem dimensões imediatamente
     * após o primeiro frame de layout.
     */
    _waitForContainer() {
        return new Promise((resolve) => {
            const check = () => this.mapEl && this.mapEl.offsetWidth > 0 && this.mapEl.offsetHeight > 0;

            if (check()) { resolve(); return; }

            // Espera o primeiro frame de layout
            requestAnimationFrame(() => {
                if (check()) { resolve(); return; }

                // Fallback: observar redimensionamento
                const ro = new ResizeObserver((entries) => {
                    for (const entry of entries) {
                        if (entry.contentRect.width > 0 && entry.contentRect.height > 0) {
                            ro.disconnect();
                            resolve();
                            return;
                        }
                    }
                });
                ro.observe(this.mapEl);

                // Safety timeout — resolve mesmo sem dimensões (edge case)
                setTimeout(() => {
                    ro.disconnect();
                    resolve();
                }, 2000);
            });
        });
    }

    // ═══════════════════════════════════════════════════════
    //  MAPA
    // ═══════════════════════════════════════════════════════

    _initMap() {
        const defaultCenter = fromLonLat([13.50, -14.92]);

        this._baseLayers = {
            satellite: new TileLayer({
                source: new XYZ({
                    url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                    maxZoom: 19,
                    attributions: 'Tiles © Esri',
                }),
            }),
            osm: new TileLayer({ source: new OSM(), visible: false }),
        };

        // Overlay de rótulos (fronteiras, cidades, estradas)
        this._labelsLayer = new TileLayer({
            source: new XYZ({
                url: 'https://{a-c}.basemaps.cartocdn.com/dark_only_labels/{z}/{x}/{y}@2x.png',
                maxZoom: 19,
            }),
            opacity: 0.85,
            zIndex: 50,
        });

        // Camada de limite do município
        this._boundarySource = new VectorSource();
        this._boundaryLayer = new VectorLayer({
            source: this._boundarySource,
            style: new Style({
                fill: new Fill({ color: 'rgba(59,130,246,0.04)' }),
                stroke: new Stroke({ color: '#3b82f6', width: 2, lineDash: [8, 6] }),
            }),
            zIndex: 40,
        });

        this._activeVectorLayer = new VectorLayer({
            source: this.vectorSource,
            style: (f) => this._featureStyle(f),
            zIndex: 100,
        });

        this.map = new OlMap({
            target: this.mapEl,
            pixelRatio: 1,
            layers: [
                this._baseLayers.satellite,
                this._baseLayers.osm,
                this._boundaryLayer,
                this._labelsLayer,
                this._activeVectorLayer,
            ],
            view: new View({
                projection: 'EPSG:3857',
                center: defaultCenter,
                zoom: 7,
                minZoom: 4,
                maxZoom: 20,
            }),
        });

        document.getElementById('btn-toggle-base')?.addEventListener('click', () => {
            const sat = this._baseLayers.satellite.getVisible();
            this._baseLayers.satellite.setVisible(!sat);
            this._baseLayers.osm.setVisible(sat);
            const btn = document.getElementById('btn-toggle-base');
            if (btn) btn.textContent = sat ? 'Satélite' : 'OSM';
        });
    }

    _initPanels() {
        this.toolbar       = this.readonly ? null : new Toolbar(this);
        this.layerPanel    = new LayerPanel(this);
        this.propertyPanel = new PropertyPanel(this);
        this.contextMenu   = new ContextMenu(this);
        this.exportManager = new ExportManager(this);

        if (!this.readonly && this.toolbar) {
            this.toolbar.activateTool('select');
        }

        // When sidebar changes active layer, update vectorSource reference
        document.addEventListener('layer:changed', (e) => {
            const layerId = e.detail?.layerId;
            if (layerId && this.vectorLayers.has(layerId)) {
                this.activeLayerId = layerId;
                this.vectorSource  = this.vectorLayers.get(layerId).getSource();
            }
        });

        // When sidebar adds a new layer, update counts
        document.addEventListener('layer:added', () => {
            this._updateCountBadge();
        });

        // Status bar — coordenadas (throttled)
        let _lastCoordsUpdate = 0;
        this.map.on('pointermove', (e) => {
            const now = performance.now();
            if (now - _lastCoordsUpdate < 50) return;  // ~20fps
            _lastCoordsUpdate = now;
            const el = document.getElementById('status-coords');
            if (!el) return;
            const [lon, lat] = toLonLat(e.coordinate);
            el.textContent = `Lon ${lon.toFixed(6)}  Lat ${lat.toFixed(6)}`;
        });

        // Status bar — zoom e escala
        const view = this.map.getView();
        const updateScaleZoom = () => {
            const zoom = view.getZoom();
            const zoomEl = document.getElementById('status-zoom');
            if (zoomEl) zoomEl.textContent = `Z ${zoom?.toFixed(1) ?? '—'}`;

            const scaleEl = document.getElementById('status-scale');
            if (scaleEl) {
                const center = view.getCenter();
                if (center) {
                    const mpu = getPointResolution(view.getProjection(), view.getResolution(), center);
                    const scale = Math.round(mpu * 39.37 * 96);
                    scaleEl.textContent = `1:${scale.toLocaleString('pt-BR')}`;
                }
            }
        };
        view.on('change:resolution', updateScaleZoom);
        view.on('change:center', updateScaleZoom);
    }

    // ═══════════════════════════════════════════════════════
    //  ESTILOS
    // ═══════════════════════════════════════════════════════

    _featureStyle(olFeature, cor) {
        if (!cor) {
            const layerId = olFeature.get('layer_id');
            const layer = layerId ? this.layers.find(l => l.id === layerId) : null;
            cor = layer?.cor ?? '#ef4444';
        }
        const selected = olFeature.get('_selected');
        return new Style({
            fill:   new Fill({ color: cor + (selected ? '60' : '35') }),
            stroke: new Stroke({ color: cor, width: selected ? 3.5 : 2 }),
            image:  new CircleStyle({
                radius: 7,
                fill: new Fill({ color: cor }),
                stroke: new Stroke({ color: '#fff', width: 2 }),
            }),
        });
    }

    _measureStyle() {
        return new Style({
            fill:   new Fill({ color: 'rgba(255,255,0,0.15)' }),
            stroke: new Stroke({ color: '#fbbf24', width: 2, lineDash: [6, 4] }),
        });
    }

    // ═══════════════════════════════════════════════════════
    //  CARREGAMENTO INICIAL
    // ═══════════════════════════════════════════════════════

    async _loadData() {
        this._setStatus('A carregar…');

        // Buscar informações geográficas do município
        try {
            this.municipioInfo = await fetchMunicipioInfo(this.municipioId);
            this._applyMunicipioBoundary();
        } catch {
            // sem informações — usa posição padrão
        }

        try {
            this.layers = await fetchLayers(this.municipioId);
        } catch {
            this.layers = [];
        }

        if (this.layers.length === 0 && !this.readonly) {
            try {
                const layer = await ensureLayer(this.municipioId, this.tipo);
                this.layers = [{ ...layer, features_count: 0 }];
            } catch (e) {
                console.error('Erro ao criar camada:', e);
            }
        }

        for (const layer of this.layers) {
            const src = new VectorSource();
            const vl  = new VectorLayer({
                source: src,
                style: (f) => this._featureStyle(f, layer.cor),
                visible: layer.visivel,
            });
            this.map.addLayer(vl);
            this.vectorLayers.set(layer.id, vl);

            // Sync sidebar counts when features change
            src.on('addfeature',    () => this._updateCountBadge());
            src.on('removefeature', () => this._updateCountBadge());
        }

        const def = this.layers.find(l => l.tipo === this.tipo) ?? this.layers[0];
        if (def) {
            this.activeLayerId = def.id;
            this.layerPanel.setActiveLayer(def.id);
        }

        this.layerPanel.render(this.layers);

        try {
            const fc = await fetchFeatures(this.municipioId, { tipo: this.tipo });
            this._loadFeatureCollection(fc, false);
            this._lastSyncTime = new Date().toISOString();
        } catch {
            // sem features
        }

        this._setStatus('Pronto');
        this._updateCountBadge();
        this._startSync();
    }

    _loadFeatureCollection(fc, merge = false) {
        if (!fc?.features?.length) return;

        for (const geoFeat of fc.features) {
            const fid = geoFeat.properties?.id;

            if (merge && fid && this._knownFeatureIds.has(fid)) {
                const existing = this._findOlFeatureById(fid);
                if (existing) {
                    const newGeom = this._geoFormat.readGeometry(geoFeat.geometry);
                    existing.setGeometry(newGeom);
                    existing.set('properties', geoFeat.properties);
                    existing.set('area_m2', geoFeat.properties?.area_m2);
                    existing.set('area_ha', geoFeat.properties?.area_ha);
                }
                continue;
            }

            const layerId = geoFeat.properties?.layer_id;
            const src = layerId ? this.vectorLayers.get(layerId)?.getSource() : this.vectorSource;
            if (!src) continue;

            const olFeature = this._geoFormat.readFeature(geoFeat);
            olFeature.set('id',              fid);
            olFeature.set('layer_id',        layerId);
            olFeature.set('area_m2',         geoFeat.properties?.area_m2);
            olFeature.set('area_ha',         geoFeat.properties?.area_ha);
            olFeature.set('criado_por_nome', geoFeat.properties?.criado_por_nome);
            olFeature.set('properties',      geoFeat.properties ?? {});
            src.addFeature(olFeature);

            if (fid) this._knownFeatureIds.add(fid);
        }

        if (!merge) this._fitToFeatures();
        this._updateCountBadge();
    }

    _findOlFeatureById(id) {
        for (const [, vl] of this.vectorLayers) {
            for (const f of vl.getSource().getFeatures()) {
                if (f.get('id') === id) return f;
            }
        }
        for (const f of this.vectorSource.getFeatures()) {
            if (f.get('id') === id) return f;
        }
        return null;
    }

    // ═══════════════════════════════════════════════════════
    //  LIMITE DO MUNICÍPIO
    // ═══════════════════════════════════════════════════════

    _applyMunicipioBoundary() {
        const info = this.municipioInfo;
        if (!info) return;

        const view = this.map.getView();

        // Centrar no município
        if (info.latitude && info.longitude) {
            view.setCenter(fromLonLat([info.longitude, info.latitude]));
            view.setZoom(info.zoom_padrao ?? 12);
        }

        // Bbox: [minLon, minLat, maxLon, maxLat]
        if (info.bbox && info.bbox.length === 4) {
            const [minLon, minLat, maxLon, maxLat] = info.bbox;
            const sw = fromLonLat([minLon, minLat]);
            const ne = fromLonLat([maxLon, maxLat]);
            this._municipioBbox = [sw[0], sw[1], ne[0], ne[1]];

            // Restringir vista ao bbox (com margem de 20%)
            const dx = (ne[0] - sw[0]) * 0.2;
            const dy = (ne[1] - sw[1]) * 0.2;
            view.setExtent([sw[0] - dx, sw[1] - dy, ne[0] + dx, ne[1] + dy]);

            // Mostrar rectângulo de limite
            const boundaryGeom = fromExtent(this._municipioBbox);
            const boundaryFeature = new Feature({ geometry: boundaryGeom });
            this._boundarySource.clear();
            this._boundarySource.addFeature(boundaryFeature);
        }
    }

    /**
     * Verifica se a geometria está dentro do limite do município.
     * Retorna true se estiver dentro (ou se não houver limite).
     */
    isWithinMunicipio(olFeature) {
        if (!this._municipioBbox) return true;
        const ext = olFeature.getGeometry().getExtent();
        const cx = (ext[0] + ext[2]) / 2;
        const cy = (ext[1] + ext[3]) / 2;
        const [x1, y1, x2, y2] = this._municipioBbox;
        return cx >= x1 && cx <= x2 && cy >= y1 && cy <= y2;
    }

    _fitToFeatures() {
        const all = [];
        this.vectorLayers.forEach(vl => all.push(...vl.getSource().getFeatures()));
        all.push(...this.vectorSource.getFeatures());
        if (all.length === 0) return;

        let ext = all[0].getGeometry().getExtent().slice();
        for (const f of all) {
            const fe = f.getGeometry().getExtent();
            if (fe[0] < ext[0]) ext[0] = fe[0];
            if (fe[1] < ext[1]) ext[1] = fe[1];
            if (fe[2] > ext[2]) ext[2] = fe[2];
            if (fe[3] > ext[3]) ext[3] = fe[3];
        }
        this.map.getView().fit(ext, { padding: [60, 60, 60, 60], maxZoom: 16 });
    }

    // ═══════════════════════════════════════════════════════
    //  SINCRONIZAÇÃO EM TEMPO REAL
    // ═══════════════════════════════════════════════════════

    _startSync() {
        if (this._destroyed || this.syncInterval <= 0) return;

        this._syncTimer = setInterval(async () => {
            if (this._destroyed || this._saving) return;
            try {
                const fc = await syncFeatures(this.municipioId, {
                    since: this._lastSyncTime,
                    tipo: this.tipo,
                });
                if (fc?.timestamp) this._lastSyncTime = fc.timestamp;
                if (fc?.features?.length > 0) {
                    this._loadFeatureCollection(fc, true);
                    this._showSyncToast(fc.features.length);
                }
            } catch {
                // silencioso
            }
        }, this.syncInterval);
    }

    _showSyncToast(count) {
        const el = document.getElementById('sync-toast');
        if (!el) return;
        el.textContent = `⟳ ${count} alteração(ões) sincronizada(s)`;
        el.classList.remove('hidden');
        el.classList.add('show');
        setTimeout(() => {
            el.classList.remove('show');
            el.classList.add('hidden');
        }, 3000);
    }

    // ═══════════════════════════════════════════════════════
    //  AUTO-SAVE
    // ═══════════════════════════════════════════════════════

    async persistNewFeature(olFeature) {
        if (this.readonly || !this.activeLayerId) return;

        // Validar se a feature está dentro do município
        if (!this.isWithinMunicipio(olFeature)) {
            alertModal({
                title: 'Fora do limite',
                message: `Não é possível criar elementos fora do município de ${this.municipioNome}.`,
                variant: 'danger',
            });
            this.removeFeature(olFeature);
            return;
        }

        this._saving = true;
        this._setStatus('A guardar…', 'saving');

        const geometry = JSON.parse(this._geoFormat.writeGeometry(olFeature.getGeometry()));
        const geomType = olFeature.getGeometry().getType();

        let area_m2 = null;
        if (geomType === 'Polygon' || geomType === 'MultiPolygon') {
            area_m2 = getArea(olFeature.getGeometry());
        }

        const ext    = olFeature.getGeometry().getExtent();
        const center = toLonLat([(ext[0] + ext[2]) / 2, (ext[1] + ext[3]) / 2]);
        const centroid = { lon: center[0], lat: center[1] };

        try {
            const saved = await createFeature(this.municipioId, {
                layer_id: this.activeLayerId,
                geometry,
                area_m2,
                properties: {},
            });

            olFeature.set('id',         saved.properties?.id);
            olFeature.set('layer_id',   this.activeLayerId);
            olFeature.set('area_m2',    saved.properties?.area_m2);
            olFeature.set('area_ha',    saved.properties?.area_ha);
            olFeature.set('properties', saved.properties ?? {});

            if (saved.properties?.id) this._knownFeatureIds.add(saved.properties.id);

            this._setStatus('✓ Guardado', 'saved');
            setTimeout(() => this._setStatus('Pronto'), 2000);
            this._updateCountBadge();

            const eventDetail = { ...saved, centroid };
            this.emit('feature:created', eventDetail);
            window.dispatchEvent(new CustomEvent('map:feature-created', { detail: eventDetail }));
        } catch (err) {
            console.error('Erro ao guardar:', err);
            this._setStatus('✗ Erro ao guardar', 'error');
            this.vectorSource.removeFeature(olFeature);
        } finally {
            this._saving = false;
        }
    }

    async persistFeatureUpdate(olFeature) {
        const geoId = olFeature.get('id');
        if (!geoId || this.readonly) return;

        this._setStatus('A guardar…', 'saving');

        const geometry = JSON.parse(this._geoFormat.writeGeometry(olFeature.getGeometry()));
        const geomType = olFeature.getGeometry().getType();

        let area_m2 = olFeature.get('area_m2');
        if (geomType === 'Polygon' || geomType === 'MultiPolygon') {
            area_m2 = getArea(olFeature.getGeometry());
            olFeature.set('area_m2', area_m2);
        }

        try {
            const updated = await updateFeature(geoId, { geometry, area_m2 });
            olFeature.set('properties', updated.properties ?? {});
            this._setStatus('✓ Guardado', 'saved');
            setTimeout(() => this._setStatus('Pronto'), 2000);
        } catch (err) {
            console.error('Erro ao actualizar:', err);
            this._setStatus('✗ Erro', 'error');
        }
    }

    async persistPropertyUpdate(featureId, properties) {
        this._setStatus('A guardar…', 'saving');
        try {
            const updated = await updateFeature(featureId, { properties });
            this._setStatus('✓ Guardado', 'saved');
            setTimeout(() => this._setStatus('Pronto'), 2000);
            return updated;
        } catch (err) {
            console.error('Erro propriedades:', err);
            this._setStatus('✗ Erro', 'error');
            throw err;
        }
    }

    async deleteFeatureById(olFeature) {
        const geoId = olFeature.get('id');
        this.removeFeature(olFeature);
        this._updateCountBadge();
        if (geoId) {
            this._knownFeatureIds.delete(geoId);
            try {
                await deleteFeature(geoId);
                this._setStatus('✓ Eliminado', 'saved');
                setTimeout(() => this._setStatus('Pronto'), 2000);
            } catch (err) {
                console.error('Erro ao eliminar:', err);
            }
        }
    }

    removeFeature(olFeature) {
        try { this.vectorSource.removeFeature(olFeature); } catch {}
        this.vectorLayers.forEach(vl => {
            try { vl.getSource().removeFeature(olFeature); } catch {}
        });
    }

    // ═══════════════════════════════════════════════════════
    //  EXPORTAÇÃO
    // ═══════════════════════════════════════════════════════

    async exportGeoJson() {
        try {
            const fc   = await apiExportGeoJson(this.municipioId);
            const blob = new Blob([JSON.stringify(fc, null, 2)], { type: 'application/geo+json' });
            const url  = URL.createObjectURL(blob);
            const a    = document.createElement('a');
            a.href     = url;
            a.download = `municipio_${this.municipioId}_terrenos.geojson`;
            a.click();
            URL.revokeObjectURL(url);
        } catch (e) {
            alertModal({ title: 'Erro ao exportar', message: e.message, variant: 'danger' });
        }
    }

    // ═══════════════════════════════════════════════════════
    //  UI HELPERS
    // ═══════════════════════════════════════════════════════

    _setStatus(text, type = '') {
        const el = document.getElementById('save-status');
        if (!el) return;
        el.textContent = text;
        el.className = 'save-status';
        if (type) el.classList.add(`save-status--${type}`);
    }

    _updateCountBadge() {
        let count = this.vectorSource.getFeatures().length;
        this.vectorLayers.forEach(vl => { count += vl.getSource().getFeatures().length; });
        const el = document.getElementById('feature-count');
        if (el) el.textContent = count;

        // Sync sidebar layer panel counts
        if (this.layerPanel) this.layerPanel.updateCounts();
    }

    // ═══════════════════════════════════════════════════════
    //  EVENTOS
    // ═══════════════════════════════════════════════════════

    _listeners = {};

    on(event, cb) {
        if (!this._listeners[event]) this._listeners[event] = [];
        this._listeners[event].push(cb);
        return this;
    }

    emit(event, data) {
        (this._listeners[event] ?? []).forEach(cb => cb(data));
    }

    // ═══════════════════════════════════════════════════════
    //  DESTRUIÇÃO
    // ═══════════════════════════════════════════════════════

    destroy() {
        this._destroyed = true;
        if (this._syncTimer) clearInterval(this._syncTimer);
        this.toolbar?._deactivateCurrent();
        this.map?.setTarget(null);
    }
}

/**
 * LayerPanel.js — Painel lateral de camadas sincronizado com o editor
 *
 * Funcionalidades:
 * - Lista de camadas com visibilidade, cor, contagem
 * - Adicionar novas camadas por tipo de terreno
 * - Toggle, renomear, mudar cor
 * - Contador de features em tempo real
 * - Indicador de camada activa
 * - Filtro de camadas
 */
import { updateLayer, ensureLayer } from './GeometryService.js';

// Tipos de terreno disponíveis (espelhados do backend)
const TERRAIN_TYPES = {
    terrenos_ociosos:    { nome: 'Terrenos Ociosos',         cor: '#ef4444', icon: 'triangle' },
    loteamentos:         { nome: 'Loteamentos',              cor: '#3b82f6', icon: 'grid' },
    croquis:             { nome: 'Croquis',                  cor: '#10b981', icon: 'map' },
    areas_verdes:        { nome: 'Áreas Verdes',             cor: '#22c55e', icon: 'tree' },
    areas_protegidas:    { nome: 'Áreas Protegidas',         cor: '#16a34a', icon: 'shield' },
    zona_industrial:     { nome: 'Zona Industrial',          cor: '#f59e0b', icon: 'factory' },
    zona_residencial:    { nome: 'Zona Residencial',         cor: '#8b5cf6', icon: 'home' },
    zona_comercial:      { nome: 'Zona Comercial',           cor: '#ec4899', icon: 'shop' },
    infraestrutura:      { nome: 'Infraestrutura',           cor: '#06b6d4', icon: 'road' },
    zona_agricola:       { nome: 'Zona Agrícola',            cor: '#84cc16', icon: 'crop' },
    zona_expansao:       { nome: 'Zona de Expansão',         cor: '#f97316', icon: 'expand' },
    patrimonio_cultural: { nome: 'Património Cultural',      cor: '#a855f7', icon: 'landmark' },
    hidrografia:         { nome: 'Hidrografia',              cor: '#0ea5e9', icon: 'water' },
    vias:                { nome: 'Vias e Acessos',           cor: '#737373', icon: 'route' },
    limites:             { nome: 'Limites Administrativos',  cor: '#dc2626', icon: 'border' },
};

export class LayerPanel {
    constructor(editor) {
        this.editor = editor;
        this.el = document.getElementById('layer-panel-list');
        this.activeLayerId = null;
        this._showAddMenu = false;
        this._expandedLayers = new Set();
    }

    render(layers) {
        if (!this.el) return;

        const panel = document.getElementById('layer-panel');
        if (!panel) return;

        // Build full panel HTML
        panel.innerHTML = `
            <div class="panel-header">
                <span class="panel-title">Camadas</span>
                <button id="btn-add-layer" class="panel-add-btn" title="Adicionar camada">
                    ${iconSvg('plus')}
                </button>
            </div>
            <div id="layer-add-menu" class="layer-add-menu ${this._showAddMenu ? 'open' : 'hidden'}">
                <div class="layer-add-header">
                    <span>Adicionar camada</span>
                    <button id="btn-close-add" class="layer-add-close">✕</button>
                </div>
                <div class="layer-add-list">
                    ${Object.entries(TERRAIN_TYPES).map(([tipo, meta]) => {
                        const exists = layers.some(l => l.tipo === tipo);
                        return `
                            <button class="layer-add-item ${exists ? 'disabled' : ''}"
                                    data-tipo="${tipo}" ${exists ? 'disabled' : ''}>
                                <span class="layer-add-swatch" style="background:${meta.cor}"></span>
                                <span class="layer-add-name">${esc(meta.nome)}</span>
                                ${exists ? '<span class="layer-add-check">✓</span>' : ''}
                            </button>`;
                    }).join('')}
                </div>
            </div>
            <div id="layer-panel-list" class="layer-list-container">
                ${layers.map(l => this._renderLayerItem(l)).join('')}
            </div>
            <div class="layer-panel-footer">
                <div class="layer-stats">
                    <span class="layer-stats-icon">${iconSvg('layers')}</span>
                    <span>${layers.length} camada${layers.length !== 1 ? 's' : ''}</span>
                    <span class="layer-stats-sep">·</span>
                    <span id="sidebar-feature-count">${this._totalFeatures(layers)} feat.</span>
                </div>
            </div>
        `;

        // Re-select the list element
        this.el = document.getElementById('layer-panel-list');

        this._bindEvents(layers);
    }

    _renderLayerItem(l) {
        const isActive = l.id === this.activeLayerId;
        const count = this._getLayerFeatureCount(l.id);
        const isExpanded = this._expandedLayers?.has(l.id);
        const features = this._getLayerFeatures(l.id);

        let featuresHtml = '';
        if (features.length > 0) {
            featuresHtml = `<div class="layer-feature-list ${isExpanded ? 'open' : ''}" data-layer-id="${l.id}">
                ${features.map(f => {
                    const geomType = f.getGeometry()?.getType() ?? 'Geometry';
                    const fid = f.get('id');
                    const props = f.get('properties') ?? {};
                    const label = props.proprietario || geomType;
                    return `<div class="layer-feature-item" data-fid="${fid ?? ''}" data-layer-id="${l.id}">
                        <span class="layer-feature-icon">${geomIcon(geomType)}</span>
                        <span class="layer-feature-label">${esc(label)}</span>
                        ${fid ? `<span class="layer-feature-id">#${fid}</span>` : ''}
                    </div>`;
                }).join('')}
            </div>`;
        }

        return `
            <div class="layer-item ${isActive ? 'active' : ''}" data-id="${l.id}">
                ${count > 0 ? `<button class="layer-toggle-btn ${isExpanded ? 'open' : ''}" data-id="${l.id}" title="Expandir features">${iconSvg('chevron')}</button>` : `<span style="width:14px;flex-shrink:0"></span>`}
                <button class="layer-vis-btn" data-id="${l.id}" title="${l.visivel ? 'Ocultar' : 'Mostrar'}">
                    ${l.visivel ? iconSvg('eye') : iconSvg('eye-off')}
                </button>
                <span class="layer-swatch" style="background:${esc(l.cor)}" data-id="${l.id}" title="Mudar cor"></span>
                <span class="layer-name" data-id="${l.id}">${esc(l.nome)}</span>
                <span class="layer-count">${count}</span>
            </div>
            ${featuresHtml}`;
    }

    _getLayerFeatures(layerId) {
        const vl = this.editor.vectorLayers.get(layerId);
        return vl ? vl.getSource().getFeatures() : [];
    }

    _bindEvents(layers) {
        // Add layer button
        document.getElementById('btn-add-layer')?.addEventListener('click', () => {
            this._showAddMenu = !this._showAddMenu;
            document.getElementById('layer-add-menu')?.classList.toggle('open', this._showAddMenu);
            document.getElementById('layer-add-menu')?.classList.toggle('hidden', !this._showAddMenu);
        });

        document.getElementById('btn-close-add')?.addEventListener('click', () => {
            this._showAddMenu = false;
            document.getElementById('layer-add-menu')?.classList.remove('open');
            document.getElementById('layer-add-menu')?.classList.add('hidden');
        });

        // Add layer items
        document.querySelectorAll('.layer-add-item:not(.disabled)').forEach(btn => {
            btn.addEventListener('click', () => this._addLayer(btn.dataset.tipo));
        });

        // Visibility toggles
        this.el?.querySelectorAll('.layer-vis-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const lId = parseInt(btn.dataset.id);
                const layer = layers.find(l => l.id === lId);
                if (layer) this._toggleVisibility(layer);
            });
        });

        // Layer item click (activate)
        this.el?.querySelectorAll('.layer-item').forEach(item => {
            item.addEventListener('click', () => {
                this.setActiveLayer(parseInt(item.dataset.id));
            });
        });

        // Double-click to rename
        this.el?.querySelectorAll('.layer-name').forEach(nameEl => {
            nameEl.addEventListener('dblclick', (e) => {
                e.stopPropagation();
                const lId = parseInt(nameEl.dataset.id);
                const layer = layers.find(l => l.id === lId);
                if (layer) this._renameInline(layer, nameEl);
            });
        });

        // Color swatch click
        this.el?.querySelectorAll('.layer-swatch[data-id]').forEach(swatch => {
            swatch.addEventListener('click', (e) => {
                e.stopPropagation();
                const lId = parseInt(swatch.dataset.id);
                const layer = layers.find(l => l.id === lId);
                if (layer) this._showColorPicker(layer, swatch);
            });
        });

        // Toggle feature list expand/collapse
        this.el?.querySelectorAll('.layer-toggle-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const lId = parseInt(btn.dataset.id);
                if (this._expandedLayers.has(lId)) {
                    this._expandedLayers.delete(lId);
                } else {
                    this._expandedLayers.add(lId);
                }
                btn.classList.toggle('open', this._expandedLayers.has(lId));
                const list = this.el?.querySelector(`.layer-feature-list[data-layer-id="${lId}"]`);
                if (list) list.classList.toggle('open', this._expandedLayers.has(lId));
            });
        });

        // Feature sub-item click → select + zoom
        this.el?.querySelectorAll('.layer-feature-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.stopPropagation();
                const fid = parseInt(item.dataset.fid);
                const layerId = parseInt(item.dataset.layerId);
                if (!fid) return;

                const olFeature = this.editor._findOlFeatureById(fid);
                if (!olFeature) return;

                // Activate layer
                this.setActiveLayer(layerId);

                // Select feature
                this.editor.map.getLayers().forEach(l => {
                    if (l.getSource?.()?.getFeatures) {
                        l.getSource().getFeatures().forEach(f => f.set('_selected', false));
                    }
                });
                olFeature.set('_selected', true);
                this.editor.propertyPanel.show(olFeature);

                // Zoom to
                const ext = olFeature.getGeometry().getExtent();
                this.editor.map.getView().fit(ext, {
                    padding: [80, 80, 80, 80],
                    maxZoom: 18,
                    duration: 400,
                });

                // Highlight active feature item
                this.el?.querySelectorAll('.layer-feature-item').forEach(fi => fi.classList.remove('active'));
                item.classList.add('active');
            });
        });
    }

    setActiveLayer(layerId) {
        this.activeLayerId = layerId;
        this.editor.activeLayerId = layerId;
        this.el?.querySelectorAll('.layer-item').forEach(item => {
            item.classList.toggle('active', parseInt(item.dataset.id) === layerId);
        });
        document.dispatchEvent(new CustomEvent('layer:changed', { detail: { layerId } }));
    }

    updateCounts() {
        if (!this.el) return;
        this.el.querySelectorAll('.layer-item').forEach(item => {
            const lId = parseInt(item.dataset.id);
            const countEl = item.querySelector('.layer-count');
            if (countEl) countEl.textContent = this._getLayerFeatureCount(lId);
        });
        const totalEl = document.getElementById('sidebar-feature-count');
        if (totalEl) totalEl.textContent = `${this._totalFeatures(this.editor.layers)} feat.`;
    }

    _getLayerFeatureCount(layerId) {
        const vl = this.editor.vectorLayers.get(layerId);
        return vl ? vl.getSource().getFeatures().length : 0;
    }

    _totalFeatures(layers) {
        let total = 0;
        for (const l of layers) {
            const vl = this.editor.vectorLayers.get(l.id);
            if (vl) total += vl.getSource().getFeatures().length;
        }
        total += this.editor.vectorSource.getFeatures().length;
        return total;
    }

    _toggleVisibility(layer) {
        layer.visivel = !layer.visivel;
        const btn = this.el?.querySelector(`.layer-vis-btn[data-id="${layer.id}"]`);
        if (btn) btn.innerHTML = layer.visivel ? iconSvg('eye') : iconSvg('eye-off');
        const olLayer = this.editor.vectorLayers.get(layer.id);
        if (olLayer) olLayer.setVisible(layer.visivel);
        updateLayer(layer.id, { visivel: layer.visivel }).catch(console.error);
    }

    _renameInline(layer, nameEl) {
        const input = document.createElement('input');
        input.type = 'text';
        input.value = layer.nome;
        input.className = 'layer-rename-input';
        nameEl.replaceWith(input);
        input.focus();
        input.select();

        const save = () => {
            const novo = input.value.trim();
            if (novo && novo !== layer.nome) {
                layer.nome = novo;
                updateLayer(layer.id, { nome: novo }).catch(console.error);
            }
            this.render(this.editor.layers);
        };

        input.addEventListener('blur', save);
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') input.blur();
            if (e.key === 'Escape') { input.value = layer.nome; input.blur(); }
        });
    }

    _showColorPicker(layer, swatch) {
        const input = document.createElement('input');
        input.type = 'color';
        input.value = layer.cor;
        input.style.cssText = 'position:absolute;opacity:0;width:0;height:0;';
        swatch.appendChild(input);
        input.click();

        input.addEventListener('input', () => {
            layer.cor = input.value;
            swatch.style.background = input.value;
            const olLayer = this.editor.vectorLayers.get(layer.id);
            if (olLayer) olLayer.changed();
        });

        input.addEventListener('change', () => {
            updateLayer(layer.id, { cor: layer.cor }).catch(console.error);
            input.remove();
        });
    }

    async _addLayer(tipo) {
        this._showAddMenu = false;
        try {
            const meta = TERRAIN_TYPES[tipo];
            const layer = await ensureLayer(this.editor.municipioId, tipo, {
                nome: meta?.nome,
                cor: meta?.cor,
            });

            const newLayer = { ...layer, features_count: 0, visivel: true };
            this.editor.layers.push(newLayer);

            // Create OL vector layer
            const VectorSource = this.editor._VectorSource;
            const VectorLayer = this.editor._VectorLayer;
            const src = new VectorSource();
            const vl = new VectorLayer({
                source: src,
                style: (f) => this.editor._featureStyle(f, newLayer.cor),
                visible: true,
            });
            this.editor.map.addLayer(vl);
            this.editor.vectorLayers.set(newLayer.id, vl);

            // Sync source change events
            src.on('addfeature',    () => this.editor._updateCountBadge());
            src.on('removefeature', () => this.editor._updateCountBadge());

            document.dispatchEvent(new CustomEvent('layer:added', { detail: { layer: newLayer } }));

            this.setActiveLayer(newLayer.id);
            this.render(this.editor.layers);
        } catch (err) {
            console.error('Erro ao criar camada:', err);
        }
    }
}

// ─── SVG Icons ───────────────────────────────────────────

function iconSvg(name) {
    const icons = {
        eye: `<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
        </svg>`,
        'eye-off': `<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
            <line x1="1" y1="1" x2="23" y2="23"/>
        </svg>`,
        plus: `<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>`,
        layers: `<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>
        </svg>`,
        chevron: `<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <polyline points="9 18 15 12 9 6"/>
        </svg>`,
    };
    return icons[name] ?? '';
}

function geomIcon(type) {
    const s = (d) => `<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">${d}</svg>`;
    const map = {
        Polygon:      s('<polygon points="12 2 22 19 2 19"/>'),
        MultiPolygon: s('<polygon points="12 2 22 19 2 19"/>'),
        LineString:   s('<line x1="3" y1="21" x2="21" y2="3"/>'),
        MultiLineString: s('<line x1="3" y1="21" x2="21" y2="3"/>'),
        Point:        s('<circle cx="12" cy="12" r="4"/>'),
        MultiPoint:   s('<circle cx="12" cy="12" r="4"/>'),
        Circle:       s('<circle cx="12" cy="12" r="9"/>'),
    };
    return map[type] ?? s('<circle cx="12" cy="12" r="3"/>');
}

function esc(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

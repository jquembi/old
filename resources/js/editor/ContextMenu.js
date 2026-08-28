/**
 * ContextMenu.js — Menu de contexto personalizado no mapa
 *
 * Exibe ao clicar com botão direito sobre o mapa:
 * - Sobre uma feature: Seleccionar, Propriedades, Mover para camada, Duplicar, Eliminar
 * - Sobre o mapa vazio: Adicionar ponto, Centrar aqui, Ajustar tudo
 */
import { confirmModal } from './ModalManager.js';
import { updateFeature } from './GeometryService.js';
import Feature from 'ol/Feature';
import Point   from 'ol/geom/Point';

export class ContextMenu {
    constructor(editor) {
        this.editor = editor;
        this.el = null;
        this._feature = null;
        this._coordinate = null;
        this._bound = false;

        this._create();
        this._bindMap();
    }

    _create() {
        this.el = document.createElement('div');
        this.el.id = 'map-context-menu';
        this.el.className = 'ctx-menu hidden';
        this.editor.mapEl.appendChild(this.el);

        // Close on any click outside
        document.addEventListener('click', () => this.hide());
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') this.hide(); });
    }

    _bindMap() {
        const mapEl = this.editor.mapEl;
        if (!mapEl || this._bound) return;
        this._bound = true;

        mapEl.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            e.stopPropagation();

            const pixel = this.editor.map.getEventPixel(e);
            this._coordinate = this.editor.map.getCoordinateFromPixel(pixel);

            // Check if clicked on a feature
            let hitFeature = null;
            this.editor.map.forEachFeatureAtPixel(pixel, (f) => {
                hitFeature = f;
                return true; // stop at first hit
            }, { hitTolerance: 5 });

            this._feature = hitFeature;

            if (hitFeature) {
                this._showFeatureMenu(e, hitFeature);
            } else {
                this._showMapMenu(e);
            }
        });
    }

    _showFeatureMenu(e, feature) {
        const geomType = feature.getGeometry()?.getType() ?? 'Geometria';
        const fid = feature.get('id');
        const layerId = feature.get('layer_id');
        const props = feature.get('properties') ?? {};
        const autor = props.criado_por_nome ?? feature.get('criado_por_nome') ?? null;
        const currentLayer = this.editor.layers.find(l => l.id === layerId);
        const layerName = currentLayer?.nome ?? 'Sem camada';

        // Build "move to layer" submenu items
        const otherLayers = this.editor.layers.filter(l => l.id !== layerId);
        const moveSubmenu = otherLayers.length > 0
            ? otherLayers.map(l =>
                `<button class="ctx-item ctx-sub-item" data-action="move-layer" data-layer-id="${l.id}">
                    <span class="ctx-swatch" style="background:${_esc(l.cor)}"></span>
                    ${_esc(l.nome)}
                </button>`
            ).join('')
            : '<span class="ctx-item ctx-disabled">Sem outras camadas</span>';

        this.el.innerHTML = `
            <div class="ctx-header">
                <span class="ctx-geom-badge">${_esc(geomType)}</span>
                ${fid ? `<span class="ctx-fid">#${fid}</span>` : ''}
            </div>
            ${autor ? `<div class="ctx-author">${_icon('user')} ${_esc(autor)}</div>` : ''}
            <button class="ctx-item" data-action="select">
                ${_icon('cursor')} Seleccionar
            </button>
            <button class="ctx-item" data-action="properties">
                ${_icon('info')} Propriedades
            </button>
            <button class="ctx-item" data-action="zoom-to">
                ${_icon('zoomIn')} Centrar nesta feature
            </button>
            <div class="ctx-sep"></div>
            <button class="ctx-item" data-action="duplicate">
                ${_icon('copy')} Duplicar
            </button>
            <div class="ctx-submenu-trigger">
                <button class="ctx-item" data-action="toggle-move">
                    ${_icon('layers')} Mover para camada
                    <span class="ctx-arrow">›</span>
                </button>
                <div class="ctx-submenu hidden" id="ctx-submenu-layers">
                    <div class="ctx-sub-header">Camada actual: <strong>${_esc(layerName)}</strong></div>
                    ${moveSubmenu}
                </div>
            </div>
            <div class="ctx-sep"></div>
            <button class="ctx-item ctx-item--danger" data-action="delete">
                ${_icon('trash')} Eliminar
            </button>
        `;

        this._position(e);
        this._bindFeatureActions(feature);
    }

    _showMapMenu(e) {
        this.el.innerHTML = `
            <button class="ctx-item" data-action="add-point">
                ${_icon('point')} Adicionar ponto aqui
            </button>
            <button class="ctx-item" data-action="center-here">
                ${_icon('zoomIn')} Centrar aqui
            </button>
            <div class="ctx-sep"></div>
            <button class="ctx-item" data-action="fit-all">
                ${_icon('fit')} Ajustar a todas as features
            </button>
        `;

        this._position(e);
        this._bindMapActions();
    }

    _position(e) {
        this.el.classList.remove('hidden');
        this.el.classList.add('open');

        const mapRect = this.editor.mapEl.getBoundingClientRect();
        let x = e.clientX - mapRect.left;
        let y = e.clientY - mapRect.top;

        // Keep menu within map boundaries
        const menuW = this.el.offsetWidth || 200;
        const menuH = this.el.offsetHeight || 250;
        if (x + menuW > mapRect.width) x = mapRect.width - menuW - 4;
        if (y + menuH > mapRect.height) y = mapRect.height - menuH - 4;
        if (x < 4) x = 4;
        if (y < 4) y = 4;

        this.el.style.left = x + 'px';
        this.el.style.top = y + 'px';
    }

    _bindFeatureActions(feature) {
        this.el.querySelectorAll('[data-action]').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.stopPropagation();
                const action = btn.dataset.action;

                switch (action) {
                    case 'select':
                        this._selectFeature(feature);
                        break;
                    case 'properties':
                        this._selectFeature(feature);
                        this.editor.propertyPanel.show(feature);
                        break;
                    case 'zoom-to':
                        this._zoomToFeature(feature);
                        break;
                    case 'duplicate':
                        this._duplicateFeature(feature);
                        break;
                    case 'toggle-move':
                        this._toggleSubmenu();
                        return; // don't hide menu
                    case 'move-layer':
                        await this._moveToLayer(feature, parseInt(btn.dataset.layerId));
                        break;
                    case 'delete':
                        await this._deleteFeature(feature);
                        break;
                }
                if (action !== 'toggle-move') this.hide();
            });
        });
    }

    _bindMapActions() {
        this.el.querySelectorAll('[data-action]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const action = btn.dataset.action;

                switch (action) {
                    case 'add-point':
                        this._addPointHere();
                        break;
                    case 'center-here':
                        this.editor.map.getView().animate({
                            center: this._coordinate,
                            duration: 400,
                        });
                        break;
                    case 'fit-all':
                        this.editor._fitToFeatures();
                        break;
                }
                this.hide();
            });
        });
    }

    hide() {
        if (this.el) {
            this.el.classList.remove('open');
            this.el.classList.add('hidden');
        }
    }

    // ─── Actions ──────────────────────────────────────────

    _selectFeature(feature) {
        // Deselect previous
        this.editor.map.getLayers().forEach(l => {
            if (l.getSource && l.getSource()?.getFeatures) {
                l.getSource().getFeatures().forEach(f => f.set('_selected', false));
            }
        });
        feature.set('_selected', true);
        this.editor.propertyPanel.show(feature);
    }

    _zoomToFeature(feature) {
        const ext = feature.getGeometry().getExtent();
        this.editor.map.getView().fit(ext, {
            padding: [80, 80, 80, 80],
            maxZoom: 18,
            duration: 500,
        });
    }

    _duplicateFeature(feature) {
        const clone = feature.clone();
        const geom = clone.getGeometry();
        if (geom) geom.translate(50, -50);
        clone.unset('id');
        clone.setId(undefined);

        const layerId = feature.get('layer_id');
        const vl = this.editor.vectorLayers.get(layerId);
        const source = vl ? vl.getSource() : this.editor.vectorSource;

        clone.set('layer_id', layerId);
        source.addFeature(clone);
        this.editor.persistNewFeature(clone);
    }

    async _deleteFeature(feature) {
        const geomType = feature.getGeometry()?.getType() ?? 'geometria';
        const fid = feature.get('id');

        const confirmed = await confirmModal({
            title: 'Eliminar geometria',
            message: `Tem a certeza que deseja eliminar esta <strong>${_esc(geomType)}</strong>${fid ? ` (ID #${fid})` : ''}?<br>Esta acção não pode ser desfeita.`,
            confirmText: 'Eliminar',
            cancelText: 'Cancelar',
            variant: 'danger',
        });

        if (confirmed) {
            this.editor.deleteFeatureById(feature);
            this.editor.propertyPanel.hide();
        }
    }

    async _moveToLayer(feature, targetLayerId) {
        const targetLayer = this.editor.layers.find(l => l.id === targetLayerId);
        if (!targetLayer) return;

        const confirmed = await confirmModal({
            title: 'Mover para camada',
            message: `Mover esta feature para a camada <strong>${_esc(targetLayer.nome)}</strong>?`,
            confirmText: 'Mover',
            cancelText: 'Cancelar',
            variant: 'info',
        });

        if (!confirmed) return;

        // Remove from current source
        this.editor.removeFeature(feature);

        // Update layer_id
        feature.set('layer_id', targetLayerId);

        // Add to target source
        const targetVl = this.editor.vectorLayers.get(targetLayerId);
        if (targetVl) {
            targetVl.getSource().addFeature(feature);
        }

        // Persist update
        const fid = feature.get('id');
        if (fid) {
            await updateFeature(fid, { layer_id: targetLayerId });
            this.editor._setStatus('✓ Movido', 'saved');
            setTimeout(() => this.editor._setStatus('Pronto'), 2000);
        }
    }

    _addPointHere() {
        if (!this._coordinate || !this.editor.activeLayerId) return;

        const point = new Point(this._coordinate);
        const f = new Feature({ geometry: point });
        f.set('layer_id', this.editor.activeLayerId);
        this.editor.vectorSource.addFeature(f);
        this.editor.persistNewFeature(f);
    }

    _toggleSubmenu() {
        const sub = this.el.querySelector('#ctx-submenu-layers');
        if (sub) {
            sub.classList.toggle('hidden');
            sub.classList.toggle('open');
        }
    }
}

// ─── Helpers ──────────────────────────────────────────────

function _esc(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

const _s = (d) => `<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">${d}</svg>`;

function _icon(name) {
    const map = {
        cursor:  _s('<path d="M5 3l14 9-7 2-2 7L5 3z"/>'),
        info:    _s('<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>'),
        zoomIn:  _s('<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/>'),
        copy:    _s('<rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>'),
        layers:  _s('<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>'),
        trash:   _s('<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>'),
        point:   _s('<circle cx="12" cy="12" r="4"/><line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/>'),
        fit:     _s('<path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/>'),
        user:    _s('<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>'),
    };
    return map[name] ?? '';
}

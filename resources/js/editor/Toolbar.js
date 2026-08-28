/**
 * Toolbar.js — Barra de ferramentas do Editor GIS
 *
 * Ferramentas: Selecionar, Polígono, Linha, Ponto, Rectângulo, Círculo,
 *              Mão-livre, Medir Área, Medir Distância,
 *              Desfazer, Refazer, Copiar, Exportar, Centrar, Eliminar
 */
import Draw      from 'ol/interaction/Draw';
import Modify    from 'ol/interaction/Modify';
import Snap      from 'ol/interaction/Snap';
import Select    from 'ol/interaction/Select';
import Translate from 'ol/interaction/Translate';
import { click } from 'ol/events/condition';
import { getArea, getLength } from 'ol/sphere';
import VectorLayer  from 'ol/layer/Vector';
import VectorSource from 'ol/source/Vector';
import { createBox, createRegularPolygon } from 'ol/interaction/Draw';
import { confirmModal } from './ModalManager.js';

const TOOLS = [
    'select',
    'draw-polygon', 'draw-line', 'draw-point',
    'draw-rectangle', 'draw-circle', 'draw-freehand',
    'measure-area', 'measure-length',
];

export class Toolbar {
    constructor(editor) {
        this.editor        = editor;
        this.activeTool    = null;
        this._interactions = {};
        this._undoStack    = [];
        this._redoStack    = [];
        this._tooltip      = null;
        this._measureLayer = null;
        this._snapEnabled  = true;

        this._buildUi();
    }

    _buildUi() {
        const bar = document.getElementById('editor-toolbar');
        if (!bar) return;

        const btns = [
            { id: 'select',         icon: icons.cursor,      title: 'Seleccionar (V)'         },
            { id: 'sep0', sep: true },
            { id: 'draw-polygon',   icon: icons.polygon,     title: 'Polígono (P)'            },
            { id: 'draw-rectangle', icon: icons.rectangle,   title: 'Rectângulo (R)'          },
            { id: 'draw-circle',    icon: icons.circle,      title: 'Círculo (C)'             },
            { id: 'draw-line',      icon: icons.line,        title: 'Linha (L)'               },
            { id: 'draw-point',     icon: icons.point,       title: 'Ponto (M)'               },
            { id: 'draw-freehand',  icon: icons.freehand,    title: 'Mão-livre (F)'           },
            { id: 'sep1', sep: true },
            { id: 'measure-area',   icon: icons.area,        title: 'Medir área (A)'          },
            { id: 'measure-length', icon: icons.ruler,       title: 'Medir distância (D)'     },
            { id: 'sep2', sep: true },
            { id: 'undo',           icon: icons.undo,        title: 'Desfazer (Ctrl+Z)',  action: () => this.undo() },
            { id: 'redo',           icon: icons.redo,        title: 'Refazer (Ctrl+Y)',   action: () => this.redo() },
            { id: 'copy',           icon: icons.copy,        title: 'Duplicar (Ctrl+D)',  action: () => this._duplicateSelected() },
            { id: 'sep3', sep: true },
            { id: 'snap-toggle',    icon: icons.magnet,      title: 'Snap (S)',           action: () => this._toggleSnap(), toggle: true },
            { id: 'sep4', sep: true },
            { id: 'fit',            icon: icons.fit,         title: 'Centrar no mapa',    action: () => this.editor._fitToFeatures() },
            { id: 'export',         icon: icons.download,    title: 'Exportar (E)',       action: () => this.editor.exportManager.showDialog() },
            { id: 'delete',         icon: icons.trash,       title: 'Eliminar (Del)',     action: () => this._deleteSelected() },
        ];

        bar.innerHTML = '';
        for (const b of btns) {
            if (b.sep) {
                const sep = document.createElement('div');
                sep.className = 'toolbar-sep';
                bar.appendChild(sep);
                continue;
            }
            const btn = document.createElement('button');
            btn.id = `tb-${b.id}`;
            btn.className = 'toolbar-btn';
            btn.title = b.title;
            btn.innerHTML = b.icon;
            bar.appendChild(btn);

            if (b.id === 'snap-toggle') {
                btn.classList.add('active');
            }

            if (TOOLS.includes(b.id)) {
                btn.addEventListener('click', () => this.activateTool(b.id));
            } else if (b.action) {
                btn.addEventListener('click', b.action);
            }
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
            if ((e.ctrlKey || e.metaKey) && e.key === 'z') { e.preventDefault(); this.undo(); }
            if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.shiftKey && e.key === 'z'))) {
                e.preventDefault(); this.redo();
            }
            if ((e.ctrlKey || e.metaKey) && (e.key === 'd' || e.key === 'D')) {
                e.preventDefault(); this._duplicateSelected();
            }
            if (e.key === 'Escape') this.activateTool('select');
            if (e.key === 'Delete' || e.key === 'Backspace') {
                if (e.target.tagName !== 'INPUT') this._deleteSelected();
            }
            if (e.key === 'v' || e.key === 'V') this.activateTool('select');
            if (e.key === 'p' || e.key === 'P') this.activateTool('draw-polygon');
            if (e.key === 'r' || e.key === 'R') this.activateTool('draw-rectangle');
            if (e.key === 'c' || e.key === 'C') this.activateTool('draw-circle');
            if (e.key === 'l' || e.key === 'L') this.activateTool('draw-line');
            if (e.key === 'm' || e.key === 'M') this.activateTool('draw-point');
            if (e.key === 'f' || e.key === 'F') this.activateTool('draw-freehand');
            if (e.key === 'a' || e.key === 'A') this.activateTool('measure-area');
            if (e.key === 'd' && !e.ctrlKey && !e.metaKey) this.activateTool('measure-length');
            if (e.key === 's' && !e.ctrlKey && !e.metaKey) this._toggleSnap();
        });
    }

    // ─── Activação ────────────────────────────────────────

    activateTool(toolId) {
        this._deactivateCurrent();
        this.activeTool = toolId;

        document.querySelectorAll('.toolbar-btn').forEach(b => b.classList.remove('active'));
        document.getElementById(`tb-${toolId}`)?.classList.add('active');

        // Cursor visual
        const container = this.editor.mapEl;
        if (container) {
            container.style.cursor = toolId === 'select' ? '' : 'crosshair';
        }

        switch (toolId) {
            case 'select':         this._activateSelect();     break;
            case 'draw-polygon':   this._activateDraw('Polygon');    break;
            case 'draw-rectangle': this._activateDraw('Circle', createBox());  break;
            case 'draw-circle':    this._activateDraw('Circle', createRegularPolygon(64)); break;
            case 'draw-line':      this._activateDraw('LineString'); break;
            case 'draw-point':     this._activateDraw('Point');      break;
            case 'draw-freehand':  this._activateDraw('LineString', null, true); break;
            case 'measure-area':   this._activateMeasure('Polygon');    break;
            case 'measure-length': this._activateMeasure('LineString'); break;
        }
    }

    _deactivateCurrent() {
        const map = this.editor.map;
        Object.values(this._interactions).forEach(ia => {
            if (Array.isArray(ia)) ia.forEach(i => map.removeInteraction(i));
            else if (ia) map.removeInteraction(ia);
        });
        this._interactions = {};
        this._removeMeasureTooltip();
        if (this._measureLayer) {
            map.removeLayer(this._measureLayer);
            this._measureLayer = null;
        }
    }

    // ─── Select / Mover / Modificar ───────────────────────

    _activateSelect() {
        const { map, vectorSource } = this.editor;

        const select = new Select({ condition: click, hitTolerance: 5 });
        const modify = new Modify({ features: select.getFeatures() });
        const translate = new Translate({ features: select.getFeatures() });
        const snap = new Snap({ source: vectorSource });

        map.addInteraction(select);
        map.addInteraction(modify);
        map.addInteraction(translate);
        map.addInteraction(snap);

        select.on('select', (e) => {
            // Limpa selecção anterior
            e.deselected.forEach(f => f.set('_selected', false));
            e.selected.forEach(f => f.set('_selected', true));

            if (e.selected.length > 0) {
                this.editor.propertyPanel.show(e.selected[0]);
            } else {
                this.editor.propertyPanel.hide();
            }
        });

        modify.on('modifyend', (e) => {
            e.features.forEach(f => this.editor.persistFeatureUpdate(f));
        });

        translate.on('translateend', (e) => {
            e.features.forEach(f => this.editor.persistFeatureUpdate(f));
        });

        this._interactions = { select, modify, translate, snap };
    }

    // ─── Desenho ──────────────────────────────────────────

    _activateDraw(type, geometryFunction, freehand) {
        const { map, vectorSource } = this.editor;
        const drawOpts = { source: vectorSource, type };
        if (geometryFunction) drawOpts.geometryFunction = geometryFunction;
        if (freehand)         drawOpts.freehand = true;

        const draw = new Draw(drawOpts);
        const interactions = [draw];

        if (this._snapEnabled) {
            const snap = new Snap({ source: vectorSource });
            map.addInteraction(snap);
            interactions.push(snap);
        }

        map.addInteraction(draw);

        draw.on('drawend', (e) => {
            const feature = e.feature;
            this._undoStack.push({ type: 'add', feature });
            this._redoStack = [];
            this.editor.persistNewFeature(feature);
        });

        this._interactions = { draw, extras: interactions };
    }

    // ─── Medição ──────────────────────────────────────────

    _activateMeasure(type) {
        const { map } = this.editor;

        const measureSource = new VectorSource();
        this._measureLayer = new VectorLayer({
            source: measureSource,
            style: this.editor._measureStyle(),
        });
        map.addLayer(this._measureLayer);

        const draw = new Draw({ source: measureSource, type });
        map.addInteraction(draw);

        const tooltip = this._createTooltipEl();

        draw.on('drawstart', () => {
            measureSource.clear();
            if (tooltip) tooltip.textContent = '';
        });

        draw.on('drawend', (e) => {
            const geom = e.feature.getGeometry();
            if (type === 'Polygon') {
                tooltip.innerHTML = `<strong>Área:</strong> ${formatArea(getArea(geom))}`;
            } else {
                tooltip.innerHTML = `<strong>Dist:</strong> ${formatLength(getLength(geom))}`;
            }
        });

        map.on('pointermove', (evt) => {
            if (!tooltip) return;
            const px = map.getEventPixel(evt.originalEvent);
            tooltip.style.left = (px[0] + 14) + 'px';
            tooltip.style.top  = (px[1] + 14) + 'px';
        });

        this._interactions = { draw };
    }

    _createTooltipEl() {
        this._removeMeasureTooltip();
        const el = document.createElement('div');
        el.className = 'measure-tooltip';
        this.editor.mapEl.appendChild(el);
        this._tooltip = el;
        return el;
    }

    _removeMeasureTooltip() {
        this._tooltip?.remove();
        this._tooltip = null;
    }

    // ─── Eliminar ─────────────────────────────────────────

    async _deleteSelected() {
        const sel = this._interactions.select;
        if (!sel) return;

        const features = sel.getFeatures();
        if (features.getLength() === 0) return;

        const count = features.getLength();
        const confirmed = await confirmModal({
            title: 'Eliminar geometria',
            message: `Tem a certeza que deseja eliminar <strong>${count}</strong> geometria${count > 1 ? 's' : ''}?<br>Esta acção não pode ser desfeita.`,
            confirmText: 'Eliminar',
            cancelText: 'Cancelar',
            variant: 'danger',
        });

        if (!confirmed) return;

        features.forEach(f => {
            this._undoStack.push({ type: 'remove', feature: f });
            this._redoStack = [];
            this.editor.deleteFeatureById(f);
        });
        features.clear();
        this.editor.propertyPanel.hide();
    }

    // ─── Undo / Redo ─────────────────────────────────────

    undo() {
        const action = this._undoStack.pop();
        if (!action) return;
        if (action.type === 'add') {
            this.editor.deleteFeatureById(action.feature);
        } else if (action.type === 'remove') {
            this.editor.vectorSource.addFeature(action.feature);
            this.editor.persistNewFeature(action.feature);
        }
        this._redoStack.push(action);
    }

    redo() {
        const action = this._redoStack.pop();
        if (!action) return;
        if (action.type === 'add') {
            this.editor.vectorSource.addFeature(action.feature);
            this.editor.persistNewFeature(action.feature);
        } else if (action.type === 'remove') {
            this.editor.deleteFeatureById(action.feature);
        }
        this._undoStack.push(action);
    }

    // ─── Duplicar ─────────────────────────────────────────

    _duplicateSelected() {
        const sel = this._interactions.select;
        if (!sel) return;

        const features = sel.getFeatures();
        if (features.getLength() === 0) return;

        features.forEach(f => {
            const clone = f.clone();
            // Offset ligeiro para distinguir visualmente
            const geom = clone.getGeometry();
            if (geom) geom.translate(50, -50);
            // Remove ID do servidor para que seja criado como novo
            clone.unset('id');
            clone.setId(undefined);
            this.editor.vectorSource.addFeature(clone);
            this.editor.persistNewFeature(clone);
            this._undoStack.push({ type: 'add', feature: clone });
            this._redoStack = [];
        });
    }

    // ─── Snap toggle ──────────────────────────────────────

    _toggleSnap() {
        this._snapEnabled = !this._snapEnabled;
        const btn = document.getElementById('tb-snap-toggle');
        if (btn) btn.classList.toggle('active', this._snapEnabled);
    }
}

// ═══════════════════════════════════════════════════════
//  HELPERS
// ═══════════════════════════════════════════════════════

function formatArea(m2) {
    if (m2 >= 10000) return `${(m2 / 10000).toFixed(4)} ha (${m2.toFixed(0)} m²)`;
    return `${m2.toFixed(2)} m²`;
}

function formatLength(m) {
    if (m >= 1000) return `${(m / 1000).toFixed(3)} km`;
    return `${m.toFixed(1)} m`;
}

// ═══════════════════════════════════════════════════════
//  ÍCONES SVG (18x18)
// ═══════════════════════════════════════════════════════

const s = (d) => `<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">${d}</svg>`;

const icons = {
    cursor:    s('<path d="M5 3l14 9-7 2-2 7L5 3z"/>'),
    polygon:   s('<polygon points="12 2 22 19 2 19"/>'),
    rectangle: s('<rect x="3" y="5" width="18" height="14" rx="1"/>'),
    circle:    s('<circle cx="12" cy="12" r="9"/>'),
    line:      s('<line x1="3" y1="21" x2="21" y2="3"/>'),
    point:     s('<circle cx="12" cy="12" r="4"/><line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/>'),
    freehand:  s('<path d="M4 18c2-4 4-2 6-6s2-6 6-6c2 0 4 2 4 4"/>'),
    area:      s('<path d="M21 3L3 21"/><path d="M6 3v4M3 6h4M18 17v4M17 18h4"/>'),
    ruler:     s('<line x1="4" y1="20" x2="20" y2="4"/><line x1="4" y1="12" x2="12" y2="4"/>'),
    undo:      s('<polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4"/>'),
    redo:      s('<polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-.49-4"/>'),
    copy:      s('<rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>'),
    magnet:    s('<path d="M6 2v6a6 6 0 1 0 12 0V2"/><line x1="6" y1="2" x2="6" y2="6"/><line x1="18" y1="2" x2="18" y2="6"/>'),
    download:  s('<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>'),
    trash:     s('<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>'),
    fit:       s('<path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/>'),
};

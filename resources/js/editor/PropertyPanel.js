/**
 * PropertyPanel.js — Painel de propriedades da feature seleccionada.
 *
 * Mostra informações, permite editar observações directamente,
 * e salva automaticamente. Sem formulário externo — tudo inline.
 */
import { updateFeature, deleteFeature } from './GeometryService.js';
import { confirmModal } from './ModalManager.js';

export class PropertyPanel {
    constructor(editor) {
        this.editor = editor;
        this.el = document.getElementById('property-panel');
        this.currentFeature = null;
        this.currentGeoId   = null;
    }

    show(olFeature) {
        this.currentFeature = olFeature;
        this.currentGeoId   = olFeature.get('id') ?? null;
        const props  = olFeature.get('properties') ?? {};
        const areaM2 = olFeature.get('area_m2');
        const areaHa = olFeature.get('area_ha');
        const geomType = olFeature.getGeometry()?.getType() ?? '—';
        const autor  = props.criado_por_nome ?? olFeature.get('criado_por_nome') ?? '—';

        if (!this.el) return;

        const observacoes = props.observacoes ?? '';
        const proprietario = props.proprietario ?? '';

        this.el.innerHTML = `
            <div class="prop-header">
                <span class="prop-title">
                    <span class="prop-dot"></span>
                    Propriedades
                </span>
                <button id="prop-close" title="Fechar">✕</button>
            </div>
            <div class="prop-body">
                <div class="prop-section">
                    <div class="prop-row">
                        <label>Tipo</label>
                        <span>${esc(geomType)}</span>
                    </div>
                    ${areaM2 != null ? `
                    <div class="prop-row">
                        <label>Área</label>
                        <span>${fmtArea(areaM2)}</span>
                    </div>` : ''}
                    ${areaHa != null ? `
                    <div class="prop-row">
                        <label>Hectares</label>
                        <span>${Number(areaHa).toFixed(4)} ha</span>
                    </div>` : ''}
                    <div class="prop-row">
                        <label>Autor</label>
                        <span>${esc(autor)}</span>
                    </div>
                    ${this.currentGeoId ? `
                    <div class="prop-row">
                        <label>ID</label>
                        <span class="prop-id">#${this.currentGeoId}</span>
                    </div>` : ''}
                </div>

                <div class="prop-section">
                    <div class="prop-field">
                        <label for="prop-proprietario">Proprietário</label>
                        <input id="prop-proprietario" class="prop-input" value="${esc(proprietario)}" placeholder="Nome do proprietário…">
                    </div>
                    <div class="prop-field">
                        <label for="prop-obs">Observações</label>
                        <textarea id="prop-obs" class="prop-input" rows="3" placeholder="Notas sobre o terreno…">${esc(observacoes)}</textarea>
                    </div>
                </div>
            </div>
            <div class="prop-footer">
                ${this.currentGeoId ? `
                    <button id="prop-save" class="prop-btn prop-btn--primary">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Guardar
                    </button>
                    <button id="prop-delete" class="prop-btn prop-btn--danger" title="Eliminar esta geometria">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                ` : ''}
            </div>
        `;

        this.el.classList.remove('hidden');
        this.el.classList.add('open');

        // Eventos
        document.getElementById('prop-close')?.addEventListener('click', () => this.hide());

        document.getElementById('prop-save')?.addEventListener('click', () => {
            this._saveProperties();
        });

        document.getElementById('prop-delete')?.addEventListener('click', async () => {
            const confirmed = await confirmModal({
                title: 'Eliminar geometria',
                message: 'Tem a certeza que deseja eliminar esta geometria?<br>Esta acção não pode ser desfeita.',
                confirmText: 'Eliminar',
                cancelText: 'Cancelar',
                variant: 'danger',
            });
            if (confirmed) this._deleteFeature();
        });

        // Auto-save ao perder foco
        const autoSaveFields = ['prop-proprietario', 'prop-obs'];
        autoSaveFields.forEach(id => {
            document.getElementById(id)?.addEventListener('blur', () => {
                this._saveProperties();
            });
        });
    }

    hide() {
        if (this.el) {
            this.el.classList.add('hidden');
            this.el.classList.remove('open');
        }
        if (this.currentFeature) {
            this.currentFeature.set('_selected', false);
        }
        this.currentFeature = null;
        this.currentGeoId   = null;
    }

    _saveProperties() {
        if (!this.currentGeoId) return;

        const proprietario = document.getElementById('prop-proprietario')?.value ?? '';
        const observacoes  = document.getElementById('prop-obs')?.value ?? '';

        const props = {
            ...(this.currentFeature?.get('properties') ?? {}),
            proprietario,
            observacoes,
        };

        // Actualiza localmente
        this.currentFeature?.set('properties', props);

        // Persiste via API
        this.editor.persistPropertyUpdate(this.currentGeoId, props);
    }

    _deleteFeature() {
        if (!this.currentFeature) return;
        this.editor.deleteFeatureById(this.currentFeature);
        this.hide();
    }
}

// ─── Helpers ──────────────────────────────────────────────

function fmtArea(m2) {
    if (m2 >= 10000) return `${(m2 / 10000).toFixed(4)} ha`;
    return `${Number(m2).toFixed(2)} m²`;
}

function esc(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

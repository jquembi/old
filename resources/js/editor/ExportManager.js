/**
 * ExportManager.js — Exportação multi-formato profissional
 *
 * Formatos suportados:
 * - GeoJSON  (QGIS, Mapbox, Leaflet, ArcGIS)
 * - KML      (Google Earth, ArcGIS)
 * - DXF      (AutoCAD, ArchiCAD, Revit, SketchUp)
 * - CSV      (Excel, LibreOffice, análise)
 */
import { alertModal } from './ModalManager.js';

export class ExportManager {
    constructor(editor) {
        this.editor = editor;
    }

    showDialog() {
        const overlay = this._getOverlay();

        overlay.innerHTML = `
            <div class="export-dialog">
                <div class="export-header">
                    <span class="export-title">
                        ${_icon('download')} Exportar Cartografia
                    </span>
                    <button class="export-close" id="export-close">✕</button>
                </div>
                <div class="export-body">
                    <p class="export-desc">Seleccione o formato de exportação pretendido:</p>
                    <div class="export-formats">
                        ${_formatBtn('geojson', 'GeoJSON', 'QGIS, Mapbox, Leaflet', 'file-json')}
                        ${_formatBtn('kml', 'KML', 'Google Earth, ArcGIS', 'globe')}
                        ${_formatBtn('dxf', 'DXF', 'AutoCAD, ArchiCAD, Revit', 'blueprint')}
                        ${_formatBtn('csv', 'CSV', 'Excel, LibreOffice', 'table')}
                    </div>
                </div>
                <div class="export-footer">
                    <span class="export-hint">Os dados são exportados em coordenadas WGS 84 (EPSG:4326)</span>
                </div>
            </div>
        `;

        overlay.classList.remove('hidden');
        requestAnimationFrame(() => overlay.classList.add('open'));

        overlay.querySelectorAll('.export-format-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                btn.classList.add('loading');
                btn.disabled = true;
                try {
                    await this._doExport(btn.dataset.format);
                } finally {
                    btn.classList.remove('loading');
                    btn.disabled = false;
                }
                this._close();
            });
        });

        overlay.querySelector('#export-close')?.addEventListener('click', () => this._close());
        overlay.addEventListener('click', (e) => { if (e.target === overlay) this._close(); });
        document.addEventListener('keydown', this._onEsc = (e) => { if (e.key === 'Escape') this._close(); });
    }

    async _doExport(format) {
        const municipioId = this.editor.municipioId;

        if (format === 'geojson') {
            await this.editor.exportGeoJson();
            return;
        }

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            const headerName = token ? 'X-CSRF-TOKEN' : 'X-XSRF-TOKEN';
            const headerValue = token || this._xsrfToken();

            const res = await fetch(`/api/maps/${municipioId}/export/${format}`, {
                credentials: 'include',
                headers: { [headerName]: headerValue, 'Accept': '*/*' },
            });

            if (!res.ok) {
                const err = await res.json().catch(() => ({}));
                throw new Error(err.message ?? `Erro HTTP ${res.status}`);
            }

            const blob = await res.blob();
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;

            const disp = res.headers.get('Content-Disposition');
            const match = disp?.match(/filename="?([^"]+)"?/);
            a.download = match?.[1] ?? `export.${format}`;

            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(url);
        } catch (e) {
            alertModal({ title: 'Erro ao exportar', message: e.message, variant: 'danger' });
        }
    }

    _xsrfToken() {
        const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
        return match ? decodeURIComponent(match[1]) : '';
    }

    _getOverlay() {
        let el = document.getElementById('export-overlay');
        if (!el) {
            el = document.createElement('div');
            el.id = 'export-overlay';
            el.className = 'modal-overlay hidden';
            document.body.appendChild(el);
        }
        return el;
    }

    _close() {
        document.removeEventListener('keydown', this._onEsc);
        const el = document.getElementById('export-overlay');
        if (el) {
            el.classList.remove('open');
            setTimeout(() => { el.classList.add('hidden'); el.innerHTML = ''; }, 200);
        }
    }
}

// ─── Helpers ───────────────────────────────

function _formatBtn(format, name, desc, icon) {
    return `
        <button class="export-format-btn" data-format="${format}">
            <span class="export-format-icon">${_icon(icon)}</span>
            <span class="export-format-info">
                <strong>${name}</strong>
                <small>${desc}</small>
            </span>
        </button>`;
}

const _s = (d) => `<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">${d}</svg>`;

function _icon(name) {
    const map = {
        'file-json': _s('<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M10 12h4"/>'),
        globe:       _s('<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>'),
        blueprint:   _s('<rect x="2" y="3" width="20" height="18" rx="2"/><line x1="8" y1="3" x2="8" y2="21"/><line x1="2" y1="9" x2="22" y2="9"/><line x1="2" y1="15" x2="22" y2="15"/>'),
        table:       _s('<rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/>'),
        download:    _s('<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>'),
    };
    return map[name] ?? '';
}

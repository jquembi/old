/**
 * ModalManager.js — Modal de confirmação/alerta personalizado
 * Substitui alert() e confirm() nativos por modais temáticos.
 */

let _container = null;

function _ensureContainer() {
    if (_container) return _container;
    _container = document.createElement('div');
    _container.id = 'editor-modal-overlay';
    _container.className = 'modal-overlay hidden';
    document.body.appendChild(_container);
    return _container;
}

/**
 * Mostra um modal de confirmação personalizado.
 * @param {object} opts
 * @param {string} opts.title   — Título do modal
 * @param {string} opts.message — Corpo da mensagem (pode conter HTML)
 * @param {string} opts.confirmText — Texto do botão confirmar (default: 'Confirmar')
 * @param {string} opts.cancelText  — Texto do botão cancelar (default: 'Cancelar')
 * @param {string} opts.variant     — 'danger' | 'warning' | 'info' (default: 'danger')
 * @param {string} opts.icon        — SVG icon override
 * @returns {Promise<boolean>}
 */
export function confirmModal(opts = {}) {
    const {
        title = 'Confirmar acção',
        message = 'Tem a certeza?',
        confirmText = 'Confirmar',
        cancelText = 'Cancelar',
        variant = 'danger',
    } = opts;

    return new Promise((resolve) => {
        const overlay = _ensureContainer();
        const iconMap = {
            danger:  `<svg width="28" height="28" fill="none" stroke="#f87171" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
            warning: `<svg width="28" height="28" fill="none" stroke="#fbbf24" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`,
            info:    `<svg width="28" height="28" fill="none" stroke="#60a5fa" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>`,
        };

        const variantColors = {
            danger:  { bg: '#7f1d1d', hover: '#991b1b', text: '#fca5a5' },
            warning: { bg: '#78350f', hover: '#92400e', text: '#fde68a' },
            info:    { bg: '#1e3a5f', hover: '#1e40af', text: '#93c5fd' },
        };
        const vc = variantColors[variant] ?? variantColors.danger;

        overlay.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-icon">${iconMap[variant] ?? iconMap.danger}</div>
                <div class="modal-content">
                    <h3 class="modal-title">${_esc(title)}</h3>
                    <p class="modal-message">${message}</p>
                </div>
                <div class="modal-actions">
                    <button class="modal-btn modal-btn--cancel" id="modal-cancel">${_esc(cancelText)}</button>
                    <button class="modal-btn modal-btn--confirm" id="modal-confirm"
                            style="background:${vc.bg};color:${vc.text};"
                            onmouseover="this.style.background='${vc.hover}'"
                            onmouseout="this.style.background='${vc.bg}'">${_esc(confirmText)}</button>
                </div>
            </div>
        `;

        overlay.classList.remove('hidden');
        requestAnimationFrame(() => overlay.classList.add('open'));

        const close = (result) => {
            overlay.classList.remove('open');
            setTimeout(() => {
                overlay.classList.add('hidden');
                overlay.innerHTML = '';
            }, 200);
            resolve(result);
        };

        overlay.querySelector('#modal-confirm').addEventListener('click', () => close(true));
        overlay.querySelector('#modal-cancel').addEventListener('click', () => close(false));
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) close(false);
        });

        // Escape to cancel
        const onKey = (e) => {
            if (e.key === 'Escape') { close(false); document.removeEventListener('keydown', onKey); }
            if (e.key === 'Enter')  { close(true);  document.removeEventListener('keydown', onKey); }
        };
        document.addEventListener('keydown', onKey);

        overlay.querySelector('#modal-confirm').focus();
    });
}

/**
 * Modal de alerta simples (substitui alert()).
 */
export function alertModal(opts = {}) {
    const {
        title = 'Aviso',
        message = '',
        buttonText = 'OK',
        variant = 'info',
    } = opts;

    return new Promise((resolve) => {
        const overlay = _ensureContainer();
        const iconMap = {
            danger:  `<svg width="28" height="28" fill="none" stroke="#f87171" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
            warning: `<svg width="28" height="28" fill="none" stroke="#fbbf24" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`,
            info:    `<svg width="28" height="28" fill="none" stroke="#60a5fa" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>`,
            success: `<svg width="28" height="28" fill="none" stroke="#34d399" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`,
        };

        overlay.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-icon">${iconMap[variant] ?? iconMap.info}</div>
                <div class="modal-content">
                    <h3 class="modal-title">${_esc(title)}</h3>
                    <p class="modal-message">${message}</p>
                </div>
                <div class="modal-actions">
                    <button class="modal-btn modal-btn--primary" id="modal-ok">${_esc(buttonText)}</button>
                </div>
            </div>
        `;

        overlay.classList.remove('hidden');
        requestAnimationFrame(() => overlay.classList.add('open'));

        const close = () => {
            overlay.classList.remove('open');
            setTimeout(() => {
                overlay.classList.add('hidden');
                overlay.innerHTML = '';
            }, 200);
            resolve();
        };

        overlay.querySelector('#modal-ok').addEventListener('click', close);
        overlay.addEventListener('click', (e) => { if (e.target === overlay) close(); });
        document.addEventListener('keydown', function onKey(e) {
            if (e.key === 'Escape' || e.key === 'Enter') { close(); document.removeEventListener('keydown', onKey); }
        });

        overlay.querySelector('#modal-ok').focus();
    });
}

function _esc(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

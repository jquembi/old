/**
 * Menu de contexto (botão direito) para listagens, adaptado à área da aplicação.
 *
 * Em qualquer linha de tabela (`tbody tr`) o menu é montado automaticamente a partir
 * das ações existentes na própria linha (links e botões) e complementado com ações
 * comuns (copiar, abrir em novo separador, atualizar) específicas de cada área.
 * Para excluir uma linha/tabela basta usar o atributo `data-no-ctx`.
 */
const AREAS = {
    nacional: { rotulo: 'Nacional', cor: '#0f766e' },
    erp: { rotulo: 'ERP', cor: '#7c3aed' },
    municipio: { rotulo: 'Município', cor: '#0369a1' },
    municipios: { rotulo: 'Territorial', cor: '#0369a1' },
    croqui: { rotulo: 'Operações', cor: '#b45309' },
    documentos: { rotulo: 'Documentos', cor: '#be123c' },
    usuarios: { rotulo: 'Administração', cor: '#475569' },
    permissoes: { rotulo: 'Administração', cor: '#475569' },
    auditoria: { rotulo: 'Auditoria', cor: '#475569' },
    sistema: { rotulo: 'Sistema', cor: '#475569' },
    'erros-sistema': { rotulo: 'Sistema', cor: '#475569' },
    lixeira: { rotulo: 'Lixeira', cor: '#475569' },
};

const PERIGO = /(elimin|remov|apag|exclu|delet|arquiv|revog|desativ|inativ|cancel)/i;
const ICONES = {
    copiar: '<path d="M8 8h10v12H8zM6 16H5a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v1"/>',
    abrir: '<path d="M14 4h6v6M20 4l-9 9M10 6H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4"/>',
    atualizar: '<path d="M20 11a8 8 0 1 0-2.3 5.7M20 4v7h-7"/>',
    acao: '<path d="M5 12h14M13 6l6 6-6 6"/>',
    perigo: '<path d="M4 7h16M10 11v6M14 11v6M6 7l1 12h10l1-12M9 7V4h6v3"/>',
};

const svg = (d) => `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${d}</svg>`;

let menu = null;

function areaAtual() {
    const seg = location.pathname.split('/').filter(Boolean)[0] ?? '';
    return AREAS[seg] ?? { rotulo: 'Lista', cor: '#52525b' };
}

function rotuloDe(el) {
    const txt = (el.getAttribute('aria-label') || el.getAttribute('title') || el.textContent || '').replace(/\s+/g, ' ').trim();
    if (txt) return txt;
    const w = el.getAttribute('wire:click') || '';
    return w.replace(/\(.*$/, '').replace(/([A-Z])/g, ' $1').replace(/[_-]/g, ' ').trim() || 'Ação';
}

function recolherAcoes(linha) {
    const vistos = new Set();
    const itens = [];
    linha.querySelectorAll('a[href], button, [wire\\:click]').forEach((el) => {
        if (el.closest('[data-no-ctx]') || el.disabled || el.getAttribute('aria-disabled') === 'true') return;
        if (el.tagName === 'BUTTON' && el.type === 'submit' && !el.hasAttribute('wire:click')) return;
        const href = el.getAttribute('href');
        if (href && (href === '#' || href.startsWith('javascript:'))) return;

        const rotulo = rotuloDe(el);
        const chave = `${rotulo}|${href ?? el.getAttribute('wire:click') ?? ''}`;
        if (!rotulo || vistos.has(chave)) return;
        vistos.add(chave);

        const perigo = PERIGO.test(rotulo) || PERIGO.test(el.getAttribute('wire:click') || '');
        const icone = el.querySelector('svg');
        itens.push({
            rotulo: rotulo.length > 38 ? `${rotulo.slice(0, 36)}…` : rotulo,
            icone: icone ? icone.outerHTML : svg(perigo ? ICONES.perigo : ICONES.acao),
            perigo,
            href,
            el,
        });
    });
    return itens;
}

function itensComuns(linha, href) {
    const celulas = [...linha.querySelectorAll('td')].map((c) => c.innerText.replace(/\s+/g, ' ').trim()).filter(Boolean);
    const itens = [];
    if (href) {
        itens.push({ rotulo: 'Abrir em novo separador', icone: svg(ICONES.abrir), executar: () => window.open(href, '_blank', 'noopener') });
    }
    if (celulas[0]) {
        const valor = celulas[0].length > 28 ? `${celulas[0].slice(0, 26)}…` : celulas[0];
        itens.push({ rotulo: `Copiar “${valor}”`, icone: svg(ICONES.copiar), executar: () => navigator.clipboard?.writeText(celulas[0]) });
    }
    if (celulas.length > 1) {
        itens.push({ rotulo: 'Copiar linha', icone: svg(ICONES.copiar), executar: () => navigator.clipboard?.writeText(celulas.join('\t')) });
    }
    return itens;
}

function fechar() {
    if (menu) {
        menu.remove();
        menu = null;
    }
}

function abrir(x, y, area, titulo, grupos) {
    fechar();
    menu = document.createElement('div');
    menu.className = 'ctxm';
    menu.setAttribute('role', 'menu');
    menu.style.setProperty('--ctxm-cor', area.cor);

    const cab = document.createElement('div');
    cab.className = 'ctxm-head';
    cab.innerHTML = '<span class="ctxm-dot"></span><span class="ctxm-area"></span><span class="ctxm-title"></span>';
    cab.querySelector('.ctxm-area').textContent = area.rotulo;
    cab.querySelector('.ctxm-title').textContent = titulo;
    menu.appendChild(cab);

    grupos.filter((g) => g.length).forEach((grupo, i) => {
        if (i > 0) menu.insertAdjacentHTML('beforeend', '<div class="ctxm-sep"></div>');
        grupo.forEach((it) => {
            const b = document.createElement('button');
            b.type = 'button';
            b.setAttribute('role', 'menuitem');
            b.className = `ctxm-item${it.perigo ? ' is-danger' : ''}`;
            b.innerHTML = `<span class="ctxm-ico">${it.icone}</span><span class="ctxm-label"></span>`;
            b.querySelector('.ctxm-label').textContent = it.rotulo;
            b.addEventListener('click', (e) => {
                e.stopPropagation();
                fechar();
                if (it.executar) it.executar();
                else it.el?.click();
            });
            menu.appendChild(b);
        });
    });

    document.body.appendChild(menu);
    const r = menu.getBoundingClientRect();
    menu.style.left = `${Math.max(8, Math.min(x, innerWidth - r.width - 8))}px`;
    menu.style.top = `${Math.max(8, Math.min(y, innerHeight - r.height - 8))}px`;
    menu.querySelector('.ctxm-item')?.focus();
}

document.addEventListener('contextmenu', (e) => {
    if (e.shiftKey || e.target.closest('input, textarea, select, [contenteditable], [data-no-ctx], .ctxm')) return;
    const linha = e.target.closest('tbody tr');
    if (!linha || linha.closest('table')?.hasAttribute('data-no-ctx')) return;

    const acoes = recolherAcoes(linha);
    const principal = acoes.find((a) => a.href && !a.perigo);
    const celulas = [...linha.querySelectorAll('td')].map((c) => c.innerText.replace(/\s+/g, ' ').trim()).filter(Boolean);
    if (!acoes.length && !celulas.length) return;

    e.preventDefault();
    const normais = acoes.filter((a) => !a.perigo);
    const perigosas = acoes.filter((a) => a.perigo);
    const titulo = celulas[0] ? (celulas[0].length > 30 ? `${celulas[0].slice(0, 28)}…` : celulas[0]) : '';
    abrir(e.clientX, e.clientY, areaAtual(), titulo, [
        normais,
        itensComuns(linha, principal?.href),
        [{ rotulo: 'Atualizar lista', icone: svg(ICONES.atualizar), executar: () => window.Livewire ? window.Livewire.all().forEach((c) => c.$wire.$refresh()) : location.reload() }],
        perigosas,
    ]);
});

['click', 'scroll', 'resize', 'blur'].forEach((ev) => window.addEventListener(ev, (e) => {
    if (!e.target?.closest?.('.ctxm')) fechar();
}, true));
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') fechar();
});
document.addEventListener('livewire:navigating', fechar);

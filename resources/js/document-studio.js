import QRCode from 'qrcode';

const PAGINAS = { retrato: [210, 297], paisagem: [297, 210] };
const ROTULOS = {
    logo: 'Logótipo', texto: 'Texto', qr: 'QR Code', linha: 'Linha', retangulo: 'Retângulo', marca_agua: 'Marca de água',
};

const PADRAO = {
    texto: { w: 40, h: 4, props: { texto: 'Novo texto', tamanho: 11, negrito: false, italico: false, alinhamento: 'L', cor: '#18181b', fonte: 'Helvetica' } },
    logo: { w: 12, h: 7, props: {} },
    qr: { w: 9, h: 6.4, props: { cor: '#18181b', cor_fundo: '#ffffff', moldura: true, cor_moldura: '#e4e4e7', rotulo: 'AUTENTICIDADE', nivel: 'M', quadrado: true } },
    linha: { w: 88, h: 0.2, props: { cor: '#d4d4d8', espessura: 0.4 } },
    retangulo: { w: 30, h: 8, props: { cor_fundo: '#f4f4f5', cor_borda: '#d4d4d8', espessura: 0.3 } },
    marca_agua: { w: 80, h: 18, props: { texto: 'CONFIDENCIAL', tamanho: 60, angulo: 45, cor: '#e4e4e7' } },
};

const clone = (o) => JSON.parse(JSON.stringify(o));
const uid = () => `b${Date.now().toString(36)}${Math.random().toString(36).slice(2, 5)}`;
const arred = (n) => Math.round(n * 100) / 100;

export function documentoStudio(cfg) {
    return {
        blocos: clone(cfg.blocos ?? []),
        orientacao: cfg.orientacao ?? 'retrato',
        inst: cfg.institucional ?? {},
        logo: cfg.logo,
        tipoNome: cfg.tipoNome ?? '',
        modelos: cfg.modelos ?? {},
        sel: null,
        zoom: 100,
        grelha: true,
        encaixe: true,
        sujo: false,
        historico: [],
        futuro: [],
        guiaX: false,
        guiaY: false,
        ctx: { aberto: false, x: 0, y: 0, itens: [], ponto: null },
        area: cfg.area ?? 'Documentos',
        areaCor: cfg.areaCor ?? '#be123c',
        copia: null,
        arrasto: null,

        init() {
            window.addEventListener('keydown', (e) => this.teclas(e));
            window.addEventListener('beforeunload', (e) => {
                if (this.sujo) e.preventDefault();
            });
            this.$watch('orientacao', () => this.marcar());
        },

        get dim() { return PAGINAS[this.orientacao] ?? PAGINAS.retrato; },
        get selecionado() { return this.blocos.find((b) => b.id === this.sel) ?? null; },
        rotulo(t) { return ROTULOS[t] ?? t; },
        get ordenados() { return this.blocos; },

        // ── Estado / histórico ──
        marcar() { this.sujo = true; },
        snapshot() {
            this.historico.push(JSON.stringify(this.blocos));
            if (this.historico.length > 80) this.historico.shift();
            this.futuro = [];
            this.sujo = true;
        },
        desfazer() {
            if (!this.historico.length) return;
            this.futuro.push(JSON.stringify(this.blocos));
            this.blocos = JSON.parse(this.historico.pop());
            if (!this.selecionado) this.sel = null;
        },
        refazer() {
            if (!this.futuro.length) return;
            this.historico.push(JSON.stringify(this.blocos));
            this.blocos = JSON.parse(this.futuro.pop());
        },

        // ── Blocos ──
        quadrado(h, w) { return arred(w * this.dim[0] / this.dim[1]); },
        adicionar(tipo, x = null, y = null) {
            this.snapshot();
            const p = clone(PADRAO[tipo]);
            if (tipo === 'qr') p.h = this.quadrado(p.h, p.w);
            const b = {
                id: uid(), tipo, w: p.w, h: tipo === 'qr' ? this.quadrado(0, p.w) : p.h,
                x: arred(Math.max(0, Math.min(100 - p.w, x ?? (100 - p.w) / 2))),
                y: arred(Math.max(0, Math.min(100 - p.h, y ?? 40))),
                pagina: 'todas', bloqueado: false, oculto: false, props: p.props,
            };
            this.blocos.push(b);
            this.sel = b.id;
            return b;
        },
        remover(id = this.sel) {
            const b = this.blocos.find((i) => i.id === id);
            if (!b || b.bloqueado) return;
            this.snapshot();
            this.blocos = this.blocos.filter((i) => i.id !== id);
            if (this.sel === id) this.sel = null;
        },
        duplicar(id = this.sel) {
            const b = this.blocos.find((i) => i.id === id);
            if (!b) return;
            this.snapshot();
            const c = clone(b);
            c.id = uid();
            c.x = arred(Math.min(100 - c.w, c.x + 2));
            c.y = arred(Math.min(100 - c.h, c.y + 2));
            c.bloqueado = false;
            this.blocos.push(c);
            this.sel = c.id;
        },
        copiar(id = this.sel) {
            const b = this.blocos.find((i) => i.id === id);
            if (b) this.copia = clone(b);
        },
        colar(x = null, y = null) {
            if (!this.copia) return;
            this.snapshot();
            const c = clone(this.copia);
            c.id = uid();
            c.x = arred(x !== null ? Math.min(100 - c.w, x) : Math.min(100 - c.w, c.x + 2));
            c.y = arred(y !== null ? Math.min(100 - c.h, y) : Math.min(100 - c.h, c.y + 2));
            this.blocos.push(c);
            this.sel = c.id;
        },
        ordem(id, modo) {
            const i = this.blocos.findIndex((b) => b.id === id);
            if (i < 0) return;
            this.snapshot();
            const [b] = this.blocos.splice(i, 1);
            const alvo = { frente: this.blocos.length, tras: 0, sobe: Math.min(this.blocos.length, i + 1), desce: Math.max(0, i - 1) }[modo];
            this.blocos.splice(alvo, 0, b);
        },
        bloquear(id = this.sel) {
            const b = this.blocos.find((i) => i.id === id);
            if (b) { this.snapshot(); b.bloqueado = !b.bloqueado; }
        },
        ocultar(id = this.sel) {
            const b = this.blocos.find((i) => i.id === id);
            if (b) { this.snapshot(); b.oculto = !b.oculto; }
        },
        alinhar(modo, id = this.sel) {
            const b = this.blocos.find((i) => i.id === id);
            if (!b || b.bloqueado) return;
            this.snapshot();
            const m = 4;
            if (modo === 'esq') b.x = m;
            if (modo === 'dir') b.x = arred(100 - b.w - m);
            if (modo === 'centro-h') b.x = arred((100 - b.w) / 2);
            if (modo === 'topo') b.y = m;
            if (modo === 'base') b.y = arred(100 - b.h - m);
            if (modo === 'centro-v') b.y = arred((100 - b.h) / 2);
        },
        limparTudo() {
            if (!this.blocos.length) return;
            this.snapshot();
            this.blocos = [];
            this.sel = null;
        },
        inserirModelo(nome) {
            const itens = clone(this.modelos[nome] ?? []);
            if (!itens.length) return;
            this.snapshot();
            this.blocos = this.blocos.filter((b) => !b.id.startsWith(nome === 'cabecalho' ? 'cab-' : 'rod-'));
            this.blocos.push(...itens);
        },
        mudarOrientacao(o) {
            if (o === this.orientacao) return;
            this.snapshot();
            this.orientacao = o;
            this.blocos.filter((b) => b.tipo === 'qr').forEach((b) => { b.h = this.quadrado(0, b.w); });
        },
        definirLargura(b, w) {
            b.w = arred(Math.max(1, Math.min(100 - b.x, Number(w) || 1)));
            if (b.tipo === 'qr') b.h = this.quadrado(0, b.w);
            this.sujo = true;
        },

        // ── Texto e pré-visualização ──
        textoReal(b) {
            const v = {
                pagina: '1', total: '3', data: new Date().toLocaleDateString('pt-PT'), hora: '09:30',
                tipo: this.tipoNome, titulo: 'Título do documento',
                orgao: this.inst.orgao, subtitulo: this.inst.subtitulo, morada: this.inst.morada, contactos: this.inst.contactos,
            };
            return String(b.props?.texto ?? '').replace(/\{\{(\w+)\}\}/g, (_, k) => v[k] ?? '');
        },
        fonteCss(b) {
            const mm = (Number(b.props?.tamanho) || 10) * 0.3528;
            return `${(mm / this.dim[0] * 100).toFixed(4)}cqw`;
        },
        alinhCss(b) { return { L: 'left', C: 'center', R: 'right', J: 'justify' }[b.props?.alinhamento] ?? 'left'; },
        espessuraCss(v) { return `${(((Number(v) || 0.3)) / this.dim[0] * 100).toFixed(4)}cqw`; },
        qrCaminho(b) {
            try {
                const qr = QRCode.create('https://inotu.ao/verificar/EXEMPLO', { errorCorrectionLevel: b.props?.nivel || 'M' });
                const n = qr.modules.size;
                let d = '';
                for (let y = 0; y < n; y++) for (let x = 0; x < n; x++) if (qr.modules.data[y * n + x]) d += `M${x} ${y}h1v1h-1z`;
                return { n, d };
            } catch {
                return { n: 1, d: '' };
            }
        },
        estiloBloco(b) {
            const rot = b.tipo === 'marca_agua' ? `transform:rotate(${-(Number(b.props?.angulo) || 0)}deg);` : '';
            return `left:${b.x}%;top:${b.y}%;width:${b.w}%;height:${b.h}%;${rot}`;
        },

        // ── Interação (arrastar / redimensionar) ──
        selecionar(b, e) {
            this.sel = b.id;
            this.fecharMenu();
            if (b.bloqueado || e.button !== 0) return;
            this.iniciar('mover', b, e);
        },
        iniciar(modo, b, e, alca = '') {
            e.preventDefault();
            e.stopPropagation();
            this.snapshot();
            const r = this.$refs.pagina.getBoundingClientRect();
            this.arrasto = { modo, alca, id: b.id, r, x0: e.clientX, y0: e.clientY, bx: b.x, by: b.y, bw: b.w, bh: b.h, mexeu: false };
            const mover = (ev) => this.mover(ev);
            const fim = () => {
                window.removeEventListener('pointermove', mover);
                window.removeEventListener('pointerup', fim);
                if (this.arrasto && !this.arrasto.mexeu) this.historico.pop();
                this.arrasto = null;
                this.guiaX = this.guiaY = false;
            };
            window.addEventListener('pointermove', mover);
            window.addEventListener('pointerup', fim);
        },
        encaixar(v) { return this.encaixe ? Math.round(v * 2) / 2 : v; },
        mover(e) {
            const a = this.arrasto;
            if (!a) return;
            const b = this.blocos.find((i) => i.id === a.id);
            if (!b) return;
            const dx = (e.clientX - a.x0) / a.r.width * 100;
            const dy = (e.clientY - a.y0) / a.r.height * 100;
            if (Math.abs(dx) + Math.abs(dy) > 0.05) a.mexeu = true;

            if (a.modo === 'mover') {
                b.x = arred(Math.max(0, Math.min(100 - b.w, this.encaixar(a.bx + dx))));
                b.y = arred(Math.max(0, Math.min(100 - b.h, this.encaixar(a.by + dy))));
                this.guiaX = Math.abs(b.x + b.w / 2 - 50) < 0.6;
                this.guiaY = Math.abs(b.y + b.h / 2 - 50) < 0.6;
                if (this.guiaX) b.x = arred(50 - b.w / 2);
                if (this.guiaY) b.y = arred(50 - b.h / 2);
            } else {
                let { bx: x, by: y, bw: w, bh: h } = a;
                if (a.alca.includes('e')) w = a.bw + dx;
                if (a.alca.includes('s')) h = a.bh + dy;
                if (a.alca.includes('w')) { x = a.bx + dx; w = a.bw - dx; }
                if (a.alca.includes('n')) { y = a.by + dy; h = a.bh - dy; }
                w = Math.max(1, this.encaixar(w));
                h = Math.max(0.2, this.encaixar(h));
                if (b.tipo === 'qr') h = this.quadrado(0, w);
                if (b.tipo === 'linha' && !a.alca.match(/[ns]/)) h = a.bh;
                if (a.alca.includes('w')) x = a.bx + a.bw - w;
                if (a.alca.includes('n')) y = a.by + a.bh - h;
                b.x = arred(Math.max(0, x)); b.y = arred(Math.max(0, y));
                b.w = arred(Math.min(w, 100 - b.x)); b.h = arred(Math.min(h, 100 - b.y));
                if (b.tipo === 'qr') b.h = this.quadrado(0, b.w);
            }
            this.sujo = true;
        },
        teclas(e) {
            if (!this.$root.isConnected || e.target.closest('input, textarea, select, [contenteditable]')) return;
            const mod = e.ctrlKey || e.metaKey;
            const b = this.selecionado;
            if (mod && e.key.toLowerCase() === 'z') { e.preventDefault(); e.shiftKey ? this.refazer() : this.desfazer(); return; }
            if (mod && e.key.toLowerCase() === 'y') { e.preventDefault(); this.refazer(); return; }
            if (mod && e.key.toLowerCase() === 's') { e.preventDefault(); this.$dispatch('studio-guardar'); return; }
            if (mod && e.key.toLowerCase() === 'c' && b) { this.copiar(); return; }
            if (mod && e.key.toLowerCase() === 'v') { this.colar(); return; }
            if (mod && e.key.toLowerCase() === 'd' && b) { e.preventDefault(); this.duplicar(); return; }
            if (!b) return;
            if (e.key === 'Delete' || e.key === 'Backspace') { e.preventDefault(); this.remover(); return; }
            if (e.key === 'Escape') { this.sel = null; return; }
            const passo = e.shiftKey ? 2 : 0.25;
            const mapa = { ArrowLeft: ['x', -passo], ArrowRight: ['x', passo], ArrowUp: ['y', -passo], ArrowDown: ['y', passo] };
            if (mapa[e.key] && !b.bloqueado) {
                e.preventDefault();
                const [eixo, d] = mapa[e.key];
                b[eixo] = arred(Math.max(0, Math.min(100 - (eixo === 'x' ? b.w : b.h), b[eixo] + d)));
                this.sujo = true;
            }
        },

        // ── Menu de contexto (adaptado à área) ──
        menuCanvas(e, b = null) {
            e.preventDefault();
            e.stopPropagation();
            const r = this.$refs.pagina.getBoundingClientRect();
            const px = (e.clientX - r.left) / r.width * 100;
            const py = (e.clientY - r.top) / r.height * 100;
            let itens;
            if (b) {
                this.sel = b.id;
                itens = [
                    { rotulo: 'Duplicar', atalho: 'Ctrl+D', icone: 'M8 8h10v12H8zM6 16H5a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v1', fn: () => this.duplicar(b.id) },
                    { rotulo: 'Copiar', atalho: 'Ctrl+C', icone: 'M8 8h10v12H8z', fn: () => this.copiar(b.id) },
                    { sep: true },
                    { rotulo: 'Trazer para a frente', icone: 'M12 5v14M6 11l6-6 6 6', fn: () => this.ordem(b.id, 'frente') },
                    { rotulo: 'Enviar para trás', icone: 'M12 5v14M6 13l6 6 6-6', fn: () => this.ordem(b.id, 'tras') },
                    { sep: true },
                    { rotulo: 'Centrar na horizontal', icone: 'M12 4v16M6 8h12M8 16h8', fn: () => this.alinhar('centro-h', b.id) },
                    { rotulo: 'Centrar na vertical', icone: 'M4 12h16M8 6v12M16 8v8', fn: () => this.alinhar('centro-v', b.id) },
                    { rotulo: 'Alinhar à esquerda', icone: 'M5 4v16M9 8h10M9 16h6', fn: () => this.alinhar('esq', b.id) },
                    { rotulo: 'Alinhar à direita', icone: 'M19 4v16M5 8h10M9 16h6', fn: () => this.alinhar('dir', b.id) },
                    { sep: true },
                    { rotulo: b.bloqueado ? 'Desbloquear' : 'Bloquear posição', icone: 'M7 11V8a5 5 0 0 1 10 0v3M6 11h12v9H6z', fn: () => this.bloquear(b.id) },
                    { rotulo: b.oculto ? 'Mostrar no documento' : 'Ocultar do documento', icone: 'M3 12s3.5-6 9-6 9 6 9 6-3.5 6-9 6-9-6-9-6zM12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6', fn: () => this.ocultar(b.id) },
                    { sep: true },
                    { rotulo: 'Eliminar', atalho: 'Del', perigo: true, icone: 'M4 7h16M10 11v6M14 11v6M6 7l1 12h10l1-12M9 7V4h6v3', fn: () => this.remover(b.id) },
                ];
            } else {
                this.sel = null;
                itens = [
                    { rotulo: 'Adicionar texto aqui', icone: 'M5 6V4h14v2M12 4v16M9 20h6', fn: () => this.adicionar('texto', px, py) },
                    { rotulo: 'Adicionar logótipo aqui', icone: 'M4 5h16v14H4zM4 15l5-5 4 4 3-3 4 4', fn: () => this.adicionar('logo', px, py) },
                    { rotulo: 'Adicionar QR Code aqui', icone: 'M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h3v3h-3zM18 18h2v2h-2z', fn: () => this.adicionar('qr', px, py) },
                    { rotulo: 'Adicionar linha aqui', icone: 'M4 12h16', fn: () => this.adicionar('linha', px, py) },
                    { rotulo: 'Adicionar retângulo aqui', icone: 'M4 6h16v12H4z', fn: () => this.adicionar('retangulo', px, py) },
                    { sep: true },
                    { rotulo: 'Colar', atalho: 'Ctrl+V', icone: 'M9 4h6v3H9zM7 6H5v14h14V6h-2', desativado: !this.copia, fn: () => this.colar(px, py) },
                    { rotulo: 'Inserir cabeçalho institucional', icone: 'M4 5h16v5H4z', fn: () => this.inserirModelo('cabecalho') },
                    { rotulo: 'Inserir rodapé institucional', icone: 'M4 14h16v5H4z', fn: () => this.inserirModelo('rodape') },
                    { sep: true },
                    { rotulo: this.grelha ? 'Ocultar grelha' : 'Mostrar grelha', icone: 'M4 4h16v16H4zM4 12h16M12 4v16', fn: () => { this.grelha = !this.grelha; } },
                    { rotulo: 'Desfazer', atalho: 'Ctrl+Z', icone: 'M9 14L4 9l5-5M4 9h10a6 6 0 0 1 0 12h-3', desativado: !this.historico.length, fn: () => this.desfazer() },
                    { rotulo: 'Limpar página', perigo: true, icone: 'M4 7h16M6 7l1 12h10l1-12M9 7V4h6v3', desativado: !this.blocos.length, fn: () => this.limparTudo() },
                ];
            }
            const menuW = 240;
            this.ctx = {
                aberto: true,
                x: Math.min(e.clientX, innerWidth - menuW - 12),
                y: Math.min(e.clientY, innerHeight - itens.length * 34 - 12),
                itens,
            };
        },
        fecharMenu() { this.ctx.aberto = false; },
        executar(it) {
            this.fecharMenu();
            if (!it.desativado) it.fn?.();
        },
    };
}

window.documentoStudio = documentoStudio;

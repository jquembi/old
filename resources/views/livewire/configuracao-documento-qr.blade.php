<div class="px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6" x-data="{
    tipo: @entangle('tipoSelecionado'),
    posX: @entangle('posicao_x_pct'),
    posY: @entangle('posicao_y_pct'),
    largura: @entangle('largura_pct'),
    corQr: @entangle('cor_qr'),
    corFundo: @entangle('cor_fundo'),
    rotulo: @entangle('texto_rotulo'),
    incluirRotulo: @entangle('incluir_rotulo'),
    incluirMoldura: @entangle('incluir_moldura'),
    corMoldura: @entangle('cor_moldura'),
    orientacao: @entangle('orientacao'),
    isDragging: false,
    isResizing: false,
    resizeHandle: null,
    dragStartX: 0,
    dragStartY: 0,
    initPosX: 0,
    initPosY: 0,
    initWidth: 0,
    modalPreviewAberta: false,
    zoomPreview: 100,
    qrUrlExemplo: '{{ url('/documentos/verificar/EXEMPLO-OFICIAL-ANGOLA-GTERRAS') }}',

    init() {
        this.$nextTick(() => {
            this.atualizarQrPreview();
        });

        this.$watch('corQr', () => {
            this.atualizarQrPreview();
            if (this.modalPreviewAberta) this.atualizarQrModal();
        });
        this.$watch('corFundo', () => {
            this.atualizarQrPreview();
            if (this.modalPreviewAberta) this.atualizarQrModal();
        });
        this.$watch('tipo', () => {
            this.$nextTick(() => {
                this.atualizarQrPreview();
                if (this.modalPreviewAberta) this.atualizarQrModal();
            });
        });
    },

    atualizarQrPreview() {
        const canvas = this.$refs.qrCanvas;
        if (!canvas) return;
        window.renderQrCode(canvas, this.qrUrlExemplo, {
            size: 160,
            cor_qr: this.corQr || '#18181b',
            cor_fundo: this.corFundo || '#ffffff',
            margin: 1,
            nivel_correcao: 'M'
        });
    },

    abrirModalPreview() {
        this.modalPreviewAberta = true;
        this.$nextTick(() => {
            this.atualizarQrModal();
        });
    },

    fecharModalPreview() {
        this.modalPreviewAberta = false;
    },

    atualizarQrModal() {
        const modalCanvas = this.$refs.qrModalCanvas;
        if (!modalCanvas) return;
        window.renderQrCode(modalCanvas, this.qrUrlExemplo, {
            size: 240,
            cor_qr: this.corQr || '#18181b',
            cor_fundo: this.corFundo || '#ffffff',
            margin: 1,
            nivel_correcao: 'M'
        });
    },

    startDrag(e) {
        // Se estiver clicando numa alça de redimensionamento, não inicia arrastar
        if (e.target.closest('[data-resize-handle]')) return;

        this.isDragging = true;
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        this.dragStartX = clientX;
        this.dragStartY = clientY;
        this.initPosX = parseFloat(this.posX) || 0;
        this.initPosY = parseFloat(this.posY) || 0;

        const onMove = (moveEvent) => {
            if (!this.isDragging) return;
            const curX = moveEvent.touches ? moveEvent.touches[0].clientX : moveEvent.clientX;
            const curY = moveEvent.touches ? moveEvent.touches[0].clientY : moveEvent.clientY;
            const sheet = this.$refs.paperSheet;
            if (!sheet) return;
            const rect = sheet.getBoundingClientRect();

            const deltaXPct = ((curX - this.dragStartX) / rect.width) * 100;
            const deltaYPct = ((curY - this.dragStartY) / rect.height) * 100;

            const qrLargura = parseFloat(this.largura) || 15;
            const maxXPct = 100 - qrLargura;
            const maxYPct = 100 - (qrLargura * 1.3); // estimativa com moldura e rótulo

            let newX = Math.max(0, Math.min(maxXPct, this.initPosX + deltaXPct));
            let newY = Math.max(0, Math.min(maxYPct, this.initPosY + deltaYPct));

            this.posX = Math.round(newX * 10) / 10;
            this.posY = Math.round(newY * 10) / 10;
        };

        const onUp = () => {
            if (this.isDragging) {
                this.isDragging = false;
                $wire.atualizarPosicaoInterativa(this.posX, this.posY, this.largura);
            }
            window.removeEventListener('mousemove', onMove);
            window.removeEventListener('mouseup', onUp);
            window.removeEventListener('touchmove', onMove);
            window.removeEventListener('touchend', onUp);
        };

        window.addEventListener('mousemove', onMove);
        window.addEventListener('mouseup', onUp);
        window.addEventListener('touchmove', onMove);
        window.addEventListener('touchend', onUp);
    },

    startResize(e, handle) {
        e.stopPropagation();
        e.preventDefault();
        this.isResizing = true;
        this.resizeHandle = handle;

        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        this.dragStartX = clientX;
        this.dragStartY = clientY;
        this.initWidth = parseFloat(this.largura) || 15;
        this.initPosX = parseFloat(this.posX) || 0;
        this.initPosY = parseFloat(this.posY) || 0;

        const onResizeMove = (moveEvent) => {
            if (!this.isResizing) return;
            const curX = moveEvent.touches ? moveEvent.touches[0].clientX : moveEvent.clientX;
            const sheet = this.$refs.paperSheet;
            if (!sheet) return;
            const rect = sheet.getBoundingClientRect();

            let deltaXPct = ((curX - this.dragStartX) / rect.width) * 100;

            let newWidth = this.initWidth;
            if (handle.includes('e')) { // este / right
                newWidth = this.initWidth + deltaXPct;
            } else if (handle.includes('w')) { // oeste / left
                newWidth = this.initWidth - deltaXPct;
            }

            // Manter dimensões entre 4% e 22% da folha (proporção discreta e legível)
            newWidth = Math.max(4, Math.min(22, newWidth));

            // Se redimensionando a partir da esquerda, ajusta posX para manter âncora à direita
            if (handle.includes('w')) {
                const diff = newWidth - this.initWidth;
                let adjustedX = this.initPosX - diff;
                adjustedX = Math.max(0, Math.min(95 - newWidth, adjustedX));
                this.posX = Math.round(adjustedX * 10) / 10;
            }

            // Garantir que não ultrapassa as margens da folha
            if (this.posX + newWidth > 98) {
                newWidth = 98 - this.posX;
            }

            this.largura = Math.round(newWidth * 10) / 10;
        };

        const onResizeUp = () => {
            if (this.isResizing) {
                this.isResizing = false;
                this.resizeHandle = null;
                $wire.atualizarPosicaoInterativa(this.posX, this.posY, this.largura);
            }
            window.removeEventListener('mousemove', onResizeMove);
            window.removeEventListener('mouseup', onResizeUp);
            window.removeEventListener('touchmove', onResizeMove);
            window.removeEventListener('touchend', onResizeUp);
        };

        window.addEventListener('mousemove', onResizeMove);
        window.addEventListener('mouseup', onResizeUp);
        window.addEventListener('touchmove', onResizeMove);
        window.addEventListener('touchend', onResizeUp);
    }
}">

    {{-- Cabeçalho da Página --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-2xl border border-zinc-200 bg-white p-4 sm:p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-tr from-red-600 to-amber-500 text-white shadow-lg shadow-red-600/20 shrink-0">
                <flux:icon.qr-code class="h-6 w-6" />
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-bold text-red-800 dark:bg-red-900/40 dark:text-red-300">
                        Layouts de Autenticidade
                    </span>
                    <span class="text-xs text-zinc-400">&bull;</span>
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">Posicionamento & Estilização</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-zinc-900 dark:text-white mt-1">
                    Personalização do Código QR por Tipo de Documento
                </h1>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                    Arraste e posicione o QR code no layout virtual de cada natureza de documento. Todos os documentos emitidos e baixados seguirão as coordenadas definidas aqui.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button
                type="button"
                @click="abrirModalPreview()"
                class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-100 transition shadow-2xs group relative"
                title="Pré-visualizar Documento"
                aria-label="Pré-visualizar Documento"
            >
                <flux:icon.eye class="h-4 w-4 text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform" />
            </button>
            <button
                type="button"
                wire:click="restaurarPadrao"
                wire:confirm="Tem a certeza que deseja repor a posição e estilo padrão deste tipo de documento?"
                class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 transition shadow-2xs group relative"
                title="Repor Valores Padrão"
                aria-label="Repor Valores Padrão"
            >
                <flux:icon.arrow-path class="h-4 w-4 text-zinc-500 dark:text-zinc-400 group-hover:rotate-180 transition-transform duration-300" />
            </button>
            <button
                type="button"
                wire:click="salvar"
                wire:loading.attr="disabled"
                class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-red-600 hover:bg-red-700 text-white transition shadow-sm shadow-red-600/20 disabled:opacity-50 group relative"
                title="Guardar Layout de Código QR"
                aria-label="Guardar Layout de Código QR"
            >
                <flux:icon.check class="h-4 w-4 group-hover:scale-110 transition-transform" wire:loading.remove wire:target="salvar" />
                <flux:icon.arrow-path class="h-4 w-4 animate-spin" wire:loading wire:target="salvar" />
            </button>
        </div>
    </div>

    {{-- Seletor de Tipo de Documento (Pills / Abas Superiores) --}}
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-3 shadow-sm">
        <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-2 px-1">
            Selecione o Tipo de Documento para Personalizar:
        </p>
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1">
            @foreach($todosTipos as $tipoItem)
                <button
                    type="button"
                    wire:click="selecionarTipo('{{ $tipoItem->tipo_documento }}')"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all border
                        {{ $tipoSelecionado === $tipoItem->tipo_documento
                            ? 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800 shadow-2xs font-bold'
                            : 'bg-zinc-50 text-zinc-600 border-zinc-200/80 hover:bg-zinc-100 dark:bg-zinc-800/60 dark:text-zinc-300 dark:border-zinc-700 dark:hover:bg-zinc-800' }}"
                >
                    <span class="h-2 w-2 rounded-full {{ $tipoSelecionado === $tipoItem->tipo_documento ? 'bg-red-600' : 'bg-zinc-400' }}"></span>
                    {{ $tipoItem->nome }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Grid Principal: Controles à Esquerda e Simulador Interativo à Direita --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- COLUNA LATERAL ESQUERDA: Painel de Controles & Parâmetros (4 Colunas) --}}
        <div class="lg:col-span-4 space-y-5">

            {{-- 1. Painel de Posição Rápida e Dimensões --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
                <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-white">Posição & Dimensão</h3>
                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Ajuste fino ou atalhos de cantos</p>
                    </div>
                    <span class="inline-flex items-center rounded-lg bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5 text-[10px] font-mono text-zinc-600 dark:text-zinc-300">
                        X: <span x-text="posX"></span>% • Y: <span x-text="posY"></span>%
                    </span>
                </div>

                {{-- Atalhos de Cantos --}}
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Atalhos de Encaixe:</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" wire:click="aplicarPosicaoPredefinida('superior_direito')" class="px-2.5 py-1.5 text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-left font-medium text-zinc-700 dark:text-zinc-300 flex items-center justify-between">
                            <span>Topo Direito</span>
                            <span class="text-[10px] text-zinc-400">↗</span>
                        </button>
                        <button type="button" wire:click="aplicarPosicaoPredefinida('superior_esquerdo')" class="px-2.5 py-1.5 text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-left font-medium text-zinc-700 dark:text-zinc-300 flex items-center justify-between">
                            <span>Topo Esquerdo</span>
                            <span class="text-[10px] text-zinc-400">↖</span>
                        </button>
                        <button type="button" wire:click="aplicarPosicaoPredefinida('inferior_direito')" class="px-2.5 py-1.5 text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-left font-medium text-zinc-700 dark:text-zinc-300 flex items-center justify-between">
                            <span>Base Direita</span>
                            <span class="text-[10px] text-zinc-400">↘</span>
                        </button>
                        <button type="button" wire:click="aplicarPosicaoPredefinida('inferior_esquerdo')" class="px-2.5 py-1.5 text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-left font-medium text-zinc-700 dark:text-zinc-300 flex items-center justify-between">
                            <span>Base Esquerda</span>
                            <span class="text-[10px] text-zinc-400">↙</span>
                        </button>
                    </div>
                </div>

                {{-- Sliders de Precisão --}}
                <div class="space-y-3 pt-2">
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-medium text-zinc-600 dark:text-zinc-400">Posição Horizontal (X):</span>
                            <span class="font-mono text-zinc-900 dark:text-white font-bold" x-text="posX + '%'"></span>
                        </div>
                        <input type="range" min="0" max="85" step="0.5" x-model.number="posX" class="w-full h-1.5 bg-zinc-200 rounded-lg appearance-none cursor-pointer dark:bg-zinc-700 accent-red-600">
                    </div>

                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-medium text-zinc-600 dark:text-zinc-400">Posição Vertical (Y):</span>
                            <span class="font-mono text-zinc-900 dark:text-white font-bold" x-text="posY + '%'"></span>
                        </div>
                        <input type="range" min="0" max="85" step="0.5" x-model.number="posY" class="w-full h-1.5 bg-zinc-200 rounded-lg appearance-none cursor-pointer dark:bg-zinc-700 accent-red-600">
                    </div>

                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-medium text-zinc-600 dark:text-zinc-400">Dimensão do Selo:</span>
                            <span class="font-mono text-zinc-900 dark:text-white font-bold" x-text="largura + '%'"></span>
                        </div>
                        <input type="range" min="4" max="20" step="0.5" x-model.number="largura" class="w-full h-1.5 bg-zinc-200 rounded-lg appearance-none cursor-pointer dark:bg-zinc-700 accent-red-600">
                        <div class="flex justify-between text-[9px] text-zinc-400 mt-0.5">
                            <span>4% (Compacto)</span>
                            <span>8% (Padrão)</span>
                            <span>20% (Grande)</span>
                        </div>
                    </div>
                </div>

                {{-- Regras de Aplicação no Documento --}}
                <div class="grid grid-cols-2 gap-3 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Orientação Padrão</label>
                        <select wire:model.live="orientacao" class="w-full text-xs rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 p-2 font-medium">
                            <option value="retrato">Retrato (Vertical)</option>
                            <option value="paisagem">Paisagem (Horizontal)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Páginas de Aplicação</label>
                        <select wire:model.live="pagina" class="w-full text-xs rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 p-2 font-medium">
                            <option value="primeira">Primeira Página</option>
                            <option value="ultima">Última Página</option>
                            <option value="todas">Todas as Páginas</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- 2. Painel de Estilização Visual e Cores --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
                <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-white">Estilização & Marca</h3>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Cores, moldura e texto oficial de autenticidade</p>
                </div>

                {{-- Seletor de Cores --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Cor do QR Code</label>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="corQr" class="h-8 w-10 p-0 rounded-lg cursor-pointer border border-zinc-200 dark:border-zinc-700 bg-transparent">
                            <input type="text" x-model="corQr" class="w-full text-xs font-mono uppercase rounded-lg border border-zinc-200 dark:border-zinc-700 p-1.5 bg-zinc-50 dark:bg-zinc-800">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Cor de Fundo</label>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="corFundo" class="h-8 w-10 p-0 rounded-lg cursor-pointer border border-zinc-200 dark:border-zinc-700 bg-transparent">
                            <input type="text" x-model="corFundo" class="w-full text-xs font-mono uppercase rounded-lg border border-zinc-200 dark:border-zinc-700 p-1.5 bg-zinc-50 dark:bg-zinc-800">
                        </div>
                    </div>
                </div>

                {{-- Moldura & Contorno --}}
                <div class="space-y-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="incluirMoldura" class="rounded border-zinc-300 text-red-600 focus:ring-red-500">
                        <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Exibir moldura protetora ao redor do QR</span>
                    </label>

                    <div x-show="incluirMoldura" class="flex items-center gap-2 pl-6">
                        <span class="text-xs text-zinc-500">Cor do contorno:</span>
                        <input type="color" x-model="corMoldura" class="h-6 w-8 p-0 rounded border border-zinc-200 bg-transparent cursor-pointer">
                        <input type="text" x-model="corMoldura" class="w-24 text-[11px] font-mono uppercase rounded border border-zinc-200 dark:border-zinc-700 p-1 bg-zinc-50 dark:bg-zinc-800">
                    </div>
                </div>

                {{-- Rótulo Textual Inferior --}}
                <div class="space-y-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="incluirRotulo" class="rounded border-zinc-300 text-red-600 focus:ring-red-500">
                        <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Incluir legenda inferior com texto de autenticidade</span>
                    </label>

                    <div x-show="incluirRotulo" class="pl-6 space-y-1">
                        <input
                            type="text"
                            x-model="rotulo"
                            placeholder="ex: AUTENTICIDADE • QR CODE"
                            class="w-full text-xs rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 p-2 font-medium"
                        >
                        <p class="text-[10px] text-zinc-400">Texto gravado logo abaixo do QR code no documento.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- COLUNA PRINCIPAL DIREITA: Simulador Interativo do Documento (8 Colunas) --}}
        <div class="lg:col-span-8 space-y-4">

            {{-- Barra de Ferramentas do Preview --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-zinc-900 p-3 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xs">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-xs font-bold text-zinc-800 dark:text-zinc-200">
                        <flux:icon.document-text class="h-4 w-4 text-red-600" />
                        {{ $nome }}
                    </span>
                    <span class="text-xs text-zinc-400">({{ ucfirst($orientacao) }} &bull; {{ $formato_papel }})</span>
                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-700 bg-emerald-50 dark:bg-emerald-950/40 dark:text-emerald-300 px-2 py-0.5 rounded-md border border-emerald-200 dark:border-emerald-800">
                        <flux:icon.check-circle class="h-3 w-3" />
                        Arrastar & Redimensionar Activo
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    {{-- Controles de Zoom da Prancheta --}}
                    <div class="inline-flex items-center rounded-lg border border-zinc-200 dark:border-zinc-700 p-0.5 bg-zinc-50 dark:bg-zinc-800">
                        <button
                            type="button"
                            @click="zoomPreview = Math.max(70, zoomPreview - 10)"
                            class="p-1 text-zinc-500 hover:text-zinc-900 dark:hover:text-white rounded"
                            title="Diminuir Zoom"
                        >
                            <flux:icon.minus class="h-3 w-3" />
                        </button>
                        <span class="px-1.5 text-[10px] font-mono font-bold text-zinc-600 dark:text-zinc-300" x-text="zoomPreview + '%'"></span>
                        <button
                            type="button"
                            @click="zoomPreview = Math.min(130, zoomPreview + 10)"
                            class="p-1 text-zinc-500 hover:text-zinc-900 dark:hover:text-white rounded"
                            title="Aumentar Zoom"
                        >
                            <flux:icon.plus class="h-3 w-3" />
                        </button>
                    </div>

                    {{-- Botão de Inverter Orientação (Apenas Ícone) --}}
                    <button
                        type="button"
                        @click="orientacao = (orientacao === 'retrato' ? 'paisagem' : 'retrato'); $wire.set('orientacao', orientacao)"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition text-zinc-700 dark:text-zinc-300"
                        title="Rodar Orientação da Folha (Retrato / Paisagem)"
                        aria-label="Rodar Orientação da Folha"
                    >
                        <flux:icon.arrow-path class="h-4 w-4" />
                    </button>

                    {{-- Botão de Visualização em Modal / Ecrã Inteiro (Apenas Ícone) --}}
                    <button
                        type="button"
                        @click="abrirModalPreview()"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:hover:bg-blue-900/60 dark:text-blue-200 border border-blue-200 dark:border-blue-800 transition shadow-2xs"
                        title="Ver Documento em Ecrã Inteiro"
                        aria-label="Ver Documento em Ecrã Inteiro"
                    >
                        <flux:icon.arrows-pointing-out class="h-4 w-4" />
                    </button>
                </div>
            </div>

            {{-- Mesa de Desenho / Prancheta Virtual --}}
            <div class="bg-zinc-200/70 dark:bg-zinc-950 p-4 sm:p-8 rounded-3xl border border-zinc-200 dark:border-zinc-800 flex items-center justify-center min-h-[580px] overflow-x-auto shadow-inner">
                
                {{-- Folha Simulada (A4 Retrato ou Paisagem) com suporte a Zoom --}}
                <div
                    x-ref="paperSheet"
                    class="relative bg-white text-zinc-900 shadow-2xl transition-all duration-300 border border-zinc-300 select-none overflow-hidden origin-center"
                    :style="(orientacao === 'retrato' 
                        ? 'width: 460px; height: 650px; aspect-ratio: 1 / 1.414;' 
                        : 'width: 650px; height: 460px; aspect-ratio: 1.414 / 1;') + 
                        ' transform: scale(' + (zoomPreview / 100) + ');'"
                >
                    {{-- Grid de Fundo Sutil de Guia --}}
                    <div class="absolute inset-0 bg-[linear-gradient(to_right,#f4f4f5_1px,transparent_1px),linear-gradient(to_bottom,#f4f4f5_1px,transparent_1px)] bg-[size:24px_24px] pointer-events-none opacity-60"></div>

                    {{-- Esqueleto de Conteúdo do Documento Simulado --}}
                    <div class="absolute inset-0 p-8 flex flex-col justify-between pointer-events-none opacity-85">
                        
                        {{-- Cabeçalho Oficial Simulado --}}
                        <div class="border-b-2 border-zinc-800 pb-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div class="h-7 w-7 rounded-full bg-red-600 flex items-center justify-center text-white text-[10px] font-bold">
                                        AO
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-wider text-zinc-900">República de Angola</p>
                                        <p class="text-[8px] text-zinc-500 uppercase">Governo Provincial • Gestão Territorial</p>
                                    </div>
                                </div>
                                <span class="text-[9px] font-mono text-zinc-400">DOC-REF: #2026/{{ strtoupper($tipoSelecionado) }}-001</span>
                            </div>
                        </div>

                        {{-- Corpo do Documento Simulado (Esboço técnico visual) --}}
                        <div class="flex-1 my-4 space-y-3">
                            <div class="space-y-1">
                                <div class="h-3.5 bg-zinc-800 rounded w-2/3"></div>
                                <div class="h-2 bg-zinc-200 rounded w-full"></div>
                                <div class="h-2 bg-zinc-200 rounded w-4/5"></div>
                            </div>

                            @if($tipoSelecionado === 'croqui')
                                {{-- Simulação de Planta / Malha de Terreno --}}
                                <div class="h-44 border border-dashed border-zinc-300 rounded-xl bg-zinc-50/70 p-3 flex flex-col justify-between">
                                    <span class="text-[8px] font-bold text-zinc-400 uppercase tracking-widest">Planta de Delimitação Territorial • Coordenadas UTM</span>
                                    <div class="flex items-center justify-center">
                                        <div class="h-20 w-32 border-2 border-red-600 bg-red-500/10 rounded rotate-6 flex items-center justify-center">
                                            <span class="text-[9px] font-bold text-red-700">Parcela #A12</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between text-[7px] font-mono text-zinc-400">
                                        <span>X: 345210.00 E</span>
                                        <span>Y: 8345910.00 N</span>
                                    </div>
                                </div>
                            @else
                                {{-- Simulação de Relatório / Despacho Textual --}}
                                <div class="space-y-2 pt-2">
                                    <div class="h-2 bg-zinc-100 rounded w-full"></div>
                                    <div class="h-2 bg-zinc-100 rounded w-full"></div>
                                    <div class="h-2 bg-zinc-100 rounded w-11/12"></div>
                                    <div class="h-2 bg-zinc-100 rounded w-full"></div>
                                    <div class="h-2 bg-zinc-100 rounded w-3/4"></div>
                                </div>
                            @endif

                            <div class="grid grid-cols-2 gap-3 pt-2">
                                <div class="p-2 border border-zinc-200 rounded-lg">
                                    <p class="text-[7px] text-zinc-400 font-bold uppercase">Requerente</p>
                                    <p class="text-[8px] font-semibold text-zinc-700">Exemplo da Silva</p>
                                </div>
                                <div class="p-2 border border-zinc-200 rounded-lg">
                                    <p class="text-[7px] text-zinc-400 font-bold uppercase">Município</p>
                                    <p class="text-[8px] font-semibold text-zinc-700">Lubango / Huíla</p>
                                </div>
                            </div>
                        </div>

                        {{-- Rodapé Simulado --}}
                        <div class="border-t border-zinc-200 pt-2 flex items-center justify-between text-[8px] text-zinc-400">
                            <span>G.TERRAS • Plataforma Integrada de Gestão Territorial</span>
                            <span>Página 1 de 1</span>
                        </div>
                    </div>

                    {{-- ELEMENTO ARRASTÁVEL E REDIMENSIONÁVEL: Bloco QR Code (Quadrado Perfeito) --}}
                    <div
                        @mousedown="startDrag($event)"
                        @touchstart="startDrag($event)"
                        class="absolute cursor-move select-none transition-shadow z-20 group"
                        :class="{
                            'shadow-2xl ring-2 ring-red-600 scale-102': isDragging,
                            'ring-2 ring-blue-500 shadow-xl': isResizing,
                            'hover:ring-2 hover:ring-red-400 hover:shadow-lg': !isDragging && !isResizing
                        }"
                        :style="{
                            left: posX + '%',
                            top: posY + '%',
                            width: largura + '%',
                        }"
                    >
                        {{-- Moldura Protetora do QR estritamente Quadrada (Aspect Ratio 1:1) --}}
                        <div
                            class="relative rounded-lg p-1 sm:p-1.5 w-full aspect-square flex flex-col items-center justify-between transition-colors overflow-hidden"
                            :style="{
                                backgroundColor: corFundo,
                                border: incluirMoldura ? '1px solid ' + corMoldura : 'none',
                                boxShadow: '0 2px 8px rgba(0,0,0,0.06)'
                            }"
                        >
                            {{-- Canvas com QR Code Real Renderizado que preenche perfeitamente o quadrado --}}
                            <div class="w-full flex-1 min-h-0 flex items-center justify-center">
                                <canvas
                                    x-ref="qrCanvas"
                                    class="max-w-full max-h-full aspect-square block rounded-xs pointer-events-none object-contain"
                                ></canvas>
                            </div>

                            {{-- Rótulo Textual Inferior --}}
                            <template x-if="incluirRotulo">
                                <div class="w-full shrink-0 text-center overflow-hidden leading-none pt-0.5">
                                    <p
                                        class="text-[5px] sm:text-[5.5px] font-black uppercase tracking-tight truncate leading-tight"
                                        :style="{ color: corQr }"
                                        x-text="rotulo"
                                    ></p>
                                </div>
                            </template>

                            {{-- ALÇAS INTERACTIVAS DE REDIMENSIONAMENTO (Handles nos 4 cantos e laterais) --}}
                            {{-- Canto Inferior Direito (SE) --}}
                            <div
                                data-resize-handle="se"
                                @mousedown="startResize($event, 'se')"
                                @touchstart="startResize($event, 'se')"
                                class="absolute -bottom-1.5 -right-1.5 h-3.5 w-3.5 bg-blue-600 hover:bg-blue-700 border-2 border-white rounded-full cursor-nwse-resize z-30 shadow-md transition-transform group-hover:scale-110 flex items-center justify-center"
                                title="Arrastar para redimensionar"
                            ></div>

                            {{-- Canto Superior Direito (NE) --}}
                            <div
                                data-resize-handle="ne"
                                @mousedown="startResize($event, 'ne')"
                                @touchstart="startResize($event, 'ne')"
                                class="absolute -top-1.5 -right-1.5 h-3 w-3 bg-blue-500 hover:bg-blue-600 border-2 border-white rounded-full cursor-nesw-resize z-30 shadow-md transition-transform opacity-0 group-hover:opacity-100"
                                title="Arrastar para redimensionar"
                            ></div>

                            {{-- Canto Inferior Esquerdo (SW) --}}
                            <div
                                data-resize-handle="sw"
                                @mousedown="startResize($event, 'sw')"
                                @touchstart="startResize($event, 'sw')"
                                class="absolute -bottom-1.5 -left-1.5 h-3.5 w-3.5 bg-blue-600 hover:bg-blue-700 border-2 border-white rounded-full cursor-nesw-resize z-30 shadow-md transition-transform group-hover:scale-110"
                                title="Arrastar para redimensionar"
                            ></div>

                            {{-- Canto Superior Esquerdo (NW) --}}
                            <div
                                data-resize-handle="nw"
                                @mousedown="startResize($event, 'nw')"
                                @touchstart="startResize($event, 'nw')"
                                class="absolute -top-1.5 -left-1.5 h-3 w-3 bg-blue-500 hover:bg-blue-600 border-2 border-white rounded-full cursor-nwse-resize z-30 shadow-md transition-transform opacity-0 group-hover:opacity-100"
                                title="Arrastar para redimensionar"
                            ></div>

                            {{-- Borda Direita (E) --}}
                            <div
                                data-resize-handle="e"
                                @mousedown="startResize($event, 'e')"
                                @touchstart="startResize($event, 'e')"
                                class="absolute top-1/2 -right-1.5 -translate-y-1/2 h-5 w-2 bg-blue-600 border border-white rounded-full cursor-ew-resize z-30 shadow-sm opacity-0 group-hover:opacity-100 transition-opacity"
                                title="Ajustar largura"
                            ></div>

                            {{-- Borda Esquerda (W) --}}
                            <div
                                data-resize-handle="w"
                                @mousedown="startResize($event, 'w')"
                                @touchstart="startResize($event, 'w')"
                                class="absolute top-1/2 -left-1.5 -translate-y-1/2 h-5 w-2 bg-blue-600 border border-white rounded-full cursor-ew-resize z-30 shadow-sm opacity-0 group-hover:opacity-100 transition-opacity"
                                title="Ajustar largura"
                            ></div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Banner Explicativo sobre a Aplicação Universal nos Documentos --}}
            <div class="rounded-2xl border border-blue-200 bg-blue-50/70 p-4 shadow-sm dark:border-blue-900/40 dark:bg-blue-950/20">
                <div class="flex items-start gap-3">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm">
                        <flux:icon.shield-check class="h-4 w-4" />
                    </span>
                    <div class="text-xs">
                        <p class="font-bold text-blue-900 dark:text-blue-200">
                            Aplicação Automática & Padronizada
                        </p>
                        <p class="text-blue-800/80 dark:text-blue-300/80 mt-0.5 leading-relaxed">
                            A posição selecionada para <span class="font-bold">{{ $nome }}</span> (<code class="bg-blue-100 dark:bg-blue-900/60 px-1 py-0.5 rounded font-mono">X: <span x-text="posX"></span>%, Y: <span x-text="posY"></span>%</code>) é sincronizada no motor de geração de documentos e visualizadores públicos. Qualquer documento novo ou histórico desta categoria apresentará o selo QR exatamente nestas coordenadas.
                        </p>
                    </div>
                </div>
            </div>

        </div>

    </div>

    {{-- MODAL DE PRÉ-VISUALIZAÇÃO COMPLETA DO DOCUMENTO --}}
    <div
        x-show="modalPreviewAberta"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-zinc-950/80 backdrop-blur-md flex items-center justify-center p-3 sm:p-6"
        @keydown.escape.window="fecharModalPreview()"
    >
        <div
            @click.away="fecharModalPreview()"
            class="relative w-full max-w-5xl bg-white dark:bg-zinc-900 rounded-3xl shadow-2xl border border-zinc-200 dark:border-zinc-800 flex flex-col max-h-[92vh] overflow-hidden animate-in fade-in zoom-in-95 duration-200"
        >
            {{-- Topo do Modal --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/60">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-xl bg-red-600 text-white flex items-center justify-center shadow-sm">
                        <flux:icon.eye class="h-5 w-5" />
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-zinc-900 dark:text-white flex items-center gap-2">
                            <span>Pré-visualização Oficial: {{ $nome }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 font-semibold font-mono">
                                {{ strtoupper($orientacao) }} • {{ $formato_papel }}
                            </span>
                        </h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                            Simulação exacta de como o documento final impresso ou em PDF apresentará o código QR.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="hidden sm:inline-flex items-center gap-1.5 text-xs text-zinc-500 font-mono bg-zinc-100 dark:bg-zinc-800 px-2.5 py-1 rounded-lg">
                        <span>X: <strong x-text="posX"></strong>%</span>
                        <span>•</span>
                        <span>Y: <strong x-text="posY"></strong>%</span>
                        <span>•</span>
                        <span>Largura: <strong x-text="largura"></strong>%</span>
                    </span>
                    <button
                        type="button"
                        @click="fecharModalPreview()"
                        class="p-2 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-xl transition"
                        title="Fechar visualização"
                    >
                        <flux:icon.x-mark class="h-5 w-5" />
                    </button>
                </div>
            </div>

            {{-- Corpo do Modal: Folha de Documento em Alta Resolução --}}
            <div class="flex-1 overflow-y-auto p-4 sm:p-8 bg-zinc-100 dark:bg-zinc-950 flex items-center justify-center">
                <div
                    class="relative bg-white text-zinc-900 shadow-2xl border border-zinc-300 rounded-sm overflow-hidden transition-all"
                    :style="orientacao === 'retrato' 
                        ? 'width: 580px; min-height: 820px; aspect-ratio: 1 / 1.414;' 
                        : 'width: 820px; min-height: 580px; aspect-ratio: 1.414 / 1;'"
                >
                    {{-- Marca d'água sutil oficial --}}
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.03] select-none">
                        <span class="text-8xl font-black uppercase rotate-[-30deg]">INOTU ANGOLA</span>
                    </div>

                    {{-- Conteúdo do Documento no Modal --}}
                    <div class="p-10 flex flex-col justify-between h-full space-y-6">
                        
                        {{-- Cabeçalho da República --}}
                        <div class="border-b-2 border-zinc-900 pb-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-red-600 flex items-center justify-center text-white text-xs font-black shadow-sm">
                                        AO
                                    </div>
                                    <div>
                                        <h3 class="text-xs font-black uppercase tracking-wider text-zinc-900">REPÚBLICA DE ANGOLA</h3>
                                        <p class="text-[9px] font-bold text-zinc-600 uppercase">INSTITUTO NACIONAL DE ORDENAMENTO DO TERRITÓRIO E DESENVOLVIMENTO URBANO</p>
                                        <p class="text-[8px] text-zinc-500">Direcção Nacional de Cartografia e Cadastro Predial</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block px-2 py-0.5 bg-zinc-100 border border-zinc-300 text-[9px] font-mono font-bold rounded">
                                        DOCUMENTO AUTÊNTICO
                                    </span>
                                    <p class="text-[8px] text-zinc-400 mt-1 font-mono">REGISTO: GTERRAS-2026-N{{ rand(1000, 9999) }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Título Central do Documento --}}
                        <div class="text-center py-2 border-b border-zinc-200">
                            <h2 class="text-sm font-extrabold uppercase tracking-wide text-zinc-900">
                                {{ $nome }}
                            </h2>
                            <p class="text-[9px] text-zinc-500 mt-0.5">Certificado de Conformidade Técnica Territorial</p>
                        </div>

                        {{-- Corpo Técnico do Documento --}}
                        <div class="flex-1 space-y-4">
                            @if($tipoSelecionado === 'croqui')
                                <div class="h-64 border-2 border-zinc-400 bg-zinc-50 rounded-xl p-4 flex flex-col justify-between relative overflow-hidden">
                                    <div class="flex justify-between items-center text-[9px] font-bold text-zinc-500 uppercase">
                                        <span>Levantamento Topográfico Georreferenciado</span>
                                        <span class="font-mono">Datum: WGS84 / UTM Zona 33S</span>
                                    </div>
                                    <div class="flex items-center justify-center py-6">
                                        <div class="h-32 w-48 border-2 border-dashed border-red-600 bg-red-500/10 rounded-lg flex items-center justify-center rotate-3">
                                            <div class="text-center">
                                                <p class="text-xs font-black text-red-700">PARCELA Nº 45-B</p>
                                                <p class="text-[9px] font-bold text-zinc-600">Área: 1.250,00 m²</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-4 gap-2 text-[8px] font-mono text-zinc-500 border-t border-zinc-200 pt-2">
                                        <div>P1: 345100, 8345900</div>
                                        <div>P2: 345150, 8345900</div>
                                        <div>P3: 345150, 8345850</div>
                                        <div>P4: 345100, 8345850</div>
                                    </div>
                                </div>
                            @else
                                <div class="space-y-2.5 text-justify">
                                    <p class="text-[10px] leading-relaxed text-zinc-700">
                                        Certifica-se para os devidos efeitos legais que o processo instruído para o efeito de <strong class="text-zinc-900">{{ $nome }}</strong> cumpre todas as disposições legais e regulamentares estabelecidas pela legislação territorial em vigor na República de Angola.
                                    </p>
                                    <p class="text-[10px] leading-relaxed text-zinc-700">
                                        O presente documento constitui instrumento formal de validação institucional, com efeito imediato perante as administrações municipais, governos provinciais e conservatórias competentes.
                                    </p>
                                    <div class="p-3 bg-zinc-50 border border-zinc-200 rounded-lg space-y-1 mt-4">
                                        <div class="flex justify-between text-[9px]">
                                            <span class="text-zinc-500">Titular / Requerente:</span>
                                            <span class="font-bold text-zinc-800">Cidadão / Entidade de Direito Angolano</span>
                                        </div>
                                        <div class="flex justify-between text-[9px]">
                                            <span class="text-zinc-500">Província:</span>
                                            <span class="font-bold text-zinc-800">Luanda / Todas as Províncias</span>
                                        </div>
                                        <div class="flex justify-between text-[9px]">
                                            <span class="text-zinc-500">Data de Emissão:</span>
                                            <span class="font-bold text-zinc-800">{{ now()->format('d/m/Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Assinaturas Simuladas --}}
                            <div class="grid grid-cols-2 gap-8 pt-8">
                                <div class="text-center border-t border-zinc-400 pt-1.5">
                                    <p class="text-[9px] font-bold text-zinc-800">O Técnico Especialista</p>
                                    <p class="text-[7.5px] text-zinc-400 uppercase">Assinatura e Carimbo</p>
                                </div>
                                <div class="text-center border-t border-zinc-400 pt-1.5">
                                    <p class="text-[9px] font-bold text-zinc-800">O Director Geral / Homologador</p>
                                    <p class="text-[7.5px] text-zinc-400 uppercase">INOTU • República de Angola</p>
                                </div>
                            </div>
                        </div>

                        {{-- Rodapé do Documento --}}
                        <div class="border-t border-zinc-200 pt-3 flex items-center justify-between text-[8px] text-zinc-400">
                            <span>Sistema Nacional de Gestão Territorial • G.TERRAS</span>
                            <span>Validação em tempo real via código QR</span>
                        </div>
                    </div>

                    {{-- QR CODE ESTAMPADO CONFORME AS COORDENADAS CONFIGURADAS (Quadrado Perfeito) --}}
                    <div
                        class="absolute pointer-events-none transition-all duration-200"
                        :style="{
                            left: posX + '%',
                            top: posY + '%',
                            width: largura + '%',
                        }"
                    >
                        <div
                            class="rounded-lg p-1.5 w-full aspect-square flex flex-col items-center justify-between overflow-hidden"
                            :style="{
                                backgroundColor: corFundo,
                                border: incluirMoldura ? '1px solid ' + corMoldura : 'none',
                                boxShadow: '0 3px 10px rgba(0,0,0,0.08)'
                            }"
                        >
                            {{-- Canvas com QR Real preenchendo exatamente o quadrado --}}
                            <div class="w-full flex-1 min-h-0 flex items-center justify-center">
                                <canvas
                                    x-ref="qrModalCanvas"
                                    class="max-w-full max-h-full aspect-square block rounded-xs object-contain"
                                ></canvas>
                            </div>

                            {{-- Rótulo do QR --}}
                            <template x-if="incluirRotulo">
                                <div class="w-full shrink-0 text-center overflow-hidden leading-none pt-0.5">
                                    <p
                                        class="text-[5.5px] sm:text-[6px] font-black uppercase tracking-tight truncate leading-tight"
                                        :style="{ color: corQr }"
                                        x-text="rotulo"
                                    ></p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rodapé do Modal com Ações --}}
            <div class="px-6 py-3.5 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/60 flex items-center justify-between">
                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                    💡 Esta pré-visualização reflecte as dimensões reais de proporção da folha {{ $formato_papel }}.
                </span>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="fecharModalPreview()"
                        class="px-4 py-2 text-xs font-semibold rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 transition"
                    >
                        Fechar Visualização
                    </button>
                    <button
                        type="button"
                        wire:click="salvar"
                        class="px-4 py-2 text-xs font-bold rounded-xl bg-red-600 hover:bg-red-700 text-white transition shadow-sm"
                    >
                        Confirmar & Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="doc-studio" x-data="{ previewUrl: null, aGerar: false, guardado: false, erro: '' }"
    x-on:studio-preview.window="
        const bin = atob($event.detail.pdf); const arr = new Uint8Array(bin.length);
        for (let i = 0; i < bin.length; i++) arr[i] = bin.charCodeAt(i);
        previewUrl = URL.createObjectURL(new Blob([arr], { type: 'application/pdf' })); aGerar = false"
    x-on:studio-erro.window="erro = $event.detail.mensagem; aGerar = false; setTimeout(() => erro = '', 4000)"
    x-on:studio-guardado.window="guardado = true; setTimeout(() => guardado = false, 2500)">

    @php
        $areaNome = 'Documentos';
        $configStudio = [
            'blocos' => $blocos,
            'orientacao' => $orientacao,
            'institucional' => $institucional,
            'logo' => $logo,
            'tipoNome' => $tipos[$tipoSelecionado]['nome'] ?? '',
            'modelos' => $modelos,
            'area' => $areaNome,
            'areaCor' => '#be123c',
        ];
    @endphp

    <div wire:key="studio-{{ $tipoSelecionado }}"
        x-data="documentoStudio(@js($configStudio))"
        x-on:studio-guardar.window="$wire.guardar(JSON.parse(JSON.stringify(blocos)), orientacao, true)"
        x-on:studio-reposto.window="blocos = JSON.parse(JSON.stringify($event.detail.blocos)); orientacao = $event.detail.orientacao; historico = []; futuro = []; sujo = false; sel = null"
        x-on:studio-institucional.window="inst = $event.detail.institucional"
        x-on:click.window="fecharMenu()"
        x-on:keydown.escape.window="fecharMenu()"
        class="flex flex-col gap-4 px-3 py-4 sm:px-6 lg:px-8">

        {{-- Cabeçalho --}}
        <div class="flex flex-wrap items-center gap-3">
            <div class="mr-auto min-w-0">
                <div class="flex items-center gap-2">
                    <span class="inline-flex size-9 items-center justify-center rounded-xl bg-gradient-to-br from-rose-600 to-orange-500 text-white shadow-sm">
                        <flux:icon.swatch class="size-5" />
                    </span>
                    <div>
                        <h1 class="text-lg font-bold leading-tight text-zinc-900 dark:text-white">Document Studio</h1>
                        <p class="text-xs text-zinc-500">Desenhe o layout dos documentos: logótipo, cabeçalho, rodapé, QR e mais.</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-1 rounded-xl border border-zinc-200 bg-white p-1 dark:border-zinc-700 dark:bg-zinc-900">
                <button type="button" class="btn-table btn-table-neutral" @click="desfazer()" :disabled="!historico.length" title="Desfazer (Ctrl+Z)"><flux:icon.arrow-uturn-left class="size-4" /></button>
                <button type="button" class="btn-table btn-table-neutral" @click="refazer()" :disabled="!futuro.length" title="Refazer (Ctrl+Y)"><flux:icon.arrow-uturn-right class="size-4" /></button>
                <span class="mx-1 h-5 w-px bg-zinc-200 dark:bg-zinc-700"></span>
                <button type="button" class="btn-table btn-table-neutral" @click="zoom = Math.max(50, zoom - 10)" title="Reduzir"><flux:icon.minus class="size-4" /></button>
                <span class="w-10 text-center text-xs tabular-nums text-zinc-600 dark:text-zinc-300" x-text="zoom + '%'"></span>
                <button type="button" class="btn-table btn-table-neutral" @click="zoom = Math.min(160, zoom + 10)" title="Ampliar"><flux:icon.plus class="size-4" /></button>
                <span class="mx-1 h-5 w-px bg-zinc-200 dark:bg-zinc-700"></span>
                <button type="button" class="btn-table btn-table-neutral" :class="grelha && 'bg-zinc-100 dark:bg-zinc-800'" @click="grelha = !grelha" title="Grelha"><flux:icon.squares-2x2 class="size-4" /></button>
                <button type="button" class="btn-table btn-table-neutral" :class="encaixe && 'bg-zinc-100 dark:bg-zinc-800'" @click="encaixe = !encaixe" title="Encaixe magnético"><flux:icon.cursor-arrow-rays class="size-4" /></button>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" class="btn-ghost" @click="$dispatch('studio-abrir-inst')">
                    <flux:icon.building-office-2 class="size-4" /> Dados institucionais
                </button>
                <button type="button" class="btn-ghost" :disabled="aGerar"
                    @click="aGerar = true; $wire.previsualizar(JSON.parse(JSON.stringify(blocos)), orientacao)">
                    <flux:icon.eye class="size-4" /> <span x-text="aGerar ? 'A gerar…' : 'Pré-visualizar PDF'"></span>
                </button>
                <button type="button" class="btn-primary" @click="$dispatch('studio-guardar')" wire:loading.attr="disabled" wire:target="guardar">
                    <flux:icon.check class="size-4" /> Guardar
                    <span x-show="sujo" class="size-1.5 rounded-full bg-amber-300"></span>
                </button>
            </div>
        </div>

        <div x-show="guardado" x-transition x-cloak class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300">Layout guardado. Será aplicado aos próximos documentos descarregados.</div>
        <div x-show="erro" x-cloak class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" x-text="erro"></div>

        {{-- Tipos de documento --}}
        <div class="flex gap-2 overflow-x-auto pb-1">
            @foreach ($tipos as $chave => $t)
                <button type="button" wire:click="selecionarTipo('{{ $chave }}')"
                    class="whitespace-nowrap rounded-full border px-3.5 py-1.5 text-sm font-medium transition
                    {{ $tipoSelecionado === $chave
                        ? 'border-rose-600 bg-rose-600 text-white shadow-sm'
                        : 'border-zinc-200 bg-white text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300' }}">
                    {{ $t['nome'] }}
                </button>
            @endforeach
        </div>

        <div class="grid gap-4 lg:grid-cols-[15rem_minmax(0,1fr)_19rem]">
            {{-- Paleta --}}
            <aside class="space-y-4">
                <div class="rounded-2xl border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
                    <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-zinc-500">Elementos</h3>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach ([
                            ['texto', 'Texto', 'bars-3-bottom-left'],
                            ['logo', 'Logótipo', 'photo'],
                            ['qr', 'QR Code', 'qr-code'],
                            ['linha', 'Linha', 'minus'],
                            ['retangulo', 'Retângulo', 'stop'],
                            ['marca_agua', 'Marca de água', 'sparkles'],
                        ] as [$tipo, $rotulo, $icone])
                            <button type="button" @click="adicionar('{{ $tipo }}')"
                                class="group flex flex-col items-center gap-1 rounded-xl border border-zinc-200 bg-zinc-50 px-2 py-2.5 text-xs font-medium text-zinc-700 transition hover:-translate-y-0.5 hover:border-rose-300 hover:bg-rose-50 hover:text-rose-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                <flux:icon :name="$icone" class="size-5 text-zinc-500 group-hover:text-rose-600" />
                                {{ $rotulo }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
                    <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-zinc-500">Modelos rápidos</h3>
                    <div class="space-y-1.5">
                        <button type="button" class="btn-ghost w-full justify-start" @click="inserirModelo('cabecalho')"><flux:icon.bars-3-center-left class="size-4" /> Cabeçalho institucional</button>
                        <button type="button" class="btn-ghost w-full justify-start" @click="inserirModelo('rodape')"><flux:icon.bars-3-bottom-left class="size-4" /> Rodapé com paginação</button>
                    </div>
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
                    <h3 class="mb-2 flex items-center justify-between text-xs font-semibold uppercase tracking-wide text-zinc-500">
                        Camadas <span class="rounded-full bg-zinc-100 px-1.5 text-[10px] dark:bg-zinc-800" x-text="blocos.length"></span>
                    </h3>
                    <ul class="max-h-64 space-y-1 overflow-y-auto">
                        <template x-for="b in [...blocos].reverse()" :key="b.id">
                            <li @click="sel = b.id" @contextmenu="menuCanvas($event, b)"
                                class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 text-xs transition"
                                :class="sel === b.id ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/40' : 'text-zinc-600 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800'">
                                <span class="size-1.5 rounded-full" :class="b.oculto ? 'bg-zinc-300' : 'bg-rose-500'"></span>
                                <span class="flex-1 truncate" x-text="rotulo(b.tipo) + (b.props?.texto ? ' · ' + b.props.texto : '')"></span>
                                <span x-show="b.bloqueado" class="text-[10px]">🔒</span>
                            </li>
                        </template>
                        <li x-show="!blocos.length" class="px-2 py-3 text-center text-xs text-zinc-400">Página vazia. Clique com o botão direito para adicionar.</li>
                    </ul>
                </div>
            </aside>

            {{-- Tela --}}
            <section class="min-w-0 rounded-2xl border border-zinc-200 bg-zinc-100 p-4 dark:border-zinc-700 dark:bg-zinc-950" @click="sel = null">
                <div class="mb-3 flex items-center justify-between text-xs text-zinc-500">
                    <span class="font-medium" x-text="tipoNome + ' · A4 ' + orientacao"></span>
                    <div class="inline-flex rounded-lg border border-zinc-200 bg-white p-0.5 dark:border-zinc-700 dark:bg-zinc-900">
                        <button type="button" @click.stop="mudarOrientacao('retrato')" class="rounded-md px-2.5 py-1" :class="orientacao === 'retrato' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : ''">Retrato</button>
                        <button type="button" @click.stop="mudarOrientacao('paisagem')" class="rounded-md px-2.5 py-1" :class="orientacao === 'paisagem' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : ''">Paisagem</button>
                    </div>
                </div>

                <div class="overflow-auto pb-2">
                    <div x-ref="pagina" @contextmenu="menuCanvas($event)" @click.stop="sel = null"
                        class="relative mx-auto select-none bg-white shadow-xl ring-1 ring-zinc-300"
                        :style="`width:${(orientacao === 'retrato' ? 560 : 780) * zoom / 100}px;aspect-ratio:${dim[0]}/${dim[1]};container-type:inline-size;` + (grelha ? 'background-image:linear-gradient(to right,rgba(244,63,94,.07) 1px,transparent 1px),linear-gradient(to bottom,rgba(244,63,94,.07) 1px,transparent 1px);background-size:5% 5%;' : '')">

                        {{-- Conteúdo fictício --}}
                        <div class="pointer-events-none absolute inset-x-[8%] top-[20%] space-y-[2.2%] opacity-60">
                            @foreach (range(1, 14) as $i)
                                <div class="h-[0.9cqw] rounded-full bg-zinc-200" style="width: {{ 100 - (($i * 7) % 5) * 9 }}%"></div>
                            @endforeach
                        </div>

                        <div class="pointer-events-none absolute inset-y-0 left-1/2 w-px bg-rose-500" x-show="guiaX" x-cloak></div>
                        <div class="pointer-events-none absolute inset-x-0 top-1/2 h-px bg-rose-500" x-show="guiaY" x-cloak></div>

                        <template x-for="b in blocos" :key="b.id">
                            <div class="absolute" :style="estiloBloco(b)"
                                :class="[b.oculto ? 'opacity-25' : '', b.bloqueado ? 'cursor-not-allowed' : 'cursor-move', sel === b.id ? 'z-20 outline outline-2 outline-rose-500' : 'hover:outline hover:outline-1 hover:outline-rose-300']"
                                @pointerdown="selecionar(b, $event)" @click.stop @contextmenu="menuCanvas($event, b)">

                                <template x-if="b.tipo === 'texto'">
                                    <div class="size-full overflow-hidden whitespace-pre-wrap leading-[1.25]"
                                        :style="`font-size:${fonteCss(b)};color:${b.props.cor};text-align:${alinhCss(b)};font-weight:${b.props.negrito ? 700 : 400};font-style:${b.props.italico ? 'italic' : 'normal'};font-family:${b.props.fonte === 'Times' ? 'Times New Roman,serif' : b.props.fonte === 'Courier' ? 'Courier New,monospace' : 'Helvetica,Arial,sans-serif'}`"
                                        x-text="textoReal(b)"></div>
                                </template>

                                <template x-if="b.tipo === 'logo'">
                                    <img :src="logo" alt="Logótipo" class="pointer-events-none size-full object-contain" draggable="false">
                                </template>

                                <template x-if="b.tipo === 'qr'">
                                    <div class="size-full" :style="`background:${b.props.cor_fundo};${b.props.moldura ? 'border:1px solid ' + b.props.cor_moldura + ';' : ''}padding:${b.props.moldura ? '5%' : '0'};display:flex;flex-direction:column;`">
                                        <svg class="min-h-0 flex-1" :viewBox="`0 0 ${qrCaminho(b).n} ${qrCaminho(b).n}`" preserveAspectRatio="xMidYMid meet" shape-rendering="crispEdges">
                                            <path :d="qrCaminho(b).d" :fill="b.props.cor"></path>
                                        </svg>
                                        <div x-show="b.props.rotulo" class="truncate text-center font-bold" :style="`font-size:${(4.5 * 0.3528 / dim[0] * 100).toFixed(3)}cqw;color:${b.props.cor}`" x-text="b.props.rotulo"></div>
                                    </div>
                                </template>

                                <template x-if="b.tipo === 'linha'">
                                    <div class="pointer-events-none absolute"
                                        :style="b.h * dim[1] > b.w * dim[0]
                                            ? `top:0;bottom:0;left:0;width:${espessuraCss(b.props.espessura)};background:${b.props.cor}`
                                            : `left:0;right:0;top:0;height:${espessuraCss(b.props.espessura)};background:${b.props.cor}`"></div>
                                </template>

                                <template x-if="b.tipo === 'retangulo'">
                                    <div class="size-full" :style="`background:${b.props.cor_fundo || 'transparent'};border:${b.props.cor_borda ? espessuraCss(b.props.espessura) + ' solid ' + b.props.cor_borda : 'none'}`"></div>
                                </template>

                                <template x-if="b.tipo === 'marca_agua'">
                                    <div class="flex size-full items-center justify-center whitespace-nowrap font-bold"
                                        :style="`font-size:${fonteCss(b)};color:${b.props.cor}`" x-text="textoReal(b)"></div>
                                </template>

                                {{-- Alças de redimensionamento --}}
                                <template x-if="sel === b.id && !b.bloqueado">
                                    <div>
                                        <template x-for="a in ['nw','n','ne','e','se','s','sw','w']" :key="a">
                                            <span class="absolute z-30 size-2.5 rounded-sm border border-rose-500 bg-white"
                                                :style="`${a.includes('n') ? 'top:-5px;' : a.includes('s') ? 'bottom:-5px;' : 'top:calc(50% - 5px);'}${a.includes('w') ? 'left:-5px;' : a.includes('e') ? 'right:-5px;' : 'left:calc(50% - 5px);'}cursor:${a}-resize`"
                                                @pointerdown="iniciar('redimensionar', b, $event, a)"></span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
                <p class="mt-2 text-center text-[11px] text-zinc-400">Arraste para mover · alças para redimensionar · setas para ajustar · botão direito para mais ações</p>
            </section>

            {{-- Propriedades --}}
            <aside class="space-y-4">
                <div class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <template x-if="!selecionado">
                        <div class="space-y-3 text-sm">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Documento</h3>
                            <p class="text-zinc-500">Selecione um elemento na página para editar as suas propriedades.</p>
                            <ul class="space-y-1 text-xs text-zinc-500">
                                <li><b class="text-zinc-700 dark:text-zinc-300">Variáveis de texto:</b></li>
                                <li><code>@{{pagina}}</code> <code>@{{total}}</code> <code>@{{data}}</code> <code>@{{hora}}</code></li>
                                <li><code>@{{tipo}}</code> <code>@{{orgao}}</code> <code>@{{subtitulo}}</code></li>
                                <li><code>@{{morada}}</code> <code>@{{contactos}}</code></li>
                            </ul>
                            <div class="border-t border-zinc-100 pt-3 dark:border-zinc-800">
                                @if ($personalizado)
                                    <button type="button" class="btn-ghost w-full" wire:click="repor" wire:confirm="Repor o layout predefinido deste tipo? As personalizações serão perdidas.">
                                        <flux:icon.arrow-path class="size-4" /> Repor layout predefinido
                                    </button>
                                @else
                                    <p class="text-xs text-zinc-400">A usar o layout predefinido (apenas QR). Guarde para personalizar.</p>
                                @endif
                            </div>
                        </div>
                    </template>

                    <template x-if="selecionado">
                        <div class="space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-500" x-text="rotulo(selecionado.tipo)"></h3>
                                <div class="flex gap-1">
                                    <button type="button" class="btn-table btn-table-neutral" @click="duplicar()" title="Duplicar"><flux:icon.square-2-stack class="size-4" /></button>
                                    <button type="button" class="btn-table btn-table-neutral" @click="bloquear()" title="Bloquear"><flux:icon.lock-closed class="size-4" /></button>
                                    <button type="button" class="btn-table btn-table-neutral text-red-600" @click="remover()" title="Eliminar"><flux:icon.trash class="size-4" /></button>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                @foreach ([['x', 'X (%)'], ['y', 'Y (%)'], ['w', 'Largura (%)'], ['h', 'Altura (%)']] as [$campo, $rot])
                                    <label class="space-y-1 text-xs text-zinc-500">{{ $rot }}
                                        @if ($campo === 'w')
                                            <input type="number" step="0.5" min="1" max="100" :value="selecionado.w" @input="snapshot(); definirLargura(selecionado, $event.target.value)" class="ui-filter-control w-full" />
                                        @else
                                            <input type="number" step="0.5" min="0" max="100" x-model.number="selecionado.{{ $campo }}" @change="sujo = true"
                                                :disabled="'{{ $campo }}' === 'h' && selecionado.tipo === 'qr'" class="ui-filter-control w-full disabled:opacity-50" />
                                        @endif
                                    </label>
                                @endforeach
                            </div>

                            <label class="block space-y-1 text-xs text-zinc-500">Mostrar em
                                <select x-model="selecionado.pagina" @change="sujo = true" class="ui-filter-control w-full">
                                    <option value="todas">Todas as páginas</option>
                                    <option value="primeira">Primeira página</option>
                                    <option value="ultima">Última página</option>
                                    <option value="exceto_primeira">Todas exceto a primeira</option>
                                    <option value="impares">Páginas ímpares</option>
                                    <option value="pares">Páginas pares</option>
                                </select>
                            </label>

                            {{-- Texto --}}
                            <template x-if="selecionado.tipo === 'texto' || selecionado.tipo === 'marca_agua'">
                                <div class="space-y-3 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                                    <label class="block space-y-1 text-xs text-zinc-500">Conteúdo
                                        <textarea rows="3" x-model="selecionado.props.texto" @input="sujo = true" class="ui-filter-control w-full"></textarea>
                                    </label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <label class="space-y-1 text-xs text-zinc-500">Tamanho (pt)
                                            <input type="number" min="4" max="200" x-model.number="selecionado.props.tamanho" @input="sujo = true" class="ui-filter-control w-full" />
                                        </label>
                                        <label class="space-y-1 text-xs text-zinc-500">Cor
                                            <input type="color" x-model="selecionado.props.cor" @input="sujo = true" class="h-9 w-full cursor-pointer rounded-lg border border-zinc-200 p-0.5" />
                                        </label>
                                    </div>
                                    <template x-if="selecionado.tipo === 'texto'">
                                        <div class="space-y-3">
                                            <div class="grid grid-cols-2 gap-2">
                                                <label class="space-y-1 text-xs text-zinc-500">Tipo de letra
                                                    <select x-model="selecionado.props.fonte" @change="sujo = true" class="ui-filter-control w-full">
                                                        <option value="Helvetica">Sans-serif</option>
                                                        <option value="Times">Serifada</option>
                                                        <option value="Courier">Monoespaçada</option>
                                                    </select>
                                                </label>
                                                <label class="space-y-1 text-xs text-zinc-500">Alinhamento
                                                    <select x-model="selecionado.props.alinhamento" @change="sujo = true" class="ui-filter-control w-full">
                                                        <option value="L">Esquerda</option><option value="C">Centro</option>
                                                        <option value="R">Direita</option><option value="J">Justificado</option>
                                                    </select>
                                                </label>
                                            </div>
                                            <div class="flex gap-4 text-xs text-zinc-600">
                                                <label class="flex items-center gap-1.5"><input type="checkbox" x-model="selecionado.props.negrito" @change="sujo = true"> Negrito</label>
                                                <label class="flex items-center gap-1.5"><input type="checkbox" x-model="selecionado.props.italico" @change="sujo = true"> Itálico</label>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="selecionado.tipo === 'marca_agua'">
                                        <label class="block space-y-1 text-xs text-zinc-500">Ângulo (°)
                                            <input type="range" min="-90" max="90" x-model.number="selecionado.props.angulo" @input="sujo = true" class="w-full" />
                                        </label>
                                    </template>
                                </div>
                            </template>

                            {{-- Linha / retângulo --}}
                            <template x-if="selecionado.tipo === 'linha' || selecionado.tipo === 'retangulo'">
                                <div class="grid grid-cols-2 gap-2 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                                    <label class="space-y-1 text-xs text-zinc-500">Espessura (mm)
                                        <input type="number" step="0.1" min="0" max="5" x-model.number="selecionado.props.espessura" @input="sujo = true" class="ui-filter-control w-full" />
                                    </label>
                                    <template x-if="selecionado.tipo === 'linha'">
                                        <label class="space-y-1 text-xs text-zinc-500">Cor
                                            <input type="color" x-model="selecionado.props.cor" @input="sujo = true" class="h-9 w-full cursor-pointer rounded-lg border border-zinc-200 p-0.5" />
                                        </label>
                                    </template>
                                    <template x-if="selecionado.tipo === 'retangulo'">
                                        <div class="contents">
                                            <label class="space-y-1 text-xs text-zinc-500">Preenchimento
                                                <input type="color" x-model="selecionado.props.cor_fundo" @input="sujo = true" class="h-9 w-full cursor-pointer rounded-lg border border-zinc-200 p-0.5" />
                                            </label>
                                            <label class="space-y-1 text-xs text-zinc-500">Contorno
                                                <input type="color" x-model="selecionado.props.cor_borda" @input="sujo = true" class="h-9 w-full cursor-pointer rounded-lg border border-zinc-200 p-0.5" />
                                            </label>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- QR --}}
                            <template x-if="selecionado.tipo === 'qr'">
                                <div class="space-y-3 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                                    <div class="grid grid-cols-2 gap-2">
                                        <label class="space-y-1 text-xs text-zinc-500">Cor do QR
                                            <input type="color" x-model="selecionado.props.cor" @input="sujo = true" class="h-9 w-full cursor-pointer rounded-lg border border-zinc-200 p-0.5" />
                                        </label>
                                        <label class="space-y-1 text-xs text-zinc-500">Fundo
                                            <input type="color" x-model="selecionado.props.cor_fundo" @input="sujo = true" class="h-9 w-full cursor-pointer rounded-lg border border-zinc-200 p-0.5" />
                                        </label>
                                    </div>
                                    <label class="flex items-center gap-2 text-xs text-zinc-600"><input type="checkbox" x-model="selecionado.props.moldura" @change="sujo = true"> Moldura</label>
                                    <label x-show="selecionado.props.moldura" class="block space-y-1 text-xs text-zinc-500">Cor da moldura
                                        <input type="color" x-model="selecionado.props.cor_moldura" @input="sujo = true" class="h-9 w-full cursor-pointer rounded-lg border border-zinc-200 p-0.5" />
                                    </label>
                                    <label class="block space-y-1 text-xs text-zinc-500">Rótulo
                                        <input type="text" maxlength="60" x-model="selecionado.props.rotulo" @input="sujo = true" class="ui-filter-control w-full" />
                                    </label>
                                    <label class="block space-y-1 text-xs text-zinc-500">Correção de erros
                                        <select x-model="selecionado.props.nivel" @change="sujo = true" class="ui-filter-control w-full">
                                            <option value="L">Baixa (L)</option><option value="M">Média (M)</option>
                                            <option value="Q">Alta (Q)</option><option value="H">Máxima (H)</option>
                                        </select>
                                    </label>
                                </div>
                            </template>

                            <template x-if="selecionado.tipo === 'logo'">
                                <p class="border-t border-zinc-100 pt-3 text-xs text-zinc-500 dark:border-zinc-800">Usa o logótipo institucional global (INOTU) em todos os documentos.</p>
                            </template>

                            <div class="grid grid-cols-3 gap-1 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                                <button type="button" class="btn-table btn-table-neutral justify-center" @click="alinhar('centro-h')" title="Centrar na horizontal"><flux:icon.arrows-right-left class="size-4" /></button>
                                <button type="button" class="btn-table btn-table-neutral justify-center" @click="alinhar('centro-v')" title="Centrar na vertical"><flux:icon.arrows-up-down class="size-4" /></button>
                                <button type="button" class="btn-table btn-table-neutral justify-center" @click="ordem(selecionado.id, 'frente')" title="Trazer para a frente"><flux:icon.chevron-double-up class="size-4" /></button>
                            </div>
                        </div>
                    </template>
                </div>
            </aside>
        </div>

        {{-- Menu de contexto do editor --}}
        <div x-show="ctx.aberto" x-cloak x-transition.opacity.duration.100ms @click.stop class="ctxm" role="menu"
            :style="`left:${ctx.x}px;top:${ctx.y}px;--ctxm-cor:${areaCor}`">
            <div class="ctxm-head"><span class="ctxm-dot"></span><span class="ctxm-area" x-text="area"></span>
                <span class="ctxm-title" x-text="selecionado ? rotulo(selecionado.tipo) : 'Página'"></span></div>
            <template x-for="(it, i) in ctx.itens" :key="i">
                <div>
                    <div x-show="it.sep" class="ctxm-sep"></div>
                    <button x-show="!it.sep" type="button" role="menuitem" class="ctxm-item" :class="it.perigo && 'is-danger'" :disabled="!!it.desativado" :style="it.desativado && 'opacity:.4'" @click="executar(it)">
                        <span class="ctxm-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path :d="it.icone" /></svg></span>
                        <span class="ctxm-label" x-text="it.rotulo"></span>
                        <span class="ctxm-kbd" x-text="it.atalho"></span>
                    </button>
                </div>
            </template>
        </div>
    </div>

    {{-- Dados institucionais --}}
    <div x-data="{ aberto: false }" x-on:studio-abrir-inst.window="aberto = true" x-on:studio-institucional.window="aberto = false"
        x-show="aberto" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @keydown.escape.window="aberto = false">
        <form wire:submit="guardarInstitucional" @click.outside="aberto = false" class="w-full max-w-lg space-y-4 rounded-2xl bg-white p-6 shadow-2xl dark:bg-zinc-900">
            <div>
                <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Dados institucionais</h2>
                <p class="text-sm text-zinc-500">Usados nas variáveis <code>@{{orgao}}</code>, <code>@{{morada}}</code> e <code>@{{contactos}}</code> de todos os documentos.</p>
            </div>
            <div class="flex items-center gap-3 rounded-xl bg-zinc-50 p-3 dark:bg-zinc-800">
                <img src="{{ $logo }}" alt="Logótipo INOTU" class="h-10 w-auto object-contain">
                <span class="text-xs text-zinc-500">Logótipo global do INOTU, partilhado por todos os documentos.</span>
            </div>
            <label class="block space-y-1 text-sm">Órgão
                <input type="text" wire:model="institucional.orgao" class="ui-filter-control w-full" />
                @error('institucional.orgao') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </label>
            <label class="block space-y-1 text-sm">Subtítulo
                <input type="text" wire:model="institucional.subtitulo" class="ui-filter-control w-full" />
            </label>
            <label class="block space-y-1 text-sm">Morada
                <input type="text" wire:model="institucional.morada" class="ui-filter-control w-full" />
            </label>
            <label class="block space-y-1 text-sm">Contactos
                <input type="text" wire:model="institucional.contactos" class="ui-filter-control w-full" />
            </label>
            <div class="flex justify-end gap-2">
                <button type="button" class="btn-ghost" @click="aberto = false">Cancelar</button>
                <button type="submit" class="btn-primary">Guardar</button>
            </div>
        </form>
    </div>

    {{-- Pré-visualização em PDF --}}
    <div x-show="previewUrl" x-cloak class="fixed inset-0 z-50 flex flex-col bg-black/70 p-4 backdrop-blur-sm" @keydown.escape.window="previewUrl = null">
        <div class="mx-auto mb-2 flex w-full max-w-4xl items-center justify-between text-white">
            <span class="text-sm font-semibold">Pré-visualização do PDF real (conteúdo de exemplo)</span>
            <button type="button" class="rounded-lg bg-white/10 px-3 py-1.5 text-sm hover:bg-white/20" @click="previewUrl = null">Fechar</button>
        </div>
        <iframe :src="previewUrl" class="mx-auto w-full max-w-4xl flex-1 rounded-xl bg-white" title="Pré-visualização"></iframe>
    </div>
</div>

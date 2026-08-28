<div class="w-full">
    <div class="mb-6">
        <div class="mb-2 flex flex-wrap items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
            <span>/</span>
            <a href="{{ route('municipio.operacoes', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Operações Urbanísticas</a>
            <span>/</span>
            <a href="{{ route('municipio.operacoes.pareceres', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Pareceres INOTU</a>
            <span>/</span>
            <span>{{ $registoId ? 'Editar' : 'Novo' }}</span>
        </div>

        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <flux:heading size="xl">{{ $registoId ? 'Editar' : 'Novo' }} Parecer INOTU</flux:heading>
                <flux:text class="mt-1">Registe o enquadramento territorial, classificação da área, uso do solo, decisão técnica e o documento PDF ou Word.</flux:text>
            </div>

            <a href="{{ route('municipio.operacoes.pareceres', $municipio) }}" wire:navigate>
                <flux:button variant="ghost" icon="arrow-left">Voltar</flux:button>
            </a>
        </div>
    </div>

    <div class="grid w-full gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <div class="space-y-6">
            <section class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-5">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">Identificação do parecer</h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Dados principais usados para localizar e classificar o parecer no sistema.</p>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="lg:col-span-2">
                        <flux:input wire:model="titulo" label="Título do Parecer" placeholder="Ex: Parecer n.º 001/2026" required />
                    </div>

                    <flux:input wire:model="projecto_nome" label="Processo/Recurso analisado" placeholder="Ex: Croqui de localização, concessão de terra, loteamento..." />

                    <flux:select wire:model="servico" label="Serviço relacionado">
                        <option value="">Selecionar serviço</option>
                        <option value="croqui">Croqui de Localização</option>
                        <option value="terreno_ocioso">Terreno Ocioso</option>
                        <option value="loteamento">Loteamento</option>
                        <option value="instrumento">Instrumento de Ordenamento</option>
                        <option value="projecto_sona">Projecto SONA</option>
                        <option value="outro_projecto">Outro projecto territorial</option>
                        <option value="operacao_urbanistica">Operação Urbanística</option>
                        <option value="outros_pareceres">Outros pareceres</option>
                    </flux:select>

                    <flux:input wire:model="entidade_requerente" label="Município/Instituição/Requerente" placeholder="Ex: Administração Municipal..." />
                    <flux:input wire:model="tipo_concessao" label="Tipo de concessão/solicitação" placeholder="Ex: Concessão, legalização, localização..." />

                    <flux:select wire:model="visibilidade" label="Visibilidade no portal">
                        <option value="privado">Privado - apenas consulta por BI/referência</option>
                        <option value="publico">Público - aparece na consulta pública</option>
                    </flux:select>
                </div>
            </section>

            <section class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-5">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">Enquadramento territorial</h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Informação técnica que será exibida ao consultar o QR code do documento associado.</p>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    <flux:select wire:model="classificacao_area" label="Classificação da área">
                        <option value="">Selecionar classificação</option>
                        <option value="Área residencial">Área residencial</option>
                        <option value="Área industrial">Área industrial</option>
                        <option value="Área agrícola">Área agrícola</option>
                        <option value="Área comercial">Área comercial</option>
                        <option value="Área reservada">Área reservada</option>
                        <option value="Área mista">Área mista</option>
                        <option value="Não classificada">Não classificada</option>
                    </flux:select>

                    <flux:select wire:model="area_reservada" label="Incide em área reservada?">
                        <option value="">Não informado</option>
                        <option value="1">Sim</option>
                        <option value="0">Não</option>
                    </flux:select>

                    <flux:input wire:model="uso_solo" label="Uso do solo" placeholder="Ex: Residencial, industrial, equipamento..." />
                    <flux:input wire:model="zona_ordenamento" label="Zona/plano de ordenamento" placeholder="Ex: Zona urbana consolidada..." />
                </div>
            </section>

            <section class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-5">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">Análise e decisão técnica</h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Fundamente a decisão e deixe a conclusão suficientemente clara para consulta institucional.</p>
                </div>

                <div class="grid gap-4">
                    <flux:textarea wire:model="fundamentacao" label="Fundamentação técnica" rows="5" placeholder="Descreva a análise territorial, condicionantes, instrumentos consultados e enquadramento..." />
                    <flux:textarea wire:model="conclusao" label="Conclusão do parecer" rows="5" placeholder="Ex: Favorável, favorável condicionado, desfavorável, sujeito a regularização..." />
                </div>
            </section>

            <section class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-5">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">Documento PDF ou Word</h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">O documento será usado para emissão, consulta e autenticação por QR code.</p>
                </div>

                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Arquivo PDF ou Word
                    <span class="ml-1 text-xs font-normal text-zinc-400">(máx. 50MB)</span>
                </label>
                @if($ficheiroAtualCaminho)
                    <div class="mb-4 flex flex-col gap-3 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-950 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Ficheiro atual</p>
                            <p class="mt-1 truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $ficheiroAtualNome ?? basename($ficheiroAtualCaminho) }}</p>
                        </div>
                        <div class="flex shrink-0 gap-2">
                            <a href="{{ route('storage.serve', $ficheiroAtualCaminho) }}" target="_blank">
                                <flux:button size="sm" variant="ghost" icon="eye">Abrir</flux:button>
                            </a>
                            @if(auth()->user()?->podeEnviarParaLixeira())
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="trash"
                                    class="text-red-600 dark:text-red-400"
                                    x-on:click.prevent="$dispatch('confirmar', { title: 'Remover ficheiro', message: 'Deseja remover o PDF associado a este parecer? Esta ação não pode ser desfeita.', action: 'removerFicheiroAtual', component: $wire.$id, params: [], variant: 'danger', confirmLabel: 'Remover' })"
                                />
                            @endif
                        </div>
                    </div>
                @endif
                <input type="file" wire:model="ficheiro" accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                    class="block w-full rounded-lg border border-dashed border-zinc-300 bg-zinc-50 px-4 py-6 text-sm text-zinc-500 file:mr-4 file:rounded-md file:border-0 file:bg-zinc-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800 dark:border-zinc-700 dark:bg-zinc-950 dark:file:bg-zinc-100 dark:file:text-zinc-900" />

                <div wire:loading wire:target="ficheiro" class="mt-4 space-y-2">
                    <div class="flex items-center gap-2 text-xs text-blue-600 dark:text-blue-400">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        A carregar documento...
                    </div>
                    <div class="h-2 w-full rounded-full bg-zinc-200 dark:bg-zinc-700">
                        <div class="h-2 rounded-full bg-blue-600 transition-all duration-300" style="width: {{ $this->uploadProgress }}%"></div>
                    </div>
                    <div class="text-right text-xs text-zinc-500 dark:text-zinc-400">{{ $this->uploadProgress }}%</div>
                </div>

                <div wire:loading.remove wire:target="ficheiro">
                    @if($ficheiro)
                        <div class="mt-4 flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 p-3 text-green-700 dark:border-green-800/60 dark:bg-green-950/30 dark:text-green-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-sm font-medium">Documento carregado com sucesso</span>
                        </div>
                    @endif
                </div>

                @error('ficheiro') <span class="mt-2 block text-xs text-red-500">{{ $message }}</span> @enderror
            </section>

            @if($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-900/60 dark:bg-red-950/30">
                    @foreach($errors->all() as $error)
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
                    @endforeach
                </div>
            @endif
        </div>

        <aside class="space-y-4 xl:sticky xl:top-20 xl:self-start">
            @if($recursoTitulo)
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-800/60 dark:bg-emerald-950/30 dark:text-emerald-200">
                    <p class="text-xs font-semibold uppercase tracking-wide">Recurso associado</p>
                    <p class="mt-2 font-semibold">{{ $recursoTitulo }}</p>
                </div>
            @endif

            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">Resumo</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="text-zinc-500">Município</dt>
                        <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $municipio->nome }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Serviço</dt>
                        <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $servico ? str($servico)->replace('_', ' ')->title() : 'Não selecionado' }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Área reservada</dt>
                        <dd class="font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $area_reservada === '1' ? 'Sim' : ($area_reservada === '0' ? 'Não' : 'Não informado') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">PDF</dt>
                        <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $ficheiro ? 'Novo PDF carregado' : ($ficheiroAtualCaminho ? 'Ficheiro atual associado' : ($registoId ? 'Sem ficheiro associado' : 'Obrigatório')) }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800 dark:border-blue-900/60 dark:bg-blue-950/30 dark:text-blue-200">
                <p class="font-semibold">Consulta por QR</p>
                <p class="mt-1">As informações territoriais deste parecer ficam disponíveis na verificação do QR do próprio parecer e do recurso associado, quando existir.</p>
            </div>

            <div class="flex flex-col gap-3 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:button wire:click="guardar" variant="primary" wire:loading.attr="disabled" wire:target="guardar,ficheiro" :disabled="$isUploading || (!$ficheiro && !$registoId)">
                    <span wire:loading.remove wire:target="guardar">{{ $registoId ? 'Actualizar parecer' : 'Guardar parecer' }}</span>
                    <span wire:loading wire:target="guardar" class="flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        A guardar...
                    </span>
                </flux:button>

                <a href="{{ route('municipio.operacoes.pareceres', $municipio) }}" wire:navigate>
                    <flux:button variant="ghost" class="w-full">Cancelar</flux:button>
                </a>
            </div>
        </aside>
    </div>
</div>

<div>
    <div class="mb-6">
        <div class="mb-2 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
            <span>/</span>
            <a href="{{ route('municipio.requalificacao.outros', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Outros Projectos</a>
            <span>/</span>
            <span>{{ $registoId ? 'Editar' : 'Novo' }}</span>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <flux:heading size="xl">{{ $registoId ? 'Editar' : 'Novo' }} Projecto</flux:heading>
                <flux:text class="mt-1">Dados tecnicos e administrativos do projecto - {{ $municipio->nome }}</flux:text>
            </div>
            <a href="{{ route('municipio.requalificacao.outros', $municipio) }}" wire:navigate>
                <flux:button variant="ghost" icon="arrow-left">Voltar</flux:button>
            </a>
        </div>
    </div>

    <div class="max-w-4xl">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="space-y-5">
                <div class="rounded-lg border border-sky-200 bg-sky-50 p-3 text-sm text-sky-800 dark:border-sky-900/50 dark:bg-sky-950/30 dark:text-sky-200">
                    Entidade responsavel e fase inicial sao preenchidas automaticamente para novos projectos; pode alterar antes de guardar.
                </div>

                <div class="grid gap-4 lg:grid-cols-[2fr_1fr]">
                    <flux:input wire:model="titulo" label="Titulo do Projecto" placeholder="Ex: Projecto de Requalificacao - {{ $municipio->nome }}" required />
                    <flux:select wire:model.live="tipo" label="Tipo">
                        <flux:select.option value="requalificacao">Requalificacao</flux:select.option>
                        <flux:select.option value="infraestrutura">Infraestrutura</flux:select.option>
                        <flux:select.option value="social">Social</flux:select.option>
                        <flux:select.option value="outro">Outro</flux:select.option>
                    </flux:select>
                </div>

                <flux:textarea wire:model="descricao" label="Descricao" rows="4" placeholder="Resumo tecnico, ambito territorial e principais intervencoes previstas." />

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="entidade_responsavel" label="Entidade Responsavel" />
                    <flux:input wire:model="fase" label="Fase" placeholder="Ex: Fase 1, Fase 2" />
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <flux:select wire:model.live="estado" label="Estado">
                        <flux:select.option value="planificado">Planificado</flux:select.option>
                        <flux:select.option value="em_curso">Em curso</flux:select.option>
                        <flux:select.option value="concluido">Concluido</flux:select.option>
                        <flux:select.option value="suspenso">Suspenso</flux:select.option>
                    </flux:select>
                    <flux:input wire:model="data_inicio" label="Data de Inicio" type="date" />
                    <flux:input wire:model="data_previsao_fim" label="Previsao de Conclusao" type="date" />
                </div>

                <flux:textarea wire:model="observacoes" label="Observacoes" rows="2" />
            </div>

            @if($errors->any())
                <div class="mt-4 rounded-lg bg-red-50 p-3 dark:bg-red-900/20">
                    @foreach($errors->all() as $error)
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="mt-6 flex justify-end gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <a href="{{ route('municipio.requalificacao.outros', $municipio) }}" wire:navigate>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </a>
                <flux:button wire:click="guardar" variant="primary" wire:loading.attr="disabled" wire:target="guardar">
                    <span wire:loading.remove wire:target="guardar">{{ $registoId ? 'Actualizar' : 'Guardar' }}</span>
                    <span wire:loading wire:target="guardar" class="flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        A guardar...
                    </span>
                </flux:button>
            </div>
        </div>
    </div>
</div>

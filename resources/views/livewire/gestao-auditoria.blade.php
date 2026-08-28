<div>
        <div class="mb-6">
            <flux:heading size="xl">Auditoria & Logs</flux:heading>
            <flux:text class="mt-1">Monitorizar actividades do sistema e acções dos utilizadores</flux:text>
        </div>

        {{-- Tabs --}}
        <div class="mb-6 flex gap-2 border-b border-zinc-200 dark:border-zinc-700">
            <a href="{{ route('auditoria.index', ['tab' => 'logs']) }}"
                class="px-4 py-2 text-sm font-medium border-b-2 transition {{ $tab === 'logs' ? 'border-blue-500 text-blue-600' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-200' }}">
                Logs de Acesso
            </a>
            <a href="{{ route('auditoria.index', ['tab' => 'auditoria']) }}"
                class="px-4 py-2 text-sm font-medium border-b-2 transition {{ $tab === 'auditoria' ? 'border-blue-500 text-blue-600' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-200' }}">
                Auditoria de Dados
            </a>
        </div>

        {{-- Filtros --}}
        <div class="mb-4 flex flex-wrap gap-3">
            <div class="flex-1 min-w-[200px]">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Pesquisar..." icon="magnifying-glass" />
            </div>
            <flux:input wire:model.live="dataInicio" type="date" label="De" class="w-40" />
            <flux:input wire:model.live="dataFim" type="date" label="Até" class="w-40" />
            @if($tab === 'auditoria')
                <flux:select wire:model.live="filtroAccao" class="w-44">
                    <flux:select.option value="">Todas as acções</flux:select.option>
                    <flux:select.option value="criar">Criar</flux:select.option>
                    <flux:select.option value="editar">Editar</flux:select.option>
                    <flux:select.option value="eliminar">Eliminar</flux:select.option>
                </flux:select>
            @endif
        </div>

        @if($tab === 'logs')
        {{-- Logs de Acesso --}}
        <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Data/Hora</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Utilizador</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Acção</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Recurso</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">IP</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase">Detalhes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                    @forelse($logs as $log)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <td class="px-4 py-3 text-sm text-zinc-500">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="px-4 py-3 text-sm">{{ $log->user?->name ?? 'Sistema' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">
                                    {{ $log->accao }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm font-mono text-xs">{{ Str::limit($log->recurso, 50) }}</td>
                            <td class="px-4 py-3 text-sm text-zinc-500 font-mono text-xs">{{ $log->ip_address }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('auditoria.logs.show', $log) }}" wire:navigate>
                                    <flux:button size="sm" variant="ghost" icon="eye" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-zinc-500">Nenhum log encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $logs->links() }}</div>
        @endif

        @if($tab === 'auditoria')
        {{-- Auditoria --}}
        <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Data/Hora</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Utilizador</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Acção</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Modelo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">ID</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase">Detalhes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                    @forelse($auditorias as $audit)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <td class="px-4 py-3 text-sm text-zinc-500">{{ $audit->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="px-4 py-3 text-sm">{{ $audit->user?->name ?? 'Sistema' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    @switch($audit->accao)
                                        @case('criar') bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400 @break
                                        @case('editar') bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400 @break
                                        @case('eliminar') bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400 @break
                                        @default bg-zinc-100 text-zinc-700
                                    @endswitch">
                                    {{ ucfirst($audit->accao) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm font-mono text-xs">{{ $audit->modelo }}</td>
                            <td class="px-4 py-3 text-sm">{{ $audit->modelo_id }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('auditoria.show', $audit) }}" wire:navigate>
                                    <flux:button size="sm" variant="ghost" icon="eye" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-zinc-500">Nenhum registo de auditoria encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $auditorias->links() }}</div>
        @endif
</div>

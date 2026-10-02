<x-slot name="header">
    <x-ui.page-header
        eyebrow="Núcleo nacional"
        title="Dashboard Nacional"
        description="Visão consolidada da plataforma GTerra e dos seus nodes provinciais."
    />
</x-slot>

<div class="space-y-6">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Nodes registados" :value="$totalTenants" :detail="$activeTenants.' ativos · '.$onlineTenants.' online'" tone="red">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16m14 0h2m-2 0H5m4 0v-5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v5" /></svg></x-slot:icon>
        </x-ui.stat-card>

        <x-ui.stat-card label="Sincronizações" :value="$totalSyncs" :detail="$successfulSyncs.' concluídas · '.$failedSyncs.' com falha'" tone="green">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4v5h.58m15.36 2A8 8 0 0 0 4.58 9M4.58 9H9m11 11v-5h-.58A8 8 0 0 1 4.64 13M20 15h-4.42" /></svg></x-slot:icon>
        </x-ui.stat-card>

        <x-ui.stat-card label="Auditorias" :value="$totalAudits" detail="Registos de atividade do núcleo" tone="violet">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 12h6m-6 4h4" /></svg></x-slot:icon>
        </x-ui.stat-card>

        <x-ui.stat-card label="Taxa de sucesso" :value="($totalSyncs > 0 ? round(($successfulSyncs / $totalSyncs) * 100, 1) : 0).'%'" detail="Sincronizações concluídas com êxito" tone="yellow">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19V9m5 10V5m5 14v-7m5 7V3" /></svg></x-slot:icon>
        </x-ui.stat-card>
    </div>

    <x-ui.card title="Indicadores nacionais" description="Resumo agregado e atualizado a partir das províncias.">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            @forelse($indicators as $key => $indicator)
                <section class="rounded-lg border border-zinc-200 bg-zinc-50/60 p-4 dark:border-zinc-800 dark:bg-zinc-800/30">
                    <h3 class="text-sm font-bold capitalize text-zinc-900 dark:text-zinc-100">{{ str_replace('_', ' ', $key) }}</h3>
                    <dl class="mt-4 space-y-2.5">
                        @foreach($indicator as $label => $value)
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <dt class="capitalize text-zinc-500 dark:text-zinc-400">{{ str_replace('_', ' ', $label) }}</dt>
                                <dd class="font-bold text-zinc-900 dark:text-zinc-100">{{ $value ?? 0 }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
            @empty
                <div class="md:col-span-2 xl:col-span-4"><x-ui.empty-state title="Indicadores ainda indisponíveis" description="Os indicadores aparecerão assim que os nodes enviarem dados para o núcleo." /></div>
            @endforelse
        </div>
    </x-ui.card>

    <x-ui.card title="Estado dos nodes" description="Disponibilidade e última comunicação de cada instalação.">
        <div class="overflow-x-auto">
            <table class="table-gov min-w-[760px]">
                <thead>
                    <tr>
                        <th>Código</th><th>Nome</th><th>Tipo</th><th>Disponibilidade</th><th>Sincronização</th><th>Última sync</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenantStatus as $tenant)
                        @php $availability = !$tenant['is_active'] ? 'inativo' : ($tenant['is_online'] ? 'online' : 'offline'); @endphp
                        <tr>
                            <td class="font-bold text-zinc-900 dark:text-zinc-100">{{ $tenant['code'] }}</td>
                            <td>{{ $tenant['name'] }}</td>
                            <td><x-ui.status-badge :status="$tenant['type']" /></td>
                            <td><x-ui.status-badge :status="$availability" /></td>
                            <td><x-ui.status-badge :status="$tenant['sync_status']" /></td>
                            <td class="text-zinc-500 dark:text-zinc-400">{{ $tenant['last_sync'] ? $tenant['last_sync']->diffForHumans() : 'Nunca' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="!p-0"><x-ui.empty-state title="Nenhum node registado" description="Registe uma província para acompanhar o seu estado nesta área." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-ui.card title="Sincronizações recentes" description="Últimas tentativas de comunicação entre instalações.">
            <div class="space-y-3">
                @forelse($recentSyncs as $sync)
                    <article class="flex items-center justify-between gap-4 rounded-lg border border-zinc-100 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/30">
                        <div class="min-w-0"><p class="truncate text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $sync->sourceTenant->name }} → {{ $sync->targetTenant->name }}</p><p class="mt-1 text-xs text-zinc-500">{{ $sync->sync_type }}</p></div>
                        <div class="shrink-0 text-right"><x-ui.status-badge :status="$sync->status" /><p class="mt-1.5 text-xs text-zinc-500">{{ $sync->completed_at?->diffForHumans() ?? 'Em curso' }}</p></div>
                    </article>
                @empty
                    <x-ui.empty-state title="Sem sincronizações recentes" description="Quando um node comunicar com o núcleo, a atividade aparecerá aqui." />
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card title="Auditorias recentes" description="Ações mais recentes registadas no núcleo.">
            <div class="space-y-3">
                @forelse($recentAudits as $audit)
                    <article class="flex items-start justify-between gap-4 rounded-lg border border-zinc-100 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/30"><div class="min-w-0"><p class="text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $audit->action }}</p><p class="mt-1 line-clamp-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $audit->description }}</p></div><time class="shrink-0 text-xs text-zinc-500">{{ $audit->created_at->diffForHumans() }}</time></article>
                @empty
                    <x-ui.empty-state title="Sem auditorias recentes" description="As ações administrativas registadas serão apresentadas aqui." />
                @endforelse
            </div>
        </x-ui.card>
    </div>
</div>

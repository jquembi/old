@php
    $tipoLabel = $terreno->tipo === 'terreno_ocioso' ? 'Terreno Ocioso' : 'Croqui Elaborado';
@endphp

<div class="space-y-6">
    <div>
        <div class="mb-2 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio?->nome }}</a>
            <span>/</span>
            <a href="{{ route('municipio.operacoes.ids-terreno', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>IDs de Terreno</a>
            <span>/</span>
            <span>Detalhe</span>
        </div>

        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 p-5 dark:border-zinc-700">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <flux:heading size="xl">{{ $terreno->codigo_territorial ?? 'Sem ID territorial' }}</flux:heading>
                            <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $tipoLabel }}</span>
                        </div>
                        <flux:text class="mt-1">{{ $terreno->titulo }}</flux:text>
                    </div>
                    <a href="{{ route('municipio.operacoes.ids-terreno', $municipio) }}" wire:navigate>
                        <flux:button variant="ghost" icon="arrow-left">Voltar aos IDs</flux:button>
                    </a>
                </div>
            </div>

            <div class="grid gap-0 divide-y divide-zinc-200 dark:divide-zinc-700 md:grid-cols-4 md:divide-x md:divide-y-0">
                <div class="p-4">
                    <p class="text-xs font-medium uppercase text-zinc-400">Centro UTM</p>
                    <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-zinc-100">E {{ $terreno->centro_utm_easting ?? $terreno->utm_easting }}</p>
                    <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">N {{ $terreno->centro_utm_northing ?? $terreno->utm_northing }}</p>
                    <p class="mt-1 text-xs text-zinc-500">Zona {{ $terreno->utm_zone }}{{ $terreno->utm_hemisphere }}</p>
                </div>
                <div class="p-4">
                    <p class="text-xs font-medium uppercase text-zinc-400">Documentos</p>
                    <p class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $totalDocumentos }}</p>
                    <p class="text-xs text-zinc-500">Principais e associados</p>
                </div>
                <div class="p-4">
                    <p class="text-xs font-medium uppercase text-zinc-400">Pareceres INOTU</p>
                    <p class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $pareceres->count() }}</p>
                    <p class="text-xs text-zinc-500">Interligados ao ID</p>
                </div>
                <div class="p-4">
                    <p class="text-xs font-medium uppercase text-zinc-400">Estado</p>
                    <p class="mt-1 text-sm font-semibold {{ $terreno->download_liberado ? 'text-emerald-600' : 'text-amber-600' }}">
                        {{ $terreno->download_liberado ? 'Liberado' : 'Pendente' }}
                    </p>
                    <p class="text-xs text-zinc-500">{{ $terreno->rup_pago ? 'RUP pago' : 'RUP pendente' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <section class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Documentos Principais</h2>
                    <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $totalDocumentos }}</span>
                </div>
                <div class="mt-4 grid gap-3 md:grid-cols-2">
                    @forelse($documentos as $documento)
                        <a href="{{ $documento['rota'] }}" wire:navigate class="group rounded-lg border border-zinc-200 p-4 transition hover:border-blue-300 hover:bg-blue-50/40 dark:border-zinc-700 dark:hover:border-blue-800 dark:hover:bg-blue-950/20">
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                    <flux:icon :name="$documento['icone']" class="h-5 w-5" />
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $documento['titulo'] }}</p>
                                    <p class="mt-1 text-xs font-medium text-blue-700 dark:text-blue-400">{{ $documento['tipo'] }}</p>
                                    <p class="mt-1 line-clamp-2 text-xs text-zinc-500 dark:text-zinc-400">{{ $documento['descricao'] }}</p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="rounded-lg border border-dashed border-zinc-300 p-4 text-sm text-zinc-500 dark:border-zinc-700">
                            Nenhum documento associado a este ID.
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Dados do Terreno</h2>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <dt class="text-xs font-medium uppercase text-zinc-400">Area</dt>
                        <dd class="mt-1 text-sm text-zinc-800 dark:text-zinc-200">{{ $terreno->area ? $terreno->area . ' m2' : 'Nao informado' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-zinc-400">Perimetro</dt>
                        <dd class="mt-1 text-sm text-zinc-800 dark:text-zinc-200">{{ $terreno->perimetro ? $terreno->perimetro . ' m' : 'Nao informado' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-zinc-400">Dimensao</dt>
                        <dd class="mt-1 text-sm text-zinc-800 dark:text-zinc-200">{{ $terreno->dimensao ?: 'Nao informado' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-zinc-400">Finalidade</dt>
                        <dd class="mt-1 text-sm text-zinc-800 dark:text-zinc-200">{{ $terreno->finalidade ?: 'Nao informado' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-zinc-400">Escala</dt>
                        <dd class="mt-1 text-sm text-zinc-800 dark:text-zinc-200">{{ $terreno->escala ?: 'Nao informado' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-zinc-400">Tecnico</dt>
                        <dd class="mt-1 text-sm text-zinc-800 dark:text-zinc-200">{{ $terreno->tecnico_responsavel ?: 'Nao informado' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Coordenadas</h2>
                <div class="mt-4 overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500">Ponto</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500">Latitude</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500">Northing</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500">Longitude</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500">Easting</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse($coordenadas as $index => $coord)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $coord['lat_gms'] ?? '' }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $coord['lat_utm'] ?? ($coord['northing'] ?? '') }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $coord['lon_gms'] ?? '' }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $coord['lon_utm'] ?? ($coord['easting'] ?? '') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-4 text-sm text-zinc-500">Sem coordenadas registadas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Proprietarios</h2>
                <div class="mt-4 space-y-3">
                    @forelse($terreno->proprietarios as $proprietario)
                        <div class="rounded-lg border border-zinc-200 px-3 py-3 dark:border-zinc-700">
                            <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $proprietario->nome }}</p>
                            <p class="mt-1 text-xs text-zinc-500">BI/NIF: {{ $proprietario->bi ?: 'Nao informado' }}</p>
                            <p class="text-xs text-zinc-500">Contacto: {{ $proprietario->contacto ?: 'Nao informado' }}</p>
                        </div>
                    @empty
                        <p class="rounded-lg border border-dashed border-zinc-300 px-3 py-3 text-sm text-zinc-500 dark:border-zinc-700">Sem proprietarios registados.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Pareceres</h2>
                <div class="mt-4 space-y-3">
                    @forelse($pareceres as $parecer)
                        <a href="{{ route('parecer.detalhe', $parecer) }}" wire:navigate class="block rounded-lg border border-zinc-200 px-3 py-3 transition hover:border-rose-300 hover:bg-rose-50/40 dark:border-zinc-700 dark:hover:border-rose-800 dark:hover:bg-rose-950/20">
                            <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $parecer->titulo }}</p>
                            <p class="mt-1 text-xs text-zinc-500">{{ $parecer->servicoLabel() }} · {{ $parecer->estado ?? 'sem estado' }}</p>
                        </a>
                    @empty
                        <p class="rounded-lg border border-dashed border-zinc-300 px-3 py-3 text-sm text-zinc-500 dark:border-zinc-700">Sem parecer associado.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Registo no Sistema</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-medium uppercase text-zinc-400">Criado por</dt>
                        <dd class="text-zinc-800 dark:text-zinc-200">{{ $terreno->criadoPor?->name ?? 'Nao informado' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-zinc-400">Criado em</dt>
                        <dd class="text-zinc-800 dark:text-zinc-200">{{ $terreno->created_at?->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-zinc-400">Pagamento</dt>
                        <dd class="text-zinc-800 dark:text-zinc-200">{{ $terreno->pagamentoActivo?->rupe_referencia ?? $terreno->rup_referencia ?? 'Nao registado' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-zinc-400">Visibilidade</dt>
                        <dd class="text-zinc-800 dark:text-zinc-200">{{ ucfirst($terreno->visibilidade ?? 'privado') }}</dd>
                    </div>
                </dl>
            </section>
        </aside>
    </div>
</div>

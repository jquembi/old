<div>
    @section('page-title', 'Detalhes do Croqui')

    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('croquis.index') }}" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/50 rounded-lg hover:shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <h1 class="text-lg font-bold text-slate-800 dark:text-white">{{ $croqui->proprietario }}</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Registado em {{ $croqui->created_at?->format('d/m/Y H:i') }}
                        @if($croqui->creator)
                            por {{ $croqui->creator->name }}
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($this->podeEditar())
                    <a href="{{ route('croquis.edit', $croqui) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/50 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/60 hover:shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Editar
                    </a>
                    <button wire:click="deleteCroqui" wire:confirm="Tem certeza que deseja eliminar este croqui?" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-red-600 dark:text-red-400 bg-white dark:bg-slate-800 border border-red-200 dark:border-red-800/40 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Eliminar
                    </button>
                @endif
            </div>
        </div>

        {{-- Status & Badges --}}
        <div class="flex flex-wrap items-center gap-2">
            @php
                $statusColors = [
                    'pendente' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                    'aprovado' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                    'rejeitado' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                ];
                $condicaoColors = [
                    'legal' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                    'ilegal' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                    'em_conflito' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                    'em_regularizacao' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                ];
            @endphp
            <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $statusColors[$croqui->status] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $croqui->status === 'aprovado' ? 'bg-emerald-500' : ($croqui->status === 'rejeitado' ? 'bg-red-500' : 'bg-amber-500') }}"></span>
                {{ ucfirst($croqui->status ?? 'N/A') }}
            </span>
            <span class="inline-flex text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $condicaoColors[$croqui->condicao] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400' }}">
                {{ $croqui->getCondicaoLabel() }}
            </span>
            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700/50 px-2.5 py-1 rounded-full">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                {{ $croqui->tipo_geometria ?? 'Point' }}
            </span>
            @if($croqui->isVerified())
                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 px-2.5 py-1 rounded-full">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Verificado
                </span>
            @endif
        </div>

        {{-- Info Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            {{-- Col 1: Identificacao --}}
            <div class="space-y-5">
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/50">
                    <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-700/50">
                        <h3 class="text-sm font-semibold text-slate-800 dark:text-white flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Identificacao
                        </h3>
                    </div>
                    <dl class="divide-y divide-slate-100 dark:divide-slate-700/40">
                        @php
                            $fields = [
                                ['label' => 'Proprietario', 'value' => $croqui->proprietario],
                                ['label' => 'Municipio', 'value' => $croqui->municipio?->nome ?? '-'],
                                ['label' => 'No. Registro', 'value' => $croqui->numero_registro ?? '-'],
                                ['label' => 'Endereco', 'value' => $croqui->endereco ?? '-'],
                                ['label' => 'Lote', 'value' => $croqui->lote ?? '-'],
                            ];
                        @endphp
                        @foreach($fields as $f)
                            <div class="px-5 py-3 flex justify-between gap-4">
                                <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ $f['label'] }}</dt>
                                <dd class="text-xs text-slate-800 dark:text-slate-200 text-right font-medium">{{ $f['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                {{-- Dados Complementares --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/50">
                    <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-700/50">
                        <h3 class="text-sm font-semibold text-slate-800 dark:text-white flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Dados Complementares
                        </h3>
                    </div>
                    <dl class="divide-y divide-slate-100 dark:divide-slate-700/40">
                        @php
                            $complementar = [
                                ['label' => 'Escala', 'value' => $croqui->escala ?? '-'],
                                ['label' => 'Sistema de Referencia', 'value' => $croqui->sistema_referencia ?? '-'],
                                ['label' => 'Data Levantamento', 'value' => $croqui->data_levantamento?->format('d/m/Y') ?? '-'],
                            ];
                        @endphp
                        @foreach($complementar as $f)
                            <div class="px-5 py-3 flex justify-between gap-4">
                                <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ $f['label'] }}</dt>
                                <dd class="text-xs text-slate-800 dark:text-slate-200 text-right font-medium">{{ $f['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                    @if($croqui->descricao)
                        <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-700/40">
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Descricao</p>
                            <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">{{ $croqui->descricao }}</p>
                        </div>
                    @endif
                    @if($croqui->observacoes)
                        <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-700/40">
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Observacoes</p>
                            <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">{{ $croqui->observacoes }}</p>
                        </div>
                    @endif
                </div>

                {{-- Verificacao --}}
                @if($croqui->isVerified())
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/50 p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <h3 class="text-sm font-semibold text-slate-800 dark:text-white">Verificacao</h3>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300">
                        Verificado por <span class="font-semibold">{{ $croqui->verifier?->name ?? 'N/A' }}</span>
                    </p>
                </div>
                @endif

                {{-- PDF --}}
                @if($croqui->pdf_path)
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/50 p-5">
                    <a href="{{ Storage::url($croqui->pdf_path) }}" target="_blank" class="flex items-center gap-3 text-sm text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 font-medium transition">
                        <div class="w-10 h-10 bg-red-50 dark:bg-red-900/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold">Documento PDF</p>
                            <p class="text-[10px] text-slate-400">Clique para abrir</p>
                        </div>
                    </a>
                </div>
                @endif
            </div>

            {{-- Col 2-3: Mapa & Metricas --}}
            <div class="lg:col-span-2 space-y-5">
                {{-- Metricas --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @php
                        $metrics = [
                            ['label' => 'Area (m2)', 'value' => $croqui->area_calculada_m2 ? number_format((float)$croqui->area_calculada_m2, 2, ',', '.') : ($croqui->area_m2 ? number_format((float)$croqui->area_m2, 2, ',', '.') : '-'), 'color' => 'blue'],
                            ['label' => 'Perimetro (m)', 'value' => $croqui->perimetro_m ? number_format((float)$croqui->perimetro_m, 2, ',', '.') : '-', 'color' => 'emerald'],
                            ['label' => 'Vertices', 'value' => $croqui->num_vertices ?? ($croqui->coordenadas->count() ?: '-'), 'color' => 'amber'],
                            ['label' => 'Tipo', 'value' => $croqui->tipo_geometria ?? 'Point', 'color' => 'violet'],
                        ];
                        $metricColors = [
                            'blue' => 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400',
                            'emerald' => 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400',
                            'amber' => 'bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400',
                            'violet' => 'bg-violet-50 dark:bg-violet-900/20 text-violet-600 dark:text-violet-400',
                        ];
                    @endphp
                    @foreach($metrics as $m)
                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/50 p-4">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 mb-1">{{ $m['label'] }}</p>
                        <p class="text-base font-bold text-slate-800 dark:text-white tabular-nums">{{ $m['value'] }}</p>
                    </div>
                    @endforeach
                </div>

                {{-- Mapa Interativo --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/50 overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-700/50 flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.553-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        <h3 class="text-sm font-semibold text-slate-800 dark:text-white">Localizacao no Mapa</h3>
                    </div>
                    <div id="croqui-show-map" class="h-[400px] lg:h-[450px] w-full" wire:ignore></div>
                </div>

                {{-- Coordenadas --}}
                @if($croqui->coordenadas->count() > 0)
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/50">
                    <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-700/50 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-slate-800 dark:text-white flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            Coordenadas ({{ $croqui->coordenadas->count() }} vertices)
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-700/30">
                                    <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400">#</th>
                                    <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400">Latitude</th>
                                    <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400">Longitude</th>
                                    <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400">Descricao</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/40">
                                @foreach($croqui->coordenadas as $i => $coord)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/20 transition-colors">
                                    <td class="px-4 py-2 text-slate-400 font-mono">{{ $i + 1 }}</td>
                                    <td class="px-4 py-2 text-slate-700 dark:text-slate-300 font-mono">{{ number_format((float)$coord->latitude, 6) }}</td>
                                    <td class="px-4 py-2 text-slate-700 dark:text-slate-300 font-mono">{{ number_format((float)$coord->longitude, 6) }}</td>
                                    <td class="px-4 py-2 text-slate-500 dark:text-slate-400">{{ $coord->descricao ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mapEl = document.getElementById('croqui-show-map');
            if (!mapEl || typeof L === 'undefined') return;

            const geometry = @json($croqui->geometry);
            const tipo = @json($croqui->tipo_geometria ?? 'Point');
            const coordenadas = @json($croqui->coordenadas->map(fn($c) => ['lat' => (float)$c->latitude, 'lng' => (float)$c->longitude]));

            const map = L.map('croqui-show-map', { scrollWheelZoom: true }).setView([-14.9, 13.5], 10);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap',
                maxZoom: 19,
            }).addTo(map);

            if (tipo === 'Point' && geometry && geometry.coordinates) {
                const lat = geometry.coordinates[1];
                const lng = geometry.coordinates[0];
                L.marker([lat, lng]).addTo(map)
                    .bindPopup('<strong>{{ addslashes($croqui->proprietario) }}</strong>').openPopup();
                map.setView([lat, lng], 15);
            } else if (coordenadas && coordenadas.length > 0) {
                const latlngs = coordenadas.map(c => [c.lat, c.lng]);
                const polygon = L.polygon(latlngs, {
                    color: '#10b981',
                    weight: 2,
                    fillColor: '#10b981',
                    fillOpacity: 0.15,
                }).addTo(map);
                polygon.bindPopup('<strong>{{ addslashes($croqui->proprietario) }}</strong>');
                map.fitBounds(polygon.getBounds(), { padding: [30, 30] });

                latlngs.forEach((ll, i) => {
                    L.circleMarker(ll, { radius: 4, color: '#059669', fillColor: '#34d399', fillOpacity: 1, weight: 2 })
                        .addTo(map)
                        .bindTooltip('V' + (i + 1), { permanent: false, direction: 'top' });
                });
            }

            setTimeout(() => map.invalidateSize(), 200);
        });
    </script>
    @endpush
</div>

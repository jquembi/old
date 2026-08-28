@php
    $municipio = $croqui->operacao?->municipio;
    $provincia = $municipio?->provincia;
    $coordenadas = $croqui->coordenadas_texto ? json_decode($croqui->coordenadas_texto, true) : [];
    $qrUrl = $croqui->obterUrlVerificacao();
@endphp

<div class="min-h-screen bg-zinc-100 dark:bg-zinc-950 py-8 px-4 print:bg-white print:p-0">
    <div class="mx-auto max-w-4xl">

        {{-- Barra de acções (não aparece na impressão) --}}
        <div class="no-print mb-4 flex items-center justify-between">
            <a href="{{ url()->previous() }}"
               class="inline-flex items-center gap-1 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Voltar
            </a>
            <div class="flex items-center gap-2">
                @if($croqui->temFicheiroPdfValido() && $croqui->podeBaixar(auth()->user()) && auth()->user()->temPermissao('documentos.ver'))
                    <a href="{{ route('storage.serve', ['path' => $croqui->ficheiro_pdf_caminho]) }}" target="_blank"
                       class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Ver PDF
                    </a>
                @endif
                @if($croqui->temFicheiroPdfValido() && $croqui->podeBaixar(auth()->user()) && auth()->user()->temPermissao('documentos.download'))
                    <a href="{{ route('storage.download', ['path' => $croqui->ficheiro_pdf_caminho]) }}"
                       class="inline-flex items-center gap-1.5 rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Baixar PDF
                    </a>
                @endif
                @if($croqui->temMemoriaDescritivaValida() && $croqui->podeBaixar(auth()->user()) && auth()->user()->temPermissao('documentos.ver'))
                    <a href="{{ route('municipio.operacoes.ids-terreno.memoria', [$municipio, $croqui]) }}" wire:navigate
                       class="inline-flex items-center gap-1.5 rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6M7 3h7l5 5v13H7a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                        </svg>
                        Ver memoria
                    </a>
                @endif
                <button onclick="window.print()"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Imprimir
                </button>
            </div>
        </div>

        {{-- ╔══════════════════════════════════════════════════════════╗
             ║           DOCUMENTO INOTU — CROQUI DE LOCALIZAÇÃO        ║
             ╚══════════════════════════════════════════════════════════╝ --}}
        <div class="croqui-document overflow-hidden rounded-xl bg-white text-zinc-900 shadow-lg ring-1 ring-zinc-200 dark:bg-zinc-900 dark:text-zinc-100 dark:ring-zinc-700 print:rounded-none print:bg-white print:text-black print:shadow-none print:ring-0" style="border: 2px solid #1a1a1a; font-family: Arial, sans-serif;">

            {{-- ══ CABEÇALHO DO DOCUMENTO ══════════════════════════════ --}}
            <div style="display: flex; border-bottom: 2px solid #1a1a1a;">

                {{-- Bloco institucional (esquerda) --}}
                <div style="flex: 1; border-right: 2px solid #1a1a1a; padding: 10px 14px;">
                    <div style="text-align: center; margin-bottom: 4px;">
                        {{-- Logo INOTU --}}
                        <div style="font-size: 9px; font-weight: bold; letter-spacing: 1px; color: #1a1a1a; text-transform: uppercase; line-height: 1.5;">
                            REPÚBLICA DE ANGOLA<br>
                            MINISTÉRIO DO URBANISMO E HABITAÇÃO<br>
                            <span style="font-size: 11px; color: #c0392b; letter-spacing: 2px;">INOTU</span><br>
                            Instituto Nacional de Ordenamento do Território e Urbanismo
                        </div>
                    </div>
                    <div style="border-top: 1px solid #ccc; margin: 6px 0;"></div>
                    <div style="text-align: center; font-size: 9px; color: #333;">
                        <div>DIRECÇÃO PROVINCIAL DE URBANISMO</div>
                        <div style="font-weight: bold;">{{ strtoupper($provincia?->nome ?? 'HUÍLA') }}</div>
                        <div>MUNICÍPIO DE {{ strtoupper($municipio?->nome ?? '—') }}</div>
                    </div>
                    <div style="border-top: 1px solid #ccc; margin: 6px 0;"></div>
                    <div style="text-align: center; font-size: 13px; font-weight: bold; letter-spacing: 1px; color: #1a1a1a; text-transform: uppercase;">
                        CROQUI DE LOCALIZAÇÃO
                    </div>
                    <div style="text-align: center; font-size: 10px; margin-top: 2px; color: #555;">
                        {{ $croqui->titulo }}
                    </div>
                </div>

                {{-- Bloco Micro-Localização (direita) --}}
                <div style="width: 200px; min-height: 130px; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #f9f9f9;">
                    @if($croqui->micro_localizacao_caminho)
                        <img src="{{ Storage::url($croqui->micro_localizacao_caminho) }}"
                             alt="Micro-Localização"
                             style="width: 100%; height: 100%; object-fit: cover; display: block;" />
                    @else
                        <div style="text-align: center; color: #aaa; font-size: 9px; padding: 10px;">
                            <svg xmlns="http://www.w3.org/2000/svg" style="width:32px;height:32px;margin:0 auto 4px;display:block;color:#ccc;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                            MICRO-LOCALIZAÇÃO
                        </div>
                    @endif
                </div>
            </div>

            {{-- ══ ÁREA DO ESBOÇO / PLANTA (espaço reservado para o desenho) ══ --}}
            <div style="min-height: 280px; border-bottom: 2px solid #1a1a1a; background: #fafafa; position: relative;">
                @if($croqui->temFicheiroPdfValido() && auth()->user()->temPermissao('documentos.ver'))
                    {{-- Visualizador seguro: URL real nunca exposta ao cliente --}}
                    <div class="no-print">
                        <livewire:visualizador-pdf
                            :caminho="$croqui->ficheiro_pdf_caminho"
                            :label="$croqui->ficheiro_pdf_nome ?? 'Croqui PDF'"
                            :podeDownload="$croqui->podeBaixar(auth()->user()) && auth()->user()->temPermissao('documentos.download')"
                        />
                    </div>
                    {{-- Para impressão: ícone de referência --}}
                    <div class="print-only" style="display:none; text-align: center; color: #888; font-size: 11px; padding: 20px;">
                        <strong>{{ $croqui->ficheiro_pdf_nome ?? 'Documento PDF' }}</strong><br>
                        Croqui ID: {{ $croqui->id }}
                    </div>
                @elseif($croqui->ficheiro_pdf_caminho)
                    <div style="text-align: center; color: #aaa; font-size: 11px; padding: 40px;">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width:40px;height:40px;margin:0 auto 8px;display:block;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Não tem permissão para visualizar este documento.
                    </div>
                @else
                    <div style="text-align: center; color: #ccc; font-size: 10px; padding: 40px; display: flex; align-items: center; justify-content: center; min-height: 280px;">
                        [Espaço reservado para o esboço / planta de localização]
                    </div>
                @endif
            </div>

            {{-- ══ TABELA DE COORDENADAS ════════════════════════════════ --}}
            @if(!empty($coordenadas))
            <div style="border-bottom: 2px solid #1a1a1a; padding: 0;">
                <table style="width: 100%; border-collapse: collapse; font-size: 9px;">
                    <thead>
                        <tr style="background: #f0f0f0;">
                            <th style="border: 1px solid #999; padding: 4px 6px; text-align: center; width: 30px;">PONTO</th>
                            <th style="border: 1px solid #999; padding: 4px 6px; text-align: center;" colspan="2">LATITUDE</th>
                            <th style="border: 1px solid #999; padding: 4px 6px; text-align: center;" colspan="2">LONGITUDE</th>
                        </tr>
                        <tr style="background: #f8f8f8;">
                            <th style="border: 1px solid #999; padding: 3px 6px;"></th>
                            <th style="border: 1px solid #999; padding: 3px 6px; text-align: center; font-weight: normal;">GMS</th>
                            <th style="border: 1px solid #999; padding: 3px 6px; text-align: center; font-weight: normal;">UTM Y (m)</th>
                            <th style="border: 1px solid #999; padding: 3px 6px; text-align: center; font-weight: normal;">GMS</th>
                            <th style="border: 1px solid #999; padding: 3px 6px; text-align: center; font-weight: normal;">UTM X (m)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($coordenadas as $i => $coord)
                        <tr>
                            <td style="border: 1px solid #ccc; padding: 3px 6px; text-align: center; font-weight: bold;">{{ $i + 1 }}</td>
                            <td style="border: 1px solid #ccc; padding: 3px 6px; text-align: center; font-family: monospace;">{{ $coord['lat_gms'] ?? ($coord['easting'] ?? '') }}</td>
                            <td style="border: 1px solid #ccc; padding: 3px 6px; text-align: center; font-family: monospace;">{{ $coord['lat_utm'] ?? '' }}</td>
                            <td style="border: 1px solid #ccc; padding: 3px 6px; text-align: center; font-family: monospace;">{{ $coord['lon_gms'] ?? ($coord['northing'] ?? '') }}</td>
                            <td style="border: 1px solid #ccc; padding: 3px 6px; text-align: center; font-family: monospace;">{{ $coord['lon_utm'] ?? '' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div style="padding: 2px 6px; font-size: 8px; color: #666; border-top: 1px solid #ddd;">
                    Datum WGS84 — UTM {{ $croqui->utm_zone ?? '33' }}{{ $croqui->utm_hemisphere ?? 'S' }}
                </div>
            </div>
            @endif

            {{-- ══ LEGENDA INFERIOR (tabela de informação + QR code) ════ --}}
            <div style="display: flex; border-top: 0;">

                {{-- Bloco de dados (esquerda + centro) --}}
                <div style="flex: 1; border-right: 2px solid #1a1a1a;">

                    {{-- Requerente / Proprietários --}}
                    <div style="border-bottom: 1px solid #bbb; padding: 4px 8px;">
                        <span style="font-size: 8px; font-weight: bold; text-transform: uppercase; color: #555;">Requerente / Proprietário:</span>
                        <span style="font-size: 9px; margin-left: 6px;">
                            @forelse($croqui->proprietarios as $prop)
                                {{ $prop->nome }}{{ !$loop->last ? ', ' : '' }}
                            @empty
                                —
                            @endforelse
                        </span>
                    </div>

                    {{-- Grid de informações da legenda --}}
                    <table style="width: 100%; border-collapse: collapse; font-size: 9px;">
                        <tr>
                            <td style="border: 1px solid #ccc; padding: 4px 8px; width: 25%;">
                                <div style="font-size: 7px; font-weight: bold; text-transform: uppercase; color: #888;">Área (m²)</div>
                                <div style="font-size: 10px; font-weight: bold;">{{ $croqui->area ? number_format($croqui->area, 4, '.', '') : '—' }}</div>
                            </td>
                            <td style="border: 1px solid #ccc; padding: 4px 8px; width: 25%;">
                                <div style="font-size: 7px; font-weight: bold; text-transform: uppercase; color: #888;">Perímetro (m)</div>
                                <div style="font-size: 10px; font-weight: bold;">{{ $croqui->perimetro ? number_format($croqui->perimetro, 4, '.', '') : '—' }}</div>
                            </td>
                            <td style="border: 1px solid #ccc; padding: 4px 8px; width: 25%;">
                                <div style="font-size: 7px; font-weight: bold; text-transform: uppercase; color: #888;">Dimensão</div>
                                <div style="font-size: 10px; font-weight: bold;">{{ $croqui->dimensao ?? '—' }}</div>
                            </td>
                            <td style="border: 1px solid #ccc; padding: 4px 8px; width: 25%;">
                                <div style="font-size: 7px; font-weight: bold; text-transform: uppercase; color: #888;">Escala</div>
                                <div style="font-size: 10px; font-weight: bold;">{{ $croqui->escala ?? '—' }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ccc; padding: 4px 8px;" colspan="2">
                                <div style="font-size: 7px; font-weight: bold; text-transform: uppercase; color: #888;">Finalidade</div>
                                <div style="font-size: 10px;">{{ $croqui->finalidade ?? '—' }}</div>
                            </td>
                            <td style="border: 1px solid #ccc; padding: 4px 8px;">
                                <div style="font-size: 7px; font-weight: bold; text-transform: uppercase; color: #888;">Folha Nº</div>
                                <div style="font-size: 10px; font-weight: bold;">{{ $croqui->folha_numero ?? '—' }}</div>
                            </td>
                            <td style="border: 1px solid #ccc; padding: 4px 8px;">
                                <div style="font-size: 7px; font-weight: bold; text-transform: uppercase; color: #888;">Data</div>
                                <div style="font-size: 10px;">{{ $croqui->data_folha ?? ($croqui->created_at?->format('m/Y') ?? '—') }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ccc; padding: 4px 8px;" colspan="3">
                                <div style="font-size: 7px; font-weight: bold; text-transform: uppercase; color: #888;">Técnico Responsável</div>
                                <div style="font-size: 10px;">{{ $croqui->tecnico_responsavel ?? ($croqui->criadoPor?->name ?? '—') }}</div>
                            </td>
                            <td style="border: 1px solid #ccc; padding: 4px 8px;">
                                <div style="font-size: 7px; font-weight: bold; text-transform: uppercase; color: #888;">Ano</div>
                                <div style="font-size: 10px; font-weight: bold;">{{ $croqui->ano ?? $croqui->created_at?->year ?? '—' }}</div>
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- Bloco QR Code (direita) — posição exacta definida pelo layout do documento --}}
                <div style="width: 120px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 10px; background: #fff;">
                    <canvas
                        x-data
                        x-init="renderQrCode($el, '{{ $qrUrl }}', 90)"
                        width="90"
                        height="90"
                        style="display: block;"
                    ></canvas>
                    <div style="margin-top: 4px; text-align: center; font-size: 7px; color: #666; line-height: 1.3;">
                        <div style="font-weight: bold; text-transform: uppercase; font-size: 6px; color: #999;">Autenticidade</div>
                        <div style="font-size: 7px; color: #555;">Croqui #{{ $croqui->id }}</div>
                        @if($croqui->verificado)
                            <div style="color: #16a34a; font-weight: bold; font-size: 7px;">✓ Verificado</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ══ ESTADO DE HOMOLOGAÇÃO (rodapé) ══════════════════════ --}}
            <div style="border-top: 1px solid #ccc; padding: 5px 10px; background: #f9f9f9; display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 8px; color: #666;">
                    @if($croqui->rup_pago)
                        <span style="color: #16a34a; font-weight: bold;">RUP Pago</span>
                        @if($croqui->rup_referencia)
                            <span style="margin-left: 6px;">Ref: {{ $croqui->rup_referencia }}</span>
                        @endif
                    @else
                        <span style="color: #d97706; font-weight: bold;">RUP Pendente</span>
                    @endif

                    @if($croqui->download_liberado)
                        &nbsp;·&nbsp; <span style="color: #16a34a;">Download Liberado</span>
                    @else
                        &nbsp;·&nbsp; <span style="color: #dc2626;">Pendente de Homologação</span>
                    @endif
                </div>
                <div style="font-size: 8px; color: #999;">
                    Gerado em {{ now()->format('d/m/Y H:i') }} · G.TERRAS — INOTU
                </div>
            </div>
        </div>

        {{-- ══ INFORMAÇÕES EXTRAS (apenas ecrã, não imprime) ══════════ --}}
        <div class="no-print mt-6 space-y-4">

            {{-- Proprietários (detalhe completo) --}}
            @if($croqui->proprietarios->isNotEmpty())
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-3">Dados dos Proprietários</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Nome</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">BI / NIF</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Contacto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach($croqui->proprietarios as $prop)
                            <tr>
                                <td class="px-4 py-2 font-medium text-zinc-900 dark:text-zinc-100">{{ $prop->nome }}</td>
                                <td class="px-4 py-2 text-zinc-500 dark:text-zinc-400">{{ $prop->bi ?? '—' }}</td>
                                <td class="px-4 py-2 text-zinc-500 dark:text-zinc-400">{{ $prop->contacto ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            @if($croqui->temMemoriaDescritivaValida() && auth()->user()->temPermissao('documentos.ver'))
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-3">Memoria Descritiva</h3>
                <livewire:visualizador-pdf
                    :caminho="$croqui->memoria_descritiva_caminho"
                    :label="$croqui->memoria_descritiva_nome ?? 'Memoria descritiva'"
                    :podeDownload="$croqui->podeBaixar(auth()->user()) && auth()->user()->temPermissao('documentos.download')"
                />
            </div>
            @endif

            {{-- Metadados do sistema --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-3">Informações do Registo</h3>
                <dl class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Registado por</dt>
                        <dd class="mt-0.5 text-zinc-700 dark:text-zinc-300">{{ $croqui->criadoPor?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Data de registo</dt>
                        <dd class="mt-0.5 text-zinc-700 dark:text-zinc-300">{{ $croqui->created_at?->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Última actualização</dt>
                        <dd class="mt-0.5 text-zinc-700 dark:text-zinc-300">{{ $croqui->updated_at?->format('d/m/Y H:i') }}</dd>
                    </div>
                    @if($croqui->liberado_por)
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Homologado por</dt>
                        <dd class="mt-0.5 text-zinc-700 dark:text-zinc-300">{{ \App\Models\User::find($croqui->liberado_por)?->name ?? '—' }}</dd>
                    </div>
                    @endif
                    @if($croqui->liberado_em)
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Data de homologação</dt>
                        <dd class="mt-0.5 text-zinc-700 dark:text-zinc-300">{{ $croqui->liberado_em?->format('d/m/Y') }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</div>

<style>
    .croqui-document {
        color-scheme: light dark;
    }

    .dark .croqui-document {
        border-color: #52525b !important;
    }

    .dark .croqui-document [style*="border"],
    .dark .croqui-document table,
    .dark .croqui-document th,
    .dark .croqui-document td {
        border-color: #52525b !important;
    }

    .dark .croqui-document [style*="background: #f9f9f9"],
    .dark .croqui-document [style*="background: #fafafa"],
    .dark .croqui-document [style*="background: #f8f8f8"],
    .dark .croqui-document [style*="background: #f0f0f0"] {
        background: #27272a !important;
    }

    .dark .croqui-document [style*="background: #fff"] {
        background: #18181b !important;
    }

    .dark .croqui-document [style*="color: #1a1a1a"],
    .dark .croqui-document [style*="color: #333"],
    .dark .croqui-document [style*="color: #555"],
    .dark .croqui-document [style*="color: #666"] {
        color: #d4d4d8 !important;
    }

    .dark .croqui-document [style*="color: #888"],
    .dark .croqui-document [style*="color: #999"],
    .dark .croqui-document [style*="color: #aaa"],
    .dark .croqui-document [style*="color: #ccc"] {
        color: #a1a1aa !important;
    }

    .dark .croqui-document table,
    .dark .croqui-document td,
    .dark .croqui-document th,
    .dark .croqui-document span:not([style*="color: #16a34a"]):not([style*="color: #d97706"]):not([style*="color: #dc2626"]) {
        color: inherit;
    }

    .dark .croqui-document canvas {
        background: #fff;
        border-radius: 4px;
    }

    @media print {
        .no-print { display: none !important; }
        body { margin: 0; padding: 0; background: white; }
        .min-h-screen { min-height: auto; padding: 0; }
        .mx-auto { max-width: 100% !important; }
        .shadow-lg { box-shadow: none !important; }
        .croqui-document,
        .croqui-document * {
            background-color: white !important;
            color: black !important;
            border-color: #1a1a1a !important;
        }
    }
</style>

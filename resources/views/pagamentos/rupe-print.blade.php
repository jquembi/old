<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RUPE — {{ $pagamento->rupe_referencia }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: #1e293b;
            padding: 2rem;
        }

        .document {
            max-width: 720px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,.08);
        }

        /* ── Cabeçalho ── */
        .header {
            background: #1e3a5f;
            color: #fff;
            padding: 1.75rem 2rem;
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }
        .header-logo {
            width: 56px;
            height: 56px;
            background: rgba(255,255,255,.15);
            border-radius: .75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -.5px;
        }
        .header-title { font-size: 1.25rem; font-weight: 700; line-height: 1.3; }
        .header-subtitle { font-size: .8rem; opacity: .75; margin-top: .25rem; }

        /* ── Bandeira RUPE ── */
        .rupe-banner {
            background: #2563eb;
            color: #fff;
            text-align: center;
            padding: 1rem;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .15em;
            text-transform: uppercase;
        }

        /* ── Referência destacada ── */
        .ref-block {
            padding: 2rem;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
        }
        .ref-label {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: .5rem;
        }
        .ref-number {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: .05em;
            color: #1e3a5f;
            font-variant-numeric: tabular-nums;
            font-family: 'Courier New', monospace;
        }
        .ref-entity {
            margin-top: .75rem;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: #f1f5f9;
            border-radius: .5rem;
            padding: .5rem 1rem;
            font-size: .85rem;
        }
        .ref-entity strong { color: #1e3a5f; }

        /* ── Detalhes ── */
        .details {
            padding: 1.5rem 2rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .detail-item label {
            font-size: .7rem;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #64748b;
            display: block;
            margin-bottom: .25rem;
        }
        .detail-item .value {
            font-size: .95rem;
            font-weight: 600;
            color: #1e293b;
        }
        .detail-item .value.money {
            font-size: 1.15rem;
            color: #15803d;
            font-variant-numeric: tabular-nums;
        }
        .detail-item .value.warning {
            color: #b45309;
        }

        /* ── Instruções ── */
        .instructions {
            padding: 1.5rem 2rem;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        .instructions h3 {
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: .75rem;
        }
        .instructions ol {
            list-style: decimal;
            padding-left: 1.25rem;
            space-y: .5rem;
        }
        .instructions li {
            font-size: .82rem;
            color: #475569;
            margin-bottom: .4rem;
            line-height: 1.5;
        }

        /* ── Rodapé ── */
        .footer {
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: .72rem;
            color: #94a3b8;
        }

        /* ── Botão imprimir (oculto na impressão) ── */
        .no-print {
            text-align: center;
            padding: 1.5rem;
        }
        .btn-print {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: #1e3a5f;
            color: #fff;
            border: none;
            border-radius: .75rem;
            padding: .75rem 1.75rem;
            font-size: .9rem;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-print:hover { background: #162d4a; }

        @media print {
            body { background: #fff; padding: 0; }
            .document { border: none; border-radius: 0; box-shadow: none; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="document">

    {{-- Cabeçalho INOTU --}}
    <div class="header">
        <div class="header-logo">IN</div>
        <div>
            <div class="header-title">INOTU — Instituto Nacional de Ordenamento do Território e Urbanismo</div>
            <div class="header-subtitle">República de Angola · Ministério do Ordenamento do Território</div>
        </div>
    </div>

    {{-- Banner RUPE --}}
    <div class="rupe-banner">Referência Única de Pagamento ao Estado (RUPE)</div>

    {{-- Referência em destaque --}}
    <div class="ref-block">
        <p class="ref-label">Número de referência</p>
        <p class="ref-number">{{ $pagamento->rupe_referencia }}</p>
        <div class="ref-entity">
            <span>Entidade:</span>
            <strong>{{ $pagamento->rupe_entidade }}</strong>
        </div>
    </div>

    {{-- Detalhes --}}
    <div class="details">
        <div class="detail-item">
            <label>Valor a pagar</label>
            <div class="value money">{{ number_format((float) $pagamento->valor, 2, ',', '.') }} Kz</div>
        </div>
        <div class="detail-item">
            <label>Data limite de pagamento</label>
            <div class="value {{ $pagamento->rupe_data_limite?->isPast() ? 'warning' : '' }}">
                {{ $pagamento->rupe_data_limite?->format('d/m/Y') }}
                @if($pagamento->rupe_data_limite?->isPast())
                    (vencido)
                @endif
            </div>
        </div>
        <div class="detail-item">
            <label>Município</label>
            <div class="value">{{ $pagamento->municipio?->nome ?? '—' }}</div>
        </div>
        <div class="detail-item">
            <label>Processo / Croqui</label>
            <div class="value">{{ $pagamento->croqui?->titulo ?? 'Nº ' . $pagamento->id }}</div>
        </div>
        @if($pagamento->rupe_gerado_em)
            <div class="detail-item">
                <label>Data de emissão</label>
                <div class="value">{{ $pagamento->rupe_gerado_em->format('d/m/Y') }}</div>
            </div>
        @endif
        @if($pagamento->observacoes)
            <div class="detail-item" style="grid-column: span 2;">
                <label>Observações</label>
                <div class="value" style="font-weight:400;">{{ $pagamento->observacoes }}</div>
            </div>
        @endif
    </div>

    {{-- Instruções --}}
    <div class="instructions">
        <h3>Instruções de pagamento</h3>
        <ol>
            <li>Aceda ao portal RUPE Angola (<strong>rupe.mf.gov.ao</strong>) ou dirija-se a qualquer banco ou balcão autorizado.</li>
            <li>Seleccione o método de pagamento (Multicaixa, transferência bancária ou balcão).</li>
            <li>Introduza a referência e a entidade indicadas neste documento.</li>
            <li>Confirme o valor e conclua o pagamento dentro do prazo indicado.</li>
            <li>Guarde o comprovativo de pagamento e submeta-o no portal INOTU.</li>
        </ol>
    </div>

    {{-- Rodapé --}}
    <div class="footer">
        <span>Emitido em {{ now()->format('d/m/Y H:i') }}</span>
        <span>INOTU · inotu.gov.ao</span>
    </div>
</div>

{{-- Botão imprimir --}}
<div class="no-print">
    <button class="btn-print" onclick="window.print()">
        &#128424; Imprimir / Guardar PDF
    </button>
</div>

</body>
</html>

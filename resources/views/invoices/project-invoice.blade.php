<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fatura - Projeto G.TERRAS</title>
    <link rel="shortcut icon" href="{{ asset('imagens/favicon/favicon.ico') }}" type="image/x-icon">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Merriweather', serif;
            color: #333;
            background: white;
            padding: 20px;
        }

        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border: 1px solid #ddd;
        }

        .invoice-actions {
            max-width: 900px;
            margin: 0 auto 12px;
            display: flex;
            justify-content: flex-end;
        }

        .pdf-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            padding: 0 16px;
            border-radius: 4px;
            background: #1a237e;
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 2px 5px rgba(26, 35, 126, 0.18);
        }

        .pdf-button:hover {
            background: #0f165c;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #1a237e;
            padding-bottom: 20px;
        }

        .company-info h1 {
            color: #1a237e;
            font-size: 28px;
            margin-bottom: 5px;
        }

        .company-info p {
            color: #666;
            font-size: 12px;
            margin: 2px 0;
        }

        .invoice-title {
            text-align: right;
        }

        .invoice-title h2 {
            color: #1a237e;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .invoice-title p {
            color: #999;
            font-size: 11px;
        }

        .invoice-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .detail-section h3 {
            color: #1a237e;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 8px;
        }

        .detail-section p {
            font-size: 13px;
            line-height: 1.8;
            color: #333;
        }

        .detail-section .label {
            color: #999;
            font-weight: 500;
        }

        .items-table {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
        }

        .items-table thead {
            background: #f5f5f5;
            border-top: 2px solid #1a237e;
            border-bottom: 2px solid #1a237e;
        }

        .items-table th {
            padding: 12px;
            text-align: left;
            font-size: 12px;
            color: #1a237e;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .items-table td {
            padding: 12px;
            font-size: 13px;
            border-bottom: 1px solid #eee;
        }

        .items-table tbody tr:last-child td {
            border-bottom: 2px solid #1a237e;
        }

        .items-table .description small {
            display: block;
            color: #666;
            font-size: 11px;
            margin-top: 4px;
            line-height: 1.4;
        }

        .text-right {
            text-align: right;
        }

        .amount {
            font-family: 'Courier New', monospace;
            font-weight: 500;
        }

        .summary {
            margin-bottom: 30px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }

        .summary-row.total {
            font-size: 16px;
            font-weight: 700;
            border-top: 2px solid #1a237e;
            border-bottom: 2px solid #1a237e;
            padding: 15px 0;
            color: #1a237e;
        }

        .summary-row.highlight {
            background: #f9f9f9;
            padding: 12px;
            margin: 5px 0;
        }

        .summary-row.deduction {
            color: #8a4b08;
        }

        .payment-terms {
            margin-top: 30px;
            padding: 20px;
            background: #f9f9f9;
            border-left: 4px solid #1a237e;
        }

        .payment-terms h3 {
            color: #1a237e;
            font-size: 13px;
            text-transform: uppercase;
            margin-bottom: 12px;
            letter-spacing: 0.5px;
        }

        .payment-terms p {
            font-size: 12px;
            margin: 8px 0;
            color: #333;
        }

        .installment {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 15px;
        }

        .installment-box {
            background: white;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .installment-box .label {
            font-size: 11px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .installment-box .amount {
            font-size: 16px;
            font-weight: 700;
            color: #1a237e;
            margin-top: 5px;
        }

        .notes {
            margin-top: 30px;
            padding: 20px;
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 4px;
        }

        .notes h3 {
            color: #856404;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .notes p {
            font-size: 12px;
            color: #856404;
            line-height: 1.6;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 11px;
            color: #999;
        }

        .features-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .features-section h3 {
            color: #1a237e;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 12px;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #1a237e;
            padding-bottom: 8px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .feature-item {
            font-size: 12px;
            color: #333;
            line-height: 1.5;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 4px;
            border-left: 3px solid #1a237e;
        }

        .feature-item strong {
            color: #1a237e;
            display: block;
            margin-bottom: 4px;
        }

        .module-list {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .module-list th {
            background: #f5f5f5;
            color: #1a237e;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        .module-list td {
            font-size: 12px;
            padding: 8px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        .module-list .module-name {
            font-weight: 600;
            color: #1a237e;
        }

        .module-list .module-desc {
            display: block;
            color: #666;
            font-size: 11px;
            margin-top: 3px;
            line-height: 1.35;
        }

        @media print {
            body {
                padding: 0;
            }

            .invoice-actions {
                display: none;
            }

            .invoice-container {
                border: none;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    @php
        $pdfMode = $pdfMode ?? false;
        $dataEmissao = $dataEmissao ?? '11 de Maio de 2026';
        $valorBase = $valorBase ?? 350000;
        $valorPorModulo = $valorPorModulo ?? 25000;

        $modulosAdicionados = $modulosAdicionados ?? [
            ['nome' => 'Centro de notificacoes', 'descricao' => 'Alertas internos e acompanhamento de eventos do sistema.'],
            ['nome' => 'Pesquisa global', 'descricao' => 'Busca centralizada por usuarios, auditoria, municipios, instrumentos e processos.'],
            ['nome' => 'Visualizador PDF', 'descricao' => 'Visualizacao segura de documentos PDF dentro do sistema.'],
            ['nome' => 'Gestao de pagamentos', 'descricao' => 'Gestao RUPE, comprovativos e estados de pagamento.'],
            ['nome' => 'Autenticação de Documentos', 'descricao' => 'Sistema de autenticação e rastreamento de documentos via codigo QR.'],
            ['nome' => 'Lixeira do sistema', 'descricao' => 'Envio, restauracao e eliminacao definitiva de registos.'],
            ['nome' => 'IDs de terreno', 'descricao' => 'Gestao de identificadores territoriais vinculados a croquis.'],
            ['nome' => 'Detalhe de ID de terreno', 'descricao' => 'Consulta tecnica do identificador territorial.'],
            ['nome' => 'Memoria descritiva', 'descricao' => 'Visualizacao e gestao da memoria descritiva associada ao terreno.'],
        ];

        $despesasTrabalho = $despesasTrabalho ?? [
            ['descricao' => 'Despesa de condicoes de trabalho - deslocacao', 'valor' => 20000],
            ['descricao' => 'Despesa de condicoes de trabalho - internet', 'valor' => 2000],
            ['descricao' => 'Despesa de condicoes de trabalho - deslocacao', 'valor' => 20000],
            ['descricao' => 'Despesa de condicoes de trabalho - deslocacao/apoio operacional', 'valor' => 15000],
            ['descricao' => 'Despesa de condicoes de trabalho - internet', 'valor' => 2000],
            ['descricao' => 'Despesa de condicoes de trabalho - internet/deslocacao', 'valor' => 8000],
        ];

        $quantidadeModulos = $quantidadeModulos ?? count($modulosAdicionados);
        $totalModulos = $totalModulos ?? ($quantidadeModulos * $valorPorModulo);
        $subtotalBruto = $subtotalBruto ?? ($valorBase + $totalModulos);
        $totalDespesas = $totalDespesas ?? collect($despesasTrabalho)->sum('valor');
        $saldoPagar = $saldoPagar ?? ($subtotalBruto - $totalDespesas);
        $primeiraPrestacao = $primeiraPrestacao ?? round($saldoPagar * 0.60);
        $segundaPrestacao = $segundaPrestacao ?? ($saldoPagar - $primeiraPrestacao);
        $formatarKz = fn ($valor) => number_format($valor, 2, ',', '.') . ' KZA';
    @endphp

    @unless($pdfMode)
        <div class="invoice-actions">
            <a href="{{ route('invoice.project.pdf') }}" class="pdf-button">Exportar PDF</a>
        </div>
    @endunless

    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <h1>INOTU</h1>
                <p><strong>Instituto Nacional de Ordenamento do Território e Urbanismo</strong></p>
                <p>República de Angola — Província da Huíla</p>
            </div>
            <div class="invoice-title">
                <h2>FATURA</h2>
                <p>Data: {{ $dataEmissao }}</p>
            </div>
        </div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            <div class="detail-section">
                <h3>Para:</h3>
                <p><strong>Instituto Nacional de Ordenamento do Território e Urbanismo</strong></p>
                <p>Projecto SONA — Gestão Provincial de Terras</p>
                <p>Huíla, Angola</p>
            </div>
            <div class="detail-section">
                <h3>Detalhes da Fatura:</h3>
                <p><span class="label">Número:</span> <strong>INV-2026-001</strong></p>
                <p><span class="label">Data de Emissão:</span> <strong>{{ $dataEmissao }}</strong></p>
                <p><span class="label">Serviço:</span> <strong>Desenvolvimento e ampliação funcional do sistema</strong></p>
            </div>
        </div>

        <!-- Line Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Quantidade</th>
                    <th>Valor Unitário</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="description">
                        <strong>Desenvolvimento e Implementação do Sistema G.TERRAS</strong>
                        <small>Valor base do sistema sem desconto comercial aplicado.</small>
                    </td>
                    <td class="text-right">1</td>
                    <td class="text-right">{{ $formatarKz($valorBase) }}</td>
                    <td class="text-right amount">{{ $formatarKz($valorBase) }}</td>
                </tr>
                <tr>
                    <td class="description">
                        <strong>Módulos funcionais adicionados ao sistema</strong>
                        <small>{{ $quantidadeModulos }} módulos novos contabilizados a {{ $formatarKz($valorPorModulo) }} por módulo. Não inclui correções visuais nem correções internas do sistema.</small>
                    </td>
                    <td class="text-right">{{ $quantidadeModulos }}</td>
                    <td class="text-right">{{ $formatarKz($valorPorModulo) }}</td>
                    <td class="text-right amount">{{ $formatarKz($totalModulos) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Summary -->
        <div class="summary">
            <div class="summary-row">
                <span>Valor base do sistema:</span>
                <span class="amount">{{ $formatarKz($valorBase) }}</span>
            </div>
            <div class="summary-row">
                <span>Módulos adicionados ({{ $quantidadeModulos }} x {{ $formatarKz($valorPorModulo) }}):</span>
                <span class="amount">{{ $formatarKz($totalModulos) }}</span>
            </div>
            <div class="summary-row highlight">
                <span><strong>Subtotal bruto sem descontos comerciais:</strong></span>
                <span class="amount"><strong>{{ $formatarKz($subtotalBruto) }}</strong></span>
            </div>
            @foreach($despesasTrabalho as $despesa)
                <div class="summary-row deduction">
                    <span>{{ $despesa['descricao'] }}:</span>
                    <span class="amount">({{ $formatarKz($despesa['valor']) }})</span>
                </div>
            @endforeach
            <div class="summary-row deduction">
                <span><strong>Total de despesas abatidas:</strong></span>
                <span class="amount"><strong>({{ $formatarKz($totalDespesas) }})</strong></span>
            </div>
            <div class="summary-row">
                <span>Subtotal após abatimento das despesas:</span>
                <span class="amount">{{ $formatarKz($saldoPagar) }}</span>
            </div>
            <div class="summary-row total">
                <span>Total final a pagar:</span>
                <span class="amount">{{ $formatarKz($saldoPagar) }}</span>
            </div>
        </div>

        <!-- Payment Terms -->
        <div class="payment-terms">
            <h3>Modalidade de Pagamento — Duas Prestações</h3>
            <p>O saldo total final, já descontadas as despesas de condições de trabalho, será pago em duas prestações conforme discriminado abaixo:</p>
            <div class="installment">
                <div class="installment-box">
                    <div class="label">Primeira Prestação (60%)</div>
                    <div class="amount">{{ $formatarKz($primeiraPrestacao) }}</div>
                    <p style="font-size: 11px; color: #666; margin-top: 5px;">Pagável na assinatura</p>
                </div>
                <div class="installment-box">
                    <div class="label">Segunda Prestação (40%)</div>
                    <div class="amount">{{ $formatarKz($segundaPrestacao) }}</div>
                    <p style="font-size: 11px; color: #666; margin-top: 5px;">Pagável em 30 dias</p>
                </div>
            </div>
        </div>

        <!-- Project Features -->
        <div class="features-section">
            <h3>Módulos Funcionais Adicionados</h3>
            <table class="module-list">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Módulo / funcionalidade nova</th>
                        <th style="width: 140px; text-align: right;">Valor adicionado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($modulosAdicionados as $index => $modulo)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <span class="module-name">{{ $modulo['nome'] }}</span>
                                <span class="module-desc">{{ $modulo['descricao'] }}</span>
                            </td>
                            <td class="text-right amount">{{ $formatarKz($valorPorModulo) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Notes -->
        <div class="notes">
            <h3>Notas Importantes</h3>
            <p>
                ✓ Não foi aplicado desconto comercial; foram apenas abatidas despesas de condições de trabalho.<br>
                ✓ Os módulos contabilizados representam novas funcionalidades adicionadas ao sistema.<br>
                ✓ Correções visuais e correções internas do sistema não foram faturadas como módulos novos.<br>
                ✓ Total de despesas abatidas: {{ $formatarKz($totalDespesas) }} referentes a deslocação, internet e apoio operacional.<br>
                ✓ Total final a pagar após abatimento das despesas: {{ $formatarKz($saldoPagar) }}.<br>
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Esta fatura é válida e tem força legal. Emitida em {{ $dataEmissao }}.</p>
            <p>© 2026 INOTU — Instituto Nacional de Ordenamento do Território e Urbanismo</p>
        </div>
    </div>
</body>
</html>

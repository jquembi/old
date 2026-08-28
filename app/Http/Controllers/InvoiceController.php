<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    /**
     * Mostra a fatura do projecto G.TERRAS no navegador.
     */
    public function mostrarFaturaProjeto(): View
    {
        return view('invoices.project-invoice', $this->dadosFatura());
    }

    /**
     * Gera a fatura do projecto G.TERRAS em PDF.
     */
    public function gerarFaturaProjeto(): Response
    {
        $dados = $this->dadosFatura(pdfMode: true);
        $pdf = new \FPDF('P', 'mm', 'A4');

        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();
        $pdf->SetDrawColor(26, 35, 126);
        $pdf->SetTextColor(51, 51, 51);

        $this->cabecalhoPdf($pdf, $dados);
        $this->detalhesPdf($pdf, $dados);
        $this->itensPdf($pdf, $dados);
        $this->resumoPdf($pdf, $dados);
        $this->prestacoesPdf($pdf, $dados);
        $this->modulosPdf($pdf, $dados);
        $this->notasPdf($pdf, $dados);

        $conteudo = $pdf->Output('S');

        return response($conteudo, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="G.TERRAS_Fatura_2026.pdf"',
        ]);
    }

    private function dadosFatura(bool $pdfMode = false): array
    {
        $valorBase = 350000;
        $valorPorModulo = 25000;
        $modulosAdicionados = [
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

        $despesasTrabalho = [
            ['descricao' => 'Despesa de condicoes de trabalho - deslocacao', 'valor' => 20000],
            ['descricao' => 'Despesa de condicoes de trabalho - internet', 'valor' => 2000],
            ['descricao' => 'Despesa de condicoes de trabalho - deslocacao', 'valor' => 20000],
            ['descricao' => 'Despesa de condicoes de trabalho - deslocacao/apoio operacional', 'valor' => 15000],
            ['descricao' => 'Despesa de condicoes de trabalho - internet', 'valor' => 2000],
            ['descricao' => 'Despesa de condicoes de trabalho - internet/deslocacao', 'valor' => 8000],
        ];

        $quantidadeModulos = count($modulosAdicionados);
        $totalModulos = $quantidadeModulos * $valorPorModulo;
        $subtotalBruto = $valorBase + $totalModulos;
        $totalDespesas = array_sum(array_column($despesasTrabalho, 'valor'));
        $saldoPagar = $subtotalBruto - $totalDespesas;
        $primeiraPrestacao = (int) round($saldoPagar * 0.60);

        return [
            'pdfMode' => $pdfMode,
            'dataEmissao' => '11 de Maio de 2026',
            'valorBase' => $valorBase,
            'valorPorModulo' => $valorPorModulo,
            'modulosAdicionados' => $modulosAdicionados,
            'despesasTrabalho' => $despesasTrabalho,
            'quantidadeModulos' => $quantidadeModulos,
            'totalModulos' => $totalModulos,
            'subtotalBruto' => $subtotalBruto,
            'totalDespesas' => $totalDespesas,
            'saldoPagar' => $saldoPagar,
            'primeiraPrestacao' => $primeiraPrestacao,
            'segundaPrestacao' => $saldoPagar - $primeiraPrestacao,
        ];
    }

    private function cabecalhoPdf(\FPDF $pdf, array $dados): void
    {
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->SetTextColor(26, 35, 126);
        $pdf->Cell(120, 8, $this->pdfTexto('INOTU'), 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(60, 8, $this->pdfTexto('FATURA'), 0, 1, 'R');
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(102, 102, 102);
        $pdf->Cell(120, 6, $this->pdfTexto('Instituto Nacional de Ordenamento do Territorio e Urbanismo'), 0, 0, 'L');
        $pdf->Cell(60, 6, $this->pdfTexto('Data: ' . $dados['dataEmissao']), 0, 1, 'R');
        $pdf->Cell(180, 6, $this->pdfTexto('Republica de Angola - Provincia da Huila'), 0, 1, 'L');
        $pdf->Line(15, $pdf->GetY() + 3, 195, $pdf->GetY() + 3);
        $pdf->Ln(9);
    }

    private function detalhesPdf(\FPDF $pdf, array $dados): void
    {
        $pdf->SetTextColor(26, 35, 126);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(90, 6, $this->pdfTexto('Para:'), 0, 0);
        $pdf->Cell(90, 6, $this->pdfTexto('Detalhes da Fatura:'), 0, 1);
        $pdf->SetTextColor(51, 51, 51);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(90, 6, $this->pdfTexto('Instituto Nacional de Ordenamento do Territorio e Urbanismo'), 0, 0);
        $pdf->Cell(90, 6, $this->pdfTexto('Numero: INV-2026-001'), 0, 1);
        $pdf->Cell(90, 6, $this->pdfTexto('Projecto SONA - Gestao Provincial de Terras'), 0, 0);
        $pdf->Cell(90, 6, $this->pdfTexto('Data de Emissao: ' . $dados['dataEmissao']), 0, 1);
        $pdf->Cell(90, 6, $this->pdfTexto('Huila, Angola'), 0, 0);
        $pdf->Cell(90, 6, $this->pdfTexto('Servico: Desenvolvimento e ampliacao funcional'), 0, 1);
        $pdf->Ln(8);
    }

    private function itensPdf(\FPDF $pdf, array $dados): void
    {
        $this->tituloPdf($pdf, 'Itens da Fatura');
        $this->linhaTabelaPdf($pdf, ['Descricao', 'Qtd.', 'Unitario', 'Total'], [94, 18, 34, 34], true);
        $this->linhaTabelaPdf($pdf, [
            'Desenvolvimento e Implementacao do Sistema G.TERRAS',
            '1',
            $this->formatarKz($dados['valorBase']),
            $this->formatarKz($dados['valorBase']),
        ], [94, 18, 34, 34]);
        $this->linhaTabelaPdf($pdf, [
            $dados['quantidadeModulos'] . ' modulos funcionais adicionados',
            (string) $dados['quantidadeModulos'],
            $this->formatarKz($dados['valorPorModulo']),
            $this->formatarKz($dados['totalModulos']),
        ], [94, 18, 34, 34]);
        $pdf->Ln(7);
    }

    private function resumoPdf(\FPDF $pdf, array $dados): void
    {
        $this->tituloPdf($pdf, 'Resumo Financeiro');
        $this->resumoLinhaPdf($pdf, 'Valor base do sistema:', $dados['valorBase']);
        $this->resumoLinhaPdf($pdf, 'Modulos adicionados (' . $dados['quantidadeModulos'] . ' x ' . $this->formatarKz($dados['valorPorModulo']) . '):', $dados['totalModulos']);
        $this->resumoLinhaPdf($pdf, 'Subtotal bruto sem descontos comerciais:', $dados['subtotalBruto'], destaque: true);

        foreach ($dados['despesasTrabalho'] as $despesa) {
            $this->resumoLinhaPdf($pdf, $despesa['descricao'] . ':', -$despesa['valor']);
        }

        $this->resumoLinhaPdf($pdf, 'Total de despesas abatidas:', -$dados['totalDespesas'], destaque: true);
        $this->resumoLinhaPdf($pdf, 'Total final a pagar:', $dados['saldoPagar'], destaque: true);
        $pdf->Ln(6);
    }

    private function prestacoesPdf(\FPDF $pdf, array $dados): void
    {
        $this->tituloPdf($pdf, 'Modalidade de Pagamento');
        $pdf->SetFont('Arial', '', 9);
        $pdf->MultiCell(180, 5, $this->pdfTexto('O saldo total final, ja descontadas as despesas de condicoes de trabalho, sera pago em duas prestacoes.'));
        $pdf->Ln(2);
        $this->linhaTabelaPdf($pdf, ['Primeira Prestacao (60%)', 'Segunda Prestacao (40%)'], [90, 90], true);
        $this->linhaTabelaPdf($pdf, [$this->formatarKz($dados['primeiraPrestacao']), $this->formatarKz($dados['segundaPrestacao'])], [90, 90]);
        $pdf->Ln(7);
    }

    private function modulosPdf(\FPDF $pdf, array $dados): void
    {
        $this->tituloPdf($pdf, 'Modulos Funcionais Adicionados');
        $this->linhaTabelaPdf($pdf, ['#', 'Modulo / funcionalidade nova', 'Valor'], [10, 126, 44], true);

        foreach ($dados['modulosAdicionados'] as $index => $modulo) {
            $this->novaPaginaSePreciso($pdf, 10);
            $this->linhaTabelaPdf($pdf, [
                (string) ($index + 1),
                $modulo['nome'],
                $this->formatarKz($dados['valorPorModulo']),
            ], [10, 126, 44]);
        }

        $pdf->Ln(5);
    }

    private function notasPdf(\FPDF $pdf, array $dados): void
    {
        $this->tituloPdf($pdf, 'Notas Importantes');
        $pdf->SetFont('Arial', '', 9);
        $pdf->MultiCell(180, 5, $this->pdfTexto('Nao foi aplicado desconto comercial; foram apenas abatidas despesas de condicoes de trabalho.'));
        $pdf->MultiCell(180, 5, $this->pdfTexto('Correcoes visuais e correcoes internas do sistema nao foram faturadas como modulos novos.'));
        $pdf->MultiCell(180, 5, $this->pdfTexto('Total de despesas abatidas: ' . $this->formatarKz($dados['totalDespesas']) . '. Total final a pagar: ' . $this->formatarKz($dados['saldoPagar']) . '.'));
        $pdf->Ln(6);
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(102, 102, 102);
        $pdf->Cell(180, 5, $this->pdfTexto('Emitida em ' . $dados['dataEmissao'] . ' - INOTU'), 0, 1, 'C');
    }

    private function tituloPdf(\FPDF $pdf, string $titulo): void
    {
        $this->novaPaginaSePreciso($pdf, 18);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(26, 35, 126);
        $pdf->Cell(180, 7, $this->pdfTexto($titulo), 0, 1);
        $pdf->SetTextColor(51, 51, 51);
    }

    private function linhaTabelaPdf(\FPDF $pdf, array $valores, array $larguras, bool $cabecalho = false): void
    {
        $this->novaPaginaSePreciso($pdf, 8);
        $pdf->SetFont('Arial', $cabecalho ? 'B' : '', 8);
        $pdf->SetFillColor($cabecalho ? 245 : 255, $cabecalho ? 245 : 255, $cabecalho ? 245 : 255);

        foreach ($valores as $index => $valor) {
            $alinhamento = $index === count($valores) - 1 ? 'R' : 'L';
            $pdf->Cell($larguras[$index], 7, $this->pdfTexto((string) $valor), 1, 0, $alinhamento, $cabecalho);
        }

        $pdf->Ln();
    }

    private function resumoLinhaPdf(\FPDF $pdf, string $label, int $valor, bool $destaque = false): void
    {
        $this->novaPaginaSePreciso($pdf, 8);
        $pdf->SetFont('Arial', $destaque ? 'B' : '', 9);
        $pdf->Cell(126, 7, $this->pdfTexto($label), 0, 0);
        $pdf->Cell(54, 7, $this->pdfTexto(($valor < 0 ? '(' . $this->formatarKz(abs($valor)) . ')' : $this->formatarKz($valor))), 0, 1, 'R');
    }

    private function novaPaginaSePreciso(\FPDF $pdf, int $altura): void
    {
        if ($pdf->GetY() + $altura > 280) {
            $pdf->AddPage();
        }
    }

    private function formatarKz(int $valor): string
    {
        return number_format($valor, 2, ',', '.') . ' KZA';
    }

    private function pdfTexto(string $texto): string
    {
        return iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $texto) ?: $texto;
    }
}

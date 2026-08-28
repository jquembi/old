<?php

namespace App\Http\Controllers;

use App\Models\InstrumentoFicheiro;
use App\Models\LoteamentoFicheiro;
use App\Models\ProjectoSonaFicheiro;
use App\Models\Parecer;
use App\Models\Croqui;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentoAutenticidadeController extends Controller
{
    /**
     * Verifica autenticidade de um documento via QR token
     * @param string $token Token única do documento
     */
    public function verificar(Request $request, string $token)
    {
        // Procurar em todas as tabelas de ficheiros
        $ficheiro = $this->encontrarFicheiro($token);

        if (!$ficheiro) {
            return $this->responderNaoEncontrado();
        }

        // Marcar como verificado
        if (!$ficheiro->verificado) {
            $ficheiro->marcarComoVerificado();
        }

        // Dados extra para Croqui (proprietário + localização)
        $dadosCroqui = null;
        $dadosParecer = null;
        $pareceresAssociados = collect();
        $contextoDocumento = $this->contextoDocumento($request, $ficheiro);
        $podeVerDetalhesDiretor = $this->podeVerDetalhesDiretor($request);

        if ($ficheiro instanceof Croqui) {
            $ficheiro->load('proprietarios', 'operacao.municipio', 'pareceres.municipio');
            $dadosCroqui = [
                'proprietarios' => $ficheiro->obterNomesProprietarios(),
                'localizacao'   => $ficheiro->obterLocalizacao(),
                'titulo'        => $ficheiro->titulo,
                'municipio'     => $ficheiro->operacao?->municipio?->nome,
                'operacao'      => $ficheiro->operacao?->titulo ?? $ficheiro->operacao?->referencia,
            ];
            $pareceresAssociados = $ficheiro->pareceres->map(fn (Parecer $parecer) => $this->formatarParecer($parecer));
        }

        if ($ficheiro instanceof Parecer) {
            $ficheiro->load('municipio', 'criadoPor', 'recurso');
            $dadosParecer = $this->formatarParecer($ficheiro);
        }

        // Retornar view com detalhes
        return view('documento.verificado', [
            'ficheiro'    => $ficheiro,
            'tipo'        => $this->identificarTipo($ficheiro, $contextoDocumento),
            'dados'       => $this->dadosPublicosQr($ficheiro, $contextoDocumento),
            'dadosCroqui' => $dadosCroqui,
            'dadosParecer' => $dadosParecer,
            'pareceresAssociados' => $pareceresAssociados,
            'estadoVerificacao' => $this->estadoVerificacao($ficheiro),
            'detalhesDiretor' => $podeVerDetalhesDiretor ? $this->detalhesDiretor($ficheiro, $contextoDocumento) : null,
        ]);
    }

    /**
     * Procura o ficheiro em todas as tabelas
     * @param string $token
     * @return mixed
     */
    private function encontrarFicheiro(string $token)
    {
        // Procurar por ordem de probabilidade (mais comuns primeiro)
        $ficheiro = InstrumentoFicheiro::where('qr_token', $token)->first();
        if ($ficheiro) return $ficheiro;

        $ficheiro = LoteamentoFicheiro::where('qr_token', $token)->first();
        if ($ficheiro) return $ficheiro;

        $ficheiro = ProjectoSonaFicheiro::where('qr_token', $token)->first();
        if ($ficheiro) return $ficheiro;

        $ficheiro = Parecer::where('qr_token', $token)->first();
        if ($ficheiro) return $ficheiro;

        $ficheiro = Croqui::where('qr_token', $token)->first();
        if ($ficheiro) return $ficheiro;

        return null;
    }

    /**
     * Identifica o tipo de ficheiro
     * @param $ficheiro
     * @return string
     */
    private function identificarTipo($ficheiro, ?string $contextoDocumento = null): string
    {
        if ($ficheiro instanceof Croqui && $contextoDocumento === 'memoria_descritiva') {
            return 'Memória Descritiva';
        }

        return match (class_basename($ficheiro)) {
            'InstrumentoFicheiro' => 'Instrumento',
            'LoteamentoFicheiro' => 'Loteamento',
            'ProjectoSonaFicheiro' => 'Projecto SONA',
            'Parecer' => 'Parecer',
            'Croqui' => 'Croqui',
            default => 'Documento',
        };
    }

    /**
     * Renderiza resposta "não encontrado"
     */
    private function responderNaoEncontrado()
    {
        return view('documento.nao-encontrado')->with([
            'mensagem' => 'Documento não encontrado ou código QR inválido.',
            'estadoVerificacao' => [
                'tipo' => 'nao_autentico',
                'cor' => 'red',
                'titulo' => 'Documento Não Autêntico',
                'mensagem' => 'O código QR não corresponde a nenhum documento registado no sistema.',
                'rotulo' => 'Não autêntico',
            ],
        ]);
    }

    /**
     * Baixa o ficheiro verificado
     * @param string $token
     * @return mixed
     */
    public function baixar(string $token)
    {
        $ficheiro = $this->encontrarFicheiro($token);

        if (!$ficheiro || !Storage::disk('public')->exists($ficheiro->caminho)) {
            return abort(404, 'Ficheiro não encontrado.');
        }

        // Marcar como verificado
        if (!$ficheiro->verificado) {
            $ficheiro->marcarComoVerificado();
        }

        return Storage::disk('public')->download($ficheiro->caminho, $ficheiro->nome_original);
    }

    /**
     * API JSON para verificação de autenticidade
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificarApi(Request $request, string $token)
    {
        $ficheiro = $this->encontrarFicheiro($token);

        if (!$ficheiro) {
            return response()->json([
                'valido' => false,
                'mensagem' => 'Token inválido ou não encontrado.',
            ], 404);
        }

        // Marcar como verificado
        if (!$ficheiro->verificado) {
            $ficheiro->marcarComoVerificado();
        }

        $contextoDocumento = $this->contextoDocumento($request, $ficheiro);
        $podeVerDetalhesDiretor = $this->podeVerDetalhesDiretor($request);

        $resposta = [
            'valido' => true,
            'estado_verificacao' => $this->estadoVerificacao($ficheiro),
            'tipo' => $this->identificarTipo($ficheiro, $contextoDocumento),
            'dados' => $this->dadosPublicosQr($ficheiro, $contextoDocumento),
            'ficheiro' => [
                'nome' => $ficheiro->nome_original ?? 'Documento',
                'tamanho' => $ficheiro->tamanho ?? null,
                'tamanho_formatado' => $this->formatarTamanho($ficheiro->tamanho ?? 0),
                'tipo' => $ficheiro->tipo_ficheiro ?? $ficheiro->mime_type,
                'criado_em' => $ficheiro->created_at?->toIso8601String(),
                'verificado_em' => $ficheiro->updated_at?->toIso8601String(),
            ],
            'criador' => [
                'nome' => $ficheiro->enviadoPor?->name ?? 'Desconhecido',
                'email' => $ficheiro->enviadoPor?->email ?? null,
            ],
        ];

        // Dados extra para Croqui
        if ($ficheiro instanceof Croqui) {
            $ficheiro->load('proprietarios', 'operacao.municipio', 'pareceres.municipio');
            $resposta['croqui'] = [
                'proprietarios'  => $ficheiro->obterNomesProprietarios(),
                'localizacao'    => $ficheiro->obterLocalizacao(),
                'titulo'         => $ficheiro->titulo,
                'municipio'      => $ficheiro->operacao?->municipio?->nome,
                'operacao'       => $ficheiro->operacao?->titulo ?? $ficheiro->operacao?->referencia,
            ];
            $resposta['pareceres_inotu'] = $ficheiro->pareceres
                ->map(fn (Parecer $parecer) => $this->formatarParecer($parecer))
                ->values();
        }

        if ($ficheiro instanceof Parecer) {
            $ficheiro->load('municipio', 'criadoPor', 'recurso');
            $resposta['parecer_inotu'] = $this->formatarParecer($ficheiro);
        }

        if ($podeVerDetalhesDiretor) {
            $resposta['detalhes_diretor'] = $this->detalhesDiretor($ficheiro, $contextoDocumento);
        }

        return response()->json($resposta);
    }

    /**
     * Formata tamanho de ficheiro
     * @param int $bytes
     * @return string
     */
    private function formatarTamanho(int $bytes): string
    {
        if ($bytes == 0) return '0 B';
        $k = 1024;
        $sizes = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes, $k));
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    private function contextoDocumento(Request $request, $ficheiro): ?string
    {
        $documento = (string) $request->query('documento', '');

        if (
            $ficheiro instanceof Croqui
            && in_array($documento, ['memoria_descritiva', 'memoria'], true)
            && $ficheiro->memoria_descritiva_caminho
        ) {
            return 'memoria_descritiva';
        }

        return null;
    }

    private function dadosPublicosQr($ficheiro, ?string $contextoDocumento = null): array
    {
        $dados = [
            'tipo' => $this->identificarTipo($ficheiro, $contextoDocumento),
            'nome' => $this->nomePublicoDocumento($ficheiro, $contextoDocumento),
            'estado' => $this->estadoVerificacao($ficheiro)['rotulo'] ?? null,
            'criado_em' => $ficheiro->created_at?->toIso8601String(),
            'verificado_em' => $ficheiro->updated_at?->toIso8601String(),
        ];

        if ($ficheiro instanceof Croqui) {
            $dados['municipio'] = $ficheiro->operacao?->municipio?->nome;
            $dados['titulo'] = $ficheiro->titulo;
        }

        return array_filter($dados, fn ($valor) => $valor !== null && $valor !== '');
    }

    private function nomePublicoDocumento($ficheiro, ?string $contextoDocumento = null): string
    {
        if ($ficheiro instanceof Croqui && $contextoDocumento === 'memoria_descritiva') {
            return $ficheiro->memoria_descritiva_nome ?? 'Memória Descritiva';
        }

        return $ficheiro->nome_original
            ?? $ficheiro->ficheiro_nome
            ?? $ficheiro->ficheiro_pdf_nome
            ?? $ficheiro->titulo
            ?? 'Documento';
    }

    private function podeVerDetalhesDiretor(Request $request): bool
    {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        $user->loadMissing('roles');

        return $user->isDiretorInotu();
    }

    private function detalhesDiretor($ficheiro, ?string $contextoDocumento = null): array
    {
        $detalhes = [
            'tipo_contexto' => $this->identificarTipo($ficheiro, $contextoDocumento),
            'id_interno' => $ficheiro->id,
            'token_qr' => $ficheiro->qr_token ?? null,
            'verificado' => (bool) ($ficheiro->verificado ?? false),
            'criado_em' => $ficheiro->created_at?->toIso8601String(),
            'actualizado_em' => $ficheiro->updated_at?->toIso8601String(),
            'acoes_necessarias' => $this->acoesNecessariasDiretor($ficheiro, $contextoDocumento),
        ];

        if ($ficheiro instanceof Croqui) {
            $detalhes += [
                'codigo_territorial' => $ficheiro->codigo_territorial,
                'publicado' => (bool) $ficheiro->publicado,
                'rup_pago' => (bool) $ficheiro->rup_pago,
                'download_liberado' => (bool) $ficheiro->download_liberado,
                'rup_referencia' => $ficheiro->rup_referencia,
                'publicado_em' => $ficheiro->publicado_em?->toIso8601String(),
                'liberado_em' => $ficheiro->liberado_em?->toIso8601String(),
                'visibilidade' => $ficheiro->visibilidade,
                'documento_principal' => $ficheiro->ficheiro_pdf_caminho,
                'memoria_descritiva' => $ficheiro->memoria_descritiva_caminho,
            ];
        }

        if ($ficheiro instanceof Parecer) {
            $detalhes += [
                'estado' => $ficheiro->estado,
                'municipio' => $ficheiro->municipio?->nome,
                'ficheiro' => $ficheiro->ficheiro_caminho,
            ];
        }

        return array_filter($detalhes, fn ($valor) => $valor !== null && $valor !== '');
    }

    private function acoesNecessariasDiretor($ficheiro, ?string $contextoDocumento = null): array
    {
        $acoes = [];

        if ($ficheiro instanceof Croqui) {
            if ($contextoDocumento === 'memoria_descritiva' && ! $ficheiro->temMemoriaDescritivaValida()) {
                $acoes[] = 'Carregar ou corrigir o ficheiro da memória descritiva.';
            }

            if (! $ficheiro->temFicheiroPdfValido()) {
                $acoes[] = 'Carregar ou corrigir o ficheiro PDF principal do croqui.';
            }

            if (! $ficheiro->publicado) {
                $acoes[] = 'Publicar o documento para concluir a validação administrativa.';
            }

            if (! $ficheiro->foiCriadoPorInotu() && ! $ficheiro->rup_pago) {
                $acoes[] = 'Confirmar o pagamento/RUPE do processo.';
            }

            if (! $ficheiro->download_liberado && $ficheiro->podeSerLiberado()) {
                $acoes[] = 'Homologar e liberar o download do documento.';
            }

            if (! $ficheiro->codigo_territorial) {
                $acoes[] = 'Gerar ou confirmar o ID territorial do terreno.';
            }
        }

        if ($ficheiro instanceof Parecer && in_array($ficheiro->estado, ['pendente', 'em_analise'], true)) {
            $acoes[] = 'Concluir a análise e emitir decisão do parecer.';
        }

        return $acoes ?: ['Sem ação administrativa pendente.'];
    }

    private function formatarParecer(Parecer $parecer): array
    {
        return [
            'id' => $parecer->id,
            'titulo' => $parecer->titulo,
            'servico' => $parecer->servico,
            'servico_label' => $parecer->servicoLabel(),
            'municipio' => $parecer->municipio?->nome,
            'entidade_requerente' => $parecer->entidade_requerente,
            'tipo_concessao' => $parecer->tipo_concessao,
            'classificacao_area' => $parecer->classificacao_area,
            'uso_solo' => $parecer->uso_solo,
            'zona_ordenamento' => $parecer->zona_ordenamento,
            'area_reservada' => $parecer->area_reservada,
            'area_reservada_label' => $parecer->areaReservadaLabel(),
            'fundamentacao' => $parecer->fundamentacao,
            'conclusao' => $parecer->conclusao,
            'ficheiro_nome' => $parecer->ficheiro_nome,
            'criado_em' => $parecer->created_at?->toIso8601String(),
        ];
    }

    private function estadoVerificacao($ficheiro): array
    {
        if ($ficheiro instanceof Croqui) {
            if (! $ficheiro->rup_pago) {
                return [
                    'tipo' => 'pagamento_pendente',
                    'cor' => 'blue',
                    'titulo' => 'Documento Autêntico com Pagamento Pendente',
                    'mensagem' => 'O documento existe no sistema, mas o processo ainda tem pagamento pendente.',
                    'rotulo' => 'Pagamento pendente',
                ];
            }

            if (! $ficheiro->download_liberado || ! $ficheiro->publicado) {
                return [
                    'tipo' => 'acao_necessaria',
                    'cor' => 'yellow',
                    'titulo' => 'Documento Autêntico com Ação Necessária',
                    'mensagem' => 'O documento foi reconhecido, mas ainda precisa de uma ação administrativa.',
                    'rotulo' => 'Ação necessária',
                ];
            }
        }

        if ($ficheiro instanceof Parecer && in_array($ficheiro->estado, ['pendente', 'em_analise'], true)) {
            return [
                'tipo' => 'acao_necessaria',
                'cor' => 'yellow',
                'titulo' => 'Parecer Autêntico com Ação Necessária',
                'mensagem' => 'O parecer existe no sistema, mas ainda se encontra pendente ou em análise.',
                'rotulo' => 'Ação necessária',
            ];
        }

        return [
            'tipo' => 'autentico',
            'cor' => 'green',
            'titulo' => 'Documento Autêntico',
            'mensagem' => 'Este documento foi verificado e é original.',
            'rotulo' => 'Autêntico',
        ];
    }
}

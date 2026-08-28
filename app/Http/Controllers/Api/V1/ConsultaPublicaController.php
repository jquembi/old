<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\EstadoPagamento;
use App\Http\Controllers\Controller;
use App\Models\Croqui;
use App\Models\OperacaoUrbanistica;
use App\Models\Pagamento;
use App\Models\Parecer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ConsultaPublicaController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'referencia' => ['nullable', 'string', 'min:3', 'max:120'],
            'bi' => ['nullable', 'string', 'min:3', 'max:50'],
            'publicos' => ['nullable', 'boolean'],
        ], [
            'referencia.min' => 'A referencia deve ter pelo menos 3 caracteres.',
            'bi.min' => 'O numero do bilhete deve ter pelo menos 3 caracteres.',
        ]);

        $referencia = trim((string) ($dados['referencia'] ?? ''));
        $bi = trim((string) ($dados['bi'] ?? ''));
        $listarPublicos = (bool) ($dados['publicos'] ?? false);

        if ($referencia === '' && $bi === '' && ! $listarPublicos) {
            return response()->json([
                'success' => false,
                'message' => 'Informe uma referencia, o numero do bilhete de identidade ou solicite os servicos publicos.',
            ], 422);
        }

        $resultados = collect();

        if ($bi !== '') {
            $resultados = $resultados->merge($this->consultarPorBilhete($bi));
        }

        if ($referencia !== '') {
            $resultados = $resultados
                ->merge($this->consultarPagamentos($referencia))
                ->merge($this->consultarCroquis($referencia, publico: true))
                ->merge($this->consultarPareceres($referencia, publico: true))
                ->merge($this->consultarOperacoes($referencia, publico: true));
        }

        if ($listarPublicos) {
            $resultados = $resultados->merge($this->consultarServicosPublicos($referencia));
        }

        $resultados = $resultados
            ->unique(fn (array $item) => $item['tipo'].'-'.$item['id'])
            ->values()
            ->take(20);

        return response()->json([
            'success' => true,
            'encontrado' => $resultados->isNotEmpty(),
            'referencia' => $referencia,
            'bi' => $bi !== '' ? $this->mascararBilhete($bi) : null,
            'total' => $resultados->count(),
            'resumo' => $this->resumoPortal(),
            'resultados' => $resultados,
            'mensagem' => $resultados->isEmpty()
                ? 'Nenhuma solicitacao foi encontrada para a referencia informada.'
                : 'Consulta realizada com sucesso.',
        ], $resultados->isEmpty() ? 404 : 200);
    }

    private function consultarPagamentos(string $referencia): Collection
    {
        return Pagamento::query()
            ->with(['municipio', 'croqui.operacao.municipio', 'croqui.proprietarios'])
            ->where(function (Builder $query) use ($referencia) {
                $query->where('rupe_referencia', $referencia);

                if (ctype_digit($referencia)) {
                    $query->orWhere('id', (int) $referencia);
                }
            })
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Pagamento $pagamento) => $this->formatarPagamento($pagamento));
    }

    private function consultarPorBilhete(string $bi): Collection
    {
        return Croqui::query()
            ->with(['operacao.municipio', 'proprietarios', 'pagamentoActivo', 'pareceres.municipio'])
            ->whereHas('proprietarios', fn (Builder $query) => $query->where('bi', $bi))
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (Croqui $croqui) => $this->formatarCroqui($croqui));
    }

    private function consultarServicosPublicos(string $referencia = ''): Collection
    {
        return collect()
            ->merge($this->consultarCroquis($referencia, publico: true, listar: true))
            ->merge($this->consultarPareceres($referencia, publico: true, listar: true))
            ->merge($this->consultarOperacoes($referencia, publico: true, listar: true));
    }

    private function consultarCroquis(string $referencia, bool $publico = false, bool $listar = false): Collection
    {
        return Croqui::query()
            ->with(['operacao.municipio', 'proprietarios', 'pagamentoActivo', 'pareceres.municipio'])
            ->when($publico, fn (Builder $query) => $query->where('visibilidade', 'publico'))
            ->when(! $listar, function (Builder $base) use ($referencia) {
                $base->where(function (Builder $query) use ($referencia) {
                $query
                    ->where('qr_token', $referencia)
                    ->orWhere('slug', Str::slug($referencia))
                    ->orWhere('rup_referencia', $referencia)
                    ->orWhereHas('proprietarios', function (Builder $proprietarios) use ($referencia) {
                        $proprietarios
                            ->where('bi', $referencia)
                            ->orWhere('contacto', $referencia);
                    });

                if (ctype_digit($referencia)) {
                    $query->orWhere('id', (int) $referencia);
                }
                });
            })
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Croqui $croqui) => $this->formatarCroqui($croqui));
    }

    private function consultarPareceres(string $referencia, bool $publico = false, bool $listar = false): Collection
    {
        return Parecer::query()
            ->with(['municipio'])
            ->when($publico, fn (Builder $query) => $query->where('visibilidade', 'publico'))
            ->when(! $listar, function (Builder $base) use ($referencia) {
                $base->where(function (Builder $query) use ($referencia) {
                $query
                    ->where('qr_token', $referencia)
                    ->orWhere('slug', Str::slug($referencia))
                    ->orWhere('entidade_requerente', $referencia)
                    ->orWhere('projecto_nome', $referencia);

                if (ctype_digit($referencia)) {
                    $query->orWhere('id', (int) $referencia);
                }
                });
            })
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Parecer $parecer) => $this->formatarParecer($parecer));
    }

    private function consultarOperacoes(string $referencia, bool $publico = false, bool $listar = false): Collection
    {
        return OperacaoUrbanistica::query()
            ->withCount(['croquis', 'pareceres'])
            ->with('municipio')
            ->when($publico, fn (Builder $query) => $query->where('visibilidade', 'publico'))
            ->when(! $listar, function (Builder $base) use ($referencia) {
                $base->where(function (Builder $query) use ($referencia) {
                if (ctype_digit($referencia)) {
                    $query->where('id', (int) $referencia);
                } else {
                    $query->where('titulo', $referencia);
                }
                });
            })
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn (OperacaoUrbanistica $operacao) => [
                'tipo' => 'operacao',
                'id' => $operacao->id,
                'referencia' => 'OP-'.$operacao->id,
                'titulo' => $operacao->titulo,
                'servico' => 'Operacao urbanistica',
                'municipio' => $operacao->municipio?->nome,
                'estado' => [
                    'codigo' => 'registada',
                    'rotulo' => 'Registada',
                    'cor' => 'blue',
                    'descricao' => 'A operacao existe no sistema e pode possuir croquis, pareceres ou ficheiros associados.',
                ],
                'pagamento' => null,
                'proximo_passo' => 'Acompanhe os documentos associados pela referencia do croqui, parecer, RUPE, BI ou contacto do requerente.',
                'resumo' => [
                    'croquis' => $operacao->croquis_count,
                    'pareceres' => $operacao->pareceres_count,
                ],
                'detalhes' => [
                    'Tipo' => 'Operacao urbanistica',
                    'Municipio' => $operacao->municipio?->nome,
                    'Croquis associados' => $operacao->croquis_count,
                    'Pareceres associados' => $operacao->pareceres_count,
                ],
                'linha_tempo' => $this->linhaTempo('registada'),
                'actualizado_em' => $operacao->updated_at?->toIso8601String(),
                'actualizado_em_formatado' => $operacao->updated_at?->format('d/m/Y H:i'),
            ]);
    }

    private function formatarCroqui(Croqui $croqui): array
    {
        $pagamento = $croqui->pagamentoActivo;

        return [
            'tipo' => $croqui->tipo === 'terreno_ocioso' ? 'terreno_ocioso' : 'croqui',
            'id' => $croqui->id,
            'referencia' => 'CR-'.$croqui->id,
            'titulo' => $croqui->titulo,
            'servico' => $croqui->tipo === 'terreno_ocioso' ? 'Terreno ocioso' : 'Croqui de localizacao',
            'municipio' => $croqui->operacao?->municipio?->nome,
            'estado' => $this->estadoCroqui($croqui),
            'pagamento' => $pagamento ? $this->resumoPagamento($pagamento) : [
                'estado' => $croqui->rup_pago ? 'pago' : 'nao_registado',
                'rotulo' => $croqui->rup_pago ? 'Pago' : 'Sem pagamento registado',
                'referencia' => $croqui->rup_referencia,
            ],
            'pareceres' => $croqui->pareceres->map(fn (Parecer $parecer) => [
                'id' => $parecer->id,
                'titulo' => $parecer->titulo,
                'estado' => $this->estadoTexto($parecer->estado),
                'area_reservada' => $parecer->areaReservadaLabel(),
            ])->values(),
            'proximo_passo' => $this->proximoPassoCroqui($croqui, $pagamento),
            'detalhes' => [
                'Referencia' => 'CR-'.$croqui->id,
                'Tipo' => $croqui->tipo === 'terreno_ocioso' ? 'Terreno ocioso' : 'Croqui de localizacao',
                'Municipio' => $croqui->operacao?->municipio?->nome,
                'Proprietarios' => $croqui->obterNomesProprietarios(),
                'Localizacao' => $croqui->obterLocalizacao(),
                'Ano' => $croqui->ano,
                'Publicado' => $croqui->publicado ? 'Sim' : 'Nao',
                'Download liberado' => $croqui->download_liberado ? 'Sim' : 'Nao',
            ],
            'linha_tempo' => $this->linhaTempo($this->estadoCroqui($croqui)['codigo']),
            'verificacao_url' => $croqui->qr_token ? route('documento.verificar', $croqui->qr_token) : null,
            'actualizado_em' => $croqui->updated_at?->toIso8601String(),
            'actualizado_em_formatado' => $croqui->updated_at?->format('d/m/Y H:i'),
        ];
    }

    private function formatarParecer(Parecer $parecer): array
    {
        return [
            'tipo' => 'parecer',
            'id' => $parecer->id,
            'referencia' => 'PA-'.$parecer->id,
            'titulo' => $parecer->titulo,
            'servico' => $parecer->servicoLabel(),
            'municipio' => $parecer->municipio?->nome,
            'estado' => $this->estadoParecer($parecer),
            'pagamento' => null,
            'proximo_passo' => $this->proximoPassoParecer($parecer),
            'resumo' => [
                'entidade_requerente' => $parecer->entidade_requerente,
                'projecto' => $parecer->projecto_nome,
                'area_reservada' => $parecer->areaReservadaLabel(),
                'uso_solo' => $parecer->uso_solo,
            ],
            'detalhes' => [
                'Referencia' => 'PA-'.$parecer->id,
                'Servico' => $parecer->servicoLabel(),
                'Municipio' => $parecer->municipio?->nome,
                'Entidade requerente' => $parecer->entidade_requerente,
                'Projecto/Recurso' => $parecer->projecto_nome,
                'Area reservada' => $parecer->areaReservadaLabel(),
                'Uso do solo' => $parecer->uso_solo,
                'Zona de ordenamento' => $parecer->zona_ordenamento,
                'Conclusao' => $parecer->conclusao,
            ],
            'linha_tempo' => $this->linhaTempo($parecer->estado ?: 'pendente'),
            'verificacao_url' => $parecer->qr_token ? route('documento.verificar', $parecer->qr_token) : null,
            'actualizado_em' => $parecer->updated_at?->toIso8601String(),
            'actualizado_em_formatado' => $parecer->updated_at?->format('d/m/Y H:i'),
        ];
    }

    private function formatarPagamento(Pagamento $pagamento): array
    {
        $croqui = $pagamento->croqui;

        return [
            'tipo' => 'pagamento',
            'id' => $pagamento->id,
            'referencia' => $pagamento->rupe_referencia ?: 'PG-'.$pagamento->id,
            'titulo' => $croqui?->titulo ?? 'Pagamento de servico territorial',
            'servico' => 'Pagamento RUPE/RUP',
            'municipio' => $pagamento->municipio?->nome ?? $croqui?->operacao?->municipio?->nome,
            'estado' => [
                'codigo' => $pagamento->estado?->value,
                'rotulo' => $pagamento->estado?->label() ?? 'Pagamento',
                'cor' => $pagamento->estado?->cor() ?? 'blue',
                'descricao' => 'Estado actual do pagamento associado a solicitacao.',
            ],
            'pagamento' => $this->resumoPagamento($pagamento),
            'proximo_passo' => $this->proximoPassoPagamento($pagamento),
            'detalhes' => [
                'Referencia' => $pagamento->rupe_referencia ?: 'PG-'.$pagamento->id,
                'Entidade' => $pagamento->rupe_entidade,
                'Valor' => $pagamento->valor !== null ? number_format((float) $pagamento->valor, 2, ',', '.').' Kz' : null,
                'Data limite' => $pagamento->rupe_data_limite?->format('d/m/Y'),
                'Comprovativo submetido' => $pagamento->comprovativo_path ? 'Sim' : 'Nao',
                'Processo' => $croqui?->titulo,
            ],
            'linha_tempo' => $this->linhaTempo($pagamento->estado?->value),
            'actualizado_em' => $pagamento->updated_at?->toIso8601String(),
            'actualizado_em_formatado' => $pagamento->updated_at?->format('d/m/Y H:i'),
        ];
    }

    private function estadoCroqui(Croqui $croqui): array
    {
        if (! $croqui->ficheiro_pdf_caminho) {
            return $this->estado('em_preparacao', 'Em preparacao', 'yellow', 'A solicitacao existe, mas ainda nao possui o documento final associado.');
        }

        if (! $croqui->publicado) {
            return $this->estado('em_analise', 'Em analise', 'yellow', 'O documento foi registado e aguarda publicacao administrativa.');
        }

        if (! $croqui->rup_pago) {
            return $this->estado('pagamento_pendente', 'Pagamento pendente', 'blue', 'A solicitacao aguarda pagamento para prosseguir.');
        }

        if (! $croqui->download_liberado) {
            return $this->estado('homologacao_pendente', 'Homologacao pendente', 'yellow', 'O pagamento foi registado e aguarda liberacao/homologacao.');
        }

        return $this->estado('concluido', 'Concluido', 'green', 'A solicitacao encontra-se concluida e liberada.');
    }

    private function estadoParecer(Parecer $parecer): array
    {
        return match ($parecer->estado) {
            'aprovado', 'concluido' => $this->estado($parecer->estado, $this->estadoTexto($parecer->estado), 'green', 'O parecer foi concluido pela entidade competente.'),
            'rejeitado' => $this->estado('rejeitado', 'Rejeitado', 'red', 'O parecer foi rejeitado ou indeferido.'),
            'em_analise' => $this->estado('em_analise', 'Em analise', 'yellow', 'O parecer esta em analise tecnica.'),
            default => $this->estado($parecer->estado ?: 'pendente', $this->estadoTexto($parecer->estado ?: 'pendente'), 'blue', 'O parecer foi registado e aguarda tratamento.'),
        };
    }

    private function resumoPagamento(Pagamento $pagamento): array
    {
        return [
            'estado' => $pagamento->estado?->value,
            'rotulo' => $pagamento->estado?->label(),
            'referencia' => $pagamento->rupe_referencia,
            'entidade' => $pagamento->rupe_entidade,
            'valor' => $pagamento->valor !== null ? (float) $pagamento->valor : null,
            'valor_formatado' => $pagamento->valor !== null ? number_format((float) $pagamento->valor, 2, ',', '.').' Kz' : null,
            'data_limite' => $pagamento->rupe_data_limite?->toDateString(),
            'comprovativo_submetido' => (bool) $pagamento->comprovativo_path,
        ];
    }

    private function proximoPassoCroqui(Croqui $croqui, ?Pagamento $pagamento): string
    {
        if (! $croqui->ficheiro_pdf_caminho) {
            return 'Aguarde a anexacao do documento tecnico final pelo municipio ou INOTU.';
        }

        if (! $croqui->publicado) {
            return 'Aguarde a publicacao administrativa da solicitacao.';
        }

        if ($pagamento) {
            return $this->proximoPassoPagamento($pagamento);
        }

        if (! $croqui->rup_pago) {
            return 'Aguarde a geracao ou o registo da referencia de pagamento.';
        }

        if (! $croqui->download_liberado) {
            return 'Aguarde a homologacao e liberacao final do documento.';
        }

        return 'Processo concluido. Contacte o municipio caso necessite de uma segunda via.';
    }

    private function proximoPassoParecer(Parecer $parecer): string
    {
        return match ($parecer->estado) {
            'aprovado', 'concluido' => 'Parecer concluido. Contacte a instituicao responsavel para levantamento ou acesso ao documento.',
            'rejeitado' => 'Contacte a instituicao responsavel para conhecer as razoes e orientacoes de correcao.',
            'em_analise' => 'Aguarde a conclusao da analise tecnica.',
            default => 'Aguarde a distribuicao e analise do parecer.',
        };
    }

    private function proximoPassoPagamento(Pagamento $pagamento): string
    {
        return match ($pagamento->estado) {
            EstadoPagamento::AguardandoPagamento => 'Aguarde a geracao da referencia RUPE.',
            EstadoPagamento::RupeGerado => 'Efectue o pagamento e submeta o comprovativo junto da entidade responsavel.',
            EstadoPagamento::ComproativoSubmetido => 'Aguarde a confirmacao do comprovativo pelo INOTU.',
            EstadoPagamento::PagamentoRejeitado => 'Submeta um novo comprovativo ou contacte a entidade responsavel.',
            EstadoPagamento::Pago, EstadoPagamento::Concluido => 'Pagamento confirmado. Aguarde a liberacao final do documento quando aplicavel.',
            default => 'Acompanhe o estado junto da entidade responsavel.',
        };
    }

    private function estado(string $codigo, string $rotulo, string $cor, string $descricao): array
    {
        return compact('codigo', 'rotulo', 'cor', 'descricao');
    }

    private function estadoTexto(?string $estado): string
    {
        return Str::of($estado ?: 'pendente')->replace('_', ' ')->title()->toString();
    }

    private function resumoPortal(): array
    {
        return [
            'servicos_publicos' => [
                'croquis' => Croqui::where('visibilidade', 'publico')->count(),
                'pareceres' => Parecer::where('visibilidade', 'publico')->count(),
                'operacoes' => OperacaoUrbanistica::where('visibilidade', 'publico')->count(),
            ],
            'pagamentos_pendentes' => Pagamento::whereIn('estado', [
                EstadoPagamento::AguardandoPagamento->value,
                EstadoPagamento::RupeGerado->value,
                EstadoPagamento::PagamentoRejeitado->value,
            ])->count(),
            'actualizado_em' => now()->toIso8601String(),
        ];
    }

    private function linhaTempo(?string $estadoActual): array
    {
        $passos = [
            ['codigo' => 'registada', 'rotulo' => 'Registada'],
            ['codigo' => 'em_analise', 'rotulo' => 'Em analise'],
            ['codigo' => 'pagamento_pendente', 'rotulo' => 'Pagamento'],
            ['codigo' => 'homologacao_pendente', 'rotulo' => 'Homologacao'],
            ['codigo' => 'concluido', 'rotulo' => 'Concluido'],
        ];

        $indiceActual = match ($estadoActual) {
            'registada', 'pendente', 'aguardando_pagamento' => 0,
            'em_preparacao', 'em_analise' => 1,
            'pagamento_pendente', 'rupe_gerado', 'pagamento_rejeitado' => 2,
            'homologacao_pendente', 'comprovativo_submetido' => 3,
            'aprovado', 'pago', 'concluido' => 4,
            'rejeitado' => 1,
            default => 0,
        };

        return collect($passos)->map(function (array $passo, int $indice) use ($indiceActual, $estadoActual) {
            return [
                ...$passo,
                'estado' => $estadoActual === 'rejeitado'
                    ? ($indice <= $indiceActual ? 'alerta' : 'pendente')
                    : ($indice < $indiceActual ? 'feito' : ($indice === $indiceActual ? 'actual' : 'pendente')),
            ];
        })->all();
    }

    private function mascararBilhete(string $bi): string
    {
        return Str::mask($bi, '*', 3, max(strlen($bi) - 5, 0));
    }
}

<?php

namespace App\Support;

use App\Models\ArquivoBase;
use App\Models\CartaMunicipal;
use App\Models\Contrato;
use App\Models\Fornecedor;
use App\Models\Funcionario;
use App\Models\Instrumento;
use App\Models\InstrumentoOrdenamento;
use App\Models\InstrumentoUrbanistico;
use App\Models\Lancamento;
use App\Models\Localizacao;
use App\Models\Loteamento;
use App\Models\Municipio;
use App\Models\Operacao;
use App\Models\OperacaoUrbanistica;
use App\Models\OutroProjecto;
use App\Models\Pagamento;
use App\Models\PagamentoErp;
use App\Models\PagamentoFinanceiro;
use App\Models\Parecer;
use App\Models\Patrimonio;
use App\Models\ProjectoSona;
use App\Models\Provincia;
use App\Models\RequisicaoCompra;
use App\Models\SonaPlano;
use App\Models\TerrenoOcioso;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Âmbito fixado na instalação. O núcleo vê o país. Uma província só lê
 * e grava dados dos seus municípios.
 */
class AmbitoInstalacao
{
    private static ?string $codigoCache = null;

    private static ?int $provinciaCache = null;

    public static function eNucleo(): bool
    {
        return ! self::eProvincia();
    }

    public static function eProvincia(): bool
    {
        return config('tenancy.deployment_mode') === 'node' && self::codigo() !== null;
    }

    public static function codigo(): ?string
    {
        $codigo = strtoupper(trim((string) config('tenancy.node.code')));

        return preg_match('/^[A-Z]{3}$/', $codigo) === 1 && $codigo !== 'CTR'
            ? $codigo
            : null;
    }

    public static function provinciaId(): ?int
    {
        $codigo = self::codigo();

        if ($codigo === null || ! self::eProvincia()) {
            return null;
        }

        if (self::$codigoCache === $codigo && self::$provinciaCache) {
            return self::$provinciaCache;
        }

        try {
            $id = Provincia::withoutGlobalScopes()->where('codigo', $codigo)->value('id');
        } catch (\Throwable) {
            return null;
        }

        if (! $id) {
            return null;
        }

        self::$codigoCache = $codigo;
        self::$provinciaCache = (int) $id;

        return self::$provinciaCache;
    }

    public static function restringirConsultas(): void
    {
        self::restringir(Provincia::class, function (Builder $query): void {
            $id = self::provinciaId();
            if ($id) {
                $query->where($query->getModel()->getTable().'.id', $id);
            }
        });

        self::restringir(Municipio::class, function (Builder $query): void {
            $id = self::provinciaId();
            if ($id) {
                $query->where($query->getModel()->getTable().'.provincia_id', $id);
            }
        });

        self::restringir(User::class, function (Builder $query): void {
            $id = self::provinciaId();
            if ($id) {
                $tabela = $query->getModel()->getTable();
                $query->where(function (Builder $filtro) use ($tabela, $id) {
                    $filtro->where($tabela.'.provincia_id', $id)->orWhereNull($tabela.'.provincia_id');
                });
            }
        });

        foreach (self::modelosMunicipais() as $modelo) {
            self::restringir($modelo, function (Builder $query): void {
                $id = self::provinciaId();
                if (! $id) {
                    return;
                }

                $tabela = $query->getModel()->getTable();
                $query->whereIn($tabela.'.municipio_id', function ($sub) use ($id) {
                    $sub->select('id')->from('municipios')->where('provincia_id', $id);
                });
            });
        }
    }

    /**
     * @param  class-string<Model>  $modelo
     */
    private static function restringir(string $modelo, \Closure $scope): void
    {
        $modelo::addGlobalScope('ambito-instalacao', $scope);
    }

    /**
     * @return list<class-string<Model>>
     */
    private static function modelosMunicipais(): array
    {
        return [
            ArquivoBase::class,
            CartaMunicipal::class,
            Contrato::class,
            Fornecedor::class,
            Funcionario::class,
            Instrumento::class,
            InstrumentoOrdenamento::class,
            InstrumentoUrbanistico::class,
            Lancamento::class,
            Localizacao::class,
            Loteamento::class,
            Operacao::class,
            OperacaoUrbanistica::class,
            OutroProjecto::class,
            Pagamento::class,
            PagamentoErp::class,
            PagamentoFinanceiro::class,
            Parecer::class,
            Patrimonio::class,
            ProjectoSona::class,
            RequisicaoCompra::class,
            SonaPlano::class,
            TerrenoOcioso::class,
        ];
    }
}

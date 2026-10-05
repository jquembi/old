<?php

namespace App\Services;

use App\Models\Provincia;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class PerspetivaService
{
    public const MODO_GLOBAL = 'global';
    public const MODO_NACIONAL = 'nacional';
    public const MODO_PROVINCIAL = 'provincial';

    public const SESSION_MODO = 'superadmin_perspetiva_modo';
    public const SESSION_PROVINCIA_ID = 'superadmin_perspetiva_provincia_id';

    /**
     * Verifica se o utilizador é Superadmin com acesso a alternar perspetivas.
     */
    public function isSuperAdmin(?User $user = null): bool
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            return false;
        }

        return (bool) $user->isSuperAdminNacional();
    }

    /**
     * Retorna o modo de perspetiva atual ('global', 'nacional', 'provincial').
     */
    public function getModo(): string
    {
        if (! $this->isSuperAdmin()) {
            return self::MODO_GLOBAL;
        }

        $modo = (string) Session::get(self::SESSION_MODO, self::MODO_GLOBAL);

        if (! in_array($modo, [self::MODO_GLOBAL, self::MODO_NACIONAL, self::MODO_PROVINCIAL], true)) {
            return self::MODO_GLOBAL;
        }

        return $modo;
    }

    /**
     * Retorna o ID da província ativa no modo provincial.
     */
    public function getProvinciaId(): ?int
    {
        if (! $this->isSuperAdmin() || $this->getModo() !== self::MODO_PROVINCIAL) {
            return null;
        }

        $provinciaId = Session::get(self::SESSION_PROVINCIA_ID);

        if ($provinciaId) {
            // Verificar se ainda existe e está ativa
            $existe = Provincia::where('id', $provinciaId)->where('activo', true)->exists();
            if ($existe) {
                return (int) $provinciaId;
            }
        }

        // Fallback: primeira província ativa disponível
        $primeira = Provincia::where('activo', true)->orderBy('nome')->first();
        if ($primeira) {
            Session::put(self::SESSION_PROVINCIA_ID, $primeira->id);
            return (int) $primeira->id;
        }

        return null;
    }

    /**
     * Retorna o modelo Eloquent da província ativa (se em modo provincial).
     */
    public function getProvincia(): ?Provincia
    {
        $id = $this->getProvinciaId();

        if (! $id) {
            return null;
        }

        return Provincia::find($id);
    }

    /**
     * Define o modo de perspetiva e, opcionalmente, a província selecionada.
     */
    public function definirModo(string $modo, ?int $provinciaId = null): void
    {
        if (! $this->isSuperAdmin()) {
            return;
        }

        if (! in_array($modo, [self::MODO_GLOBAL, self::MODO_NACIONAL, self::MODO_PROVINCIAL], true)) {
            $modo = self::MODO_GLOBAL;
        }

        Session::put(self::SESSION_MODO, $modo);

        if ($modo === self::MODO_PROVINCIAL) {
            if ($provinciaId) {
                $valida = Provincia::where('id', $provinciaId)->where('activo', true)->exists();
                if ($valida) {
                    Session::put(self::SESSION_PROVINCIA_ID, $provinciaId);
                    return;
                }
            }

            // Se nenhum ID válido fornecido, obter a primeira ou manter a anterior se válida
            $anterior = Session::get(self::SESSION_PROVINCIA_ID);
            if (! $anterior || ! Provincia::where('id', $anterior)->where('activo', true)->exists()) {
                $primeira = Provincia::where('activo', true)->orderBy('nome')->first();
                if ($primeira) {
                    Session::put(self::SESSION_PROVINCIA_ID, $primeira->id);
                }
            }
        }
    }

    /**
     * Verifica se a perspetiva ativa é Global.
     */
    public function isGlobal(): bool
    {
        return $this->getModo() === self::MODO_GLOBAL;
    }

    /**
     * Verifica se a perspetiva ativa é Nacional.
     */
    public function isNacional(): bool
    {
        return $this->getModo() === self::MODO_NACIONAL;
    }

    /**
     * Verifica se a perspetiva ativa é Provincial.
     */
    public function isProvincial(): bool
    {
        return $this->getModo() === self::MODO_PROVINCIAL;
    }

    /**
     * Retorna a lista de todas as províncias ativas para seleção.
     */
    public function obterProvincias(): Collection
    {
        return Provincia::where('activo', true)->orderBy('nome')->get();
    }

    /**
     * Retorna a etiqueta descritiva do modo ativo.
     */
    public function label(): string
    {
        return match ($this->getModo()) {
            self::MODO_NACIONAL => 'Visão Nacional',
            self::MODO_PROVINCIAL => 'Província: ' . ($this->getProvincia()?->nome ?? 'Selecionar'),
            default => 'Visão Global',
        };
    }

    /**
     * Retorna o ícone associado ao modo.
     */
    public function icone(): string
    {
        return match ($this->getModo()) {
            self::MODO_NACIONAL => 'building-library',
            self::MODO_PROVINCIAL => 'map-pin',
            default => 'globe-alt',
        };
    }

    /**
     * Limpa as configurações de perspetiva na sessão.
     */
    public function limpar(): void
    {
        Session::forget([self::SESSION_MODO, self::SESSION_PROVINCIA_ID]);
    }
}

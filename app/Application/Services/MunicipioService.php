<?php

namespace App\Application\Services;

use App\Models\Municipio;
use App\Domain\Municipios\DTOs\CreateMunicipioDTO;
use App\Domain\Municipios\DTOs\UpdateMunicipioDTO;
use App\Domain\Municipios\DTOs\ListMunicipiosDTO;
use App\Domain\Municipios\Actions\CreateMunicipioAction;
use App\Domain\Municipios\Actions\UpdateMunicipioAction;
use App\Domain\Municipios\Actions\DeleteMunicipioAction;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Service que orquestra as operações de Município.
 * 
 * Coordena Actions e fornece interface simples para componentes e controladores.
 */
class MunicipioService
{
    public function __construct(
        private CreateMunicipioAction $createAction,
        private UpdateMunicipioAction $updateAction,
        private DeleteMunicipioAction $deleteAction,
    ) {}

    /**
     * Lista municípios com filtros e paginação.
     */
    public function list(ListMunicipiosDTO $filters): LengthAwarePaginator
    {
        $query = Municipio::query();

        // Busca por texto
        if ($filters->search) {
            $query->where('nome', 'like', "%{$filters->search}%")
                  ->orWhere('sede', 'like', "%{$filters->search}%");
        }

        // Ordenação
        $query->orderBy($filters->sortBy, $filters->sortDirection);

        // Paginação
        return $query->paginate($filters->perPage, ['*'], 'page', $filters->page);
    }

    /**
     * Obter um município por ID.
     */
    public function getById(int $id): ?Municipio
    {
        return Municipio::find($id);
    }

    /**
     * Criar um novo município.
     */
    public function create(CreateMunicipioDTO $dto): Municipio
    {
        return $this->createAction->execute($dto);
    }

    /**
     * Atualizar um município.
     */
    public function update(UpdateMunicipioDTO $dto): Municipio
    {
        return $this->updateAction->execute($dto);
    }

    /**
     * Deletar um município.
     */
    public function delete(int $id): void
    {
        $this->deleteAction->execute($id);
    }
}

<?php

namespace App\Domain\Municipios\DTOs;

/**
 * DTO para listar Municípios com filtros e paginação.
 */
final class ListMunicipiosDTO
{
    public function __construct(
        public readonly int $page = 1,
        public readonly int $perPage = 15,
        public readonly ?string $search = null,
        public readonly string $sortBy = 'nome',
        public readonly string $sortDirection = 'asc',
    ) {}

    /**
     * Cria um DTO a partir de um array de dados.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            page: (int)($data['page'] ?? 1),
            perPage: min((int)($data['per_page'] ?? 15), 100),
            search: $data['search'] ?? null,
            sortBy: $data['sort_by'] ?? 'nome',
            sortDirection: strtolower($data['sort_direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc',
        );
    }

    /**
     * Converte para array.
     */
    public function toArray(): array
    {
        return [
            'page' => $this->page,
            'per_page' => $this->perPage,
            'search' => $this->search,
            'sort_by' => $this->sortBy,
            'sort_direction' => $this->sortDirection,
        ];
    }
}

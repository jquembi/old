<?php

namespace App\Domain\Municipios\DTOs;

/**
 * DTO para atualização de um Município.
 */
final class UpdateMunicipioDTO
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $nome = null,
        public readonly ?string $sede = null,
        public readonly ?string $descricao = null,
        public readonly ?int $populacao_est = null,
        public readonly ?string $latitude = null,
        public readonly ?string $longitude = null,
    ) {}

    /**
     * Converte o DTO para array, excluindo valores null.
     */
    public function toArray(): array
    {
        return array_filter([
            'nome' => $this->nome,
            'sede' => $this->sede,
            'descricao' => $this->descricao,
            'populacao_est' => $this->populacao_est,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ], fn($value) => $value !== null);
    }

    /**
     * Cria um DTO a partir de um array de dados.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            nome: $data['nome'] ?? null,
            sede: $data['sede'] ?? null,
            descricao: $data['descricao'] ?? null,
            populacao_est: $data['populacao_est'] ?? null,
            latitude: $data['latitude'] ?? null,
            longitude: $data['longitude'] ?? null,
        );
    }
}

<?php

namespace App\Domain\Municipios\DTOs;

/**
 * DTO para criação de um novo Município.
 * 
 * Representa os dados necessários para criar um município,
 * com tipagem forte e imutabilidade.
 */
final class CreateMunicipioDTO
{
    public function __construct(
        public readonly string $nome,
        public readonly string $sede,
        public readonly ?string $descricao = null,
        public readonly ?int $populacao_est = null,
        public readonly ?string $latitude = null,
        public readonly ?string $longitude = null,
    ) {}

    /**
     * Converte o DTO para array.
     */
    public function toArray(): array
    {
        return [
            'nome' => $this->nome,
            'sede' => $this->sede,
            'descricao' => $this->descricao,
            'populacao_est' => $this->populacao_est,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    /**
     * Cria um DTO a partir de um array de dados.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            nome: $data['nome'] ?? '',
            sede: $data['sede'] ?? '',
            descricao: $data['descricao'] ?? null,
            populacao_est: $data['populacao_est'] ?? null,
            latitude: $data['latitude'] ?? null,
            longitude: $data['longitude'] ?? null,
        );
    }
}

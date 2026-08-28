<?php

namespace App\Domain\Municipios\Actions;

use App\Models\Municipio;
use App\Domain\Municipios\DTOs\CreateMunicipioDTO;
use App\Domain\Municipios\Events\MunicipioCreatedEvent;
use App\Domain\Municipios\Exceptions\MunicipioAlreadyExistsException;

/**
 * Action para criar um novo município.
 * 
 * Responsável por orquestrar a criação de um município,
 * validações de negócio e disparo de eventos.
 */
final class CreateMunicipioAction
{
    /**
     * Executa a  criação de um novo município.
     * 
     * @throws MunicipioAlreadyExistsException Se já existe um município com o mesmo nome
     */
    public function execute(CreateMunicipioDTO $dto): Municipio
    {
        // Validação de negócio: verifica se já existe
        $exists = Municipio::where('nome', $dto->nome)->exists();
        if ($exists) {
            throw new MunicipioAlreadyExistsException($dto->nome);
        }

        // Criar o município
        $municipio = Municipio::create($dto->toArray());

        // Disparar evento de criação
        event(new MunicipioCreatedEvent($municipio));

        return $municipio;
    }
}

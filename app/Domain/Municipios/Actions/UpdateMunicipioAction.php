<?php

namespace App\Domain\Municipios\Actions;

use App\Models\Municipio;
use App\Domain\Municipios\DTOs\UpdateMunicipioDTO;
use App\Domain\Municipios\Events\MunicipioUpdatedEvent;
use App\Domain\Municipios\Exceptions\MunicipioNotFoundException;

/**
 * Action para atualizar um município.
 */
final class UpdateMunicipioAction
{
    /**
     * Executa a atualização de um município.
     * 
     * @throws MunicipioNotFoundException Se o município não existe
     */
    public function execute(UpdateMunicipioDTO $dto): Municipio
    {
        // Buscar o município
        $municipio = Municipio::find($dto->id);
        if (!$municipio) {
            throw new MunicipioNotFoundException($dto->id);
        }

        // Guardar valores antigos para auditoria
        $oldValues = $municipio->toArray();

        // Atualizar o município
        $municipio->update($dto->toArray());

        // Disparar evento de atualização
        event(new MunicipioUpdatedEvent($municipio, $oldValues));

        return $municipio;
    }
}

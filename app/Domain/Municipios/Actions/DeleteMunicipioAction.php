<?php

namespace App\Domain\Municipios\Actions;

use App\Models\Municipio;
use App\Domain\Municipios\Events\MunicipioDeletedEvent;
use App\Domain\Municipios\Exceptions\MunicipioNotFoundException;

/**
 * Action para deletar um município.
 */
final class DeleteMunicipioAction
{
    /**
     * Executa a deleção de um município.
     * 
     * @throws MunicipioNotFoundException Se o município não existe
     */
    public function execute(int $municipioId): void
    {
        // Buscar o município
        $municipio = Municipio::find($municipioId);
        if (!$municipio) {
            throw new MunicipioNotFoundException($municipioId);
        }

        // Disparar evento antes de deletar
        event(new MunicipioDeletedEvent($municipio));

        // Deletar
        $municipio->delete();
    }
}

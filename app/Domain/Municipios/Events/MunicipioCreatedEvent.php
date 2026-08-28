<?php

namespace App\Domain\Municipios\Events;

use App\Models\Municipio;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MunicipioCreatedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(public Municipio $municipio)
    {
    }
}

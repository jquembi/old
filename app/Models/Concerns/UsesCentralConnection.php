<?php

namespace App\Models\Concerns;

trait UsesCentralConnection
{
    public function getConnectionName(): ?string
    {
        return config('tenancy.database.central_connection');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permissao extends Model
{
    protected $table = 'permissoes';

    protected $fillable = ['nome', 'descricao', 'grupo'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissao', 'permissao_id', 'role_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Arrondissement extends Model
{
    protected $fillable = [
        'slt_arrondissement',
        'departement_id',
    ];

    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class);
    }

    public function communes(): HasMany
    {
        return $this->hasMany(Commune::class);
    }
}

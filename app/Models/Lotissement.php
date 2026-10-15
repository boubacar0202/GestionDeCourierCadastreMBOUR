<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lotissement extends Model
{
    protected $fillable = [
        'txt_denomination',
        'txt_nombreParcelle',
        'slt_departement',
        'slt_arrondissement',
        'slt_commune',
        'txt_surfaceLotissement',
        'txt_documentsAfferants',
    ];

    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class, 'slt_departement');
    }

    public function arrondissement(): BelongsTo
    {
        return $this->belongsTo(Arrondissement::class, 'slt_arrondissement');
    }

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class, 'slt_commune');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointDensifier extends Model
{
    protected $fillable = [
        'txt_nomPoint',
        'txt_dateCreationPoint',
        'slt_departement',
        'slt_arrondissement',
        'slt_commune',
        'txt_descriptionPoint',
        'txt_situationPoint',
        'txt_estPoint',
        'txt_nordPoint',
        'txt_hauteurPoint',
        'fichierPDFPoint',
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

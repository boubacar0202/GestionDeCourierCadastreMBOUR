<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commune extends Model
{
    protected $fillable = [
        'slt_commune',
        'arrondissement_id',
        'txt_codeCommune',
    ];

    public function arrondissement(): BelongsTo
    {
        return $this->belongsTo(Arrondissement::class);
    }
}

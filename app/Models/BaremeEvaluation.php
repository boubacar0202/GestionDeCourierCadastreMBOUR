<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaremeEvaluation extends Model
{
    protected $table = 'baremes_evaluation';

    protected $fillable = [
        'groupe',
        'type',
        'code',
        'prix',
        'ordre',
        'actif',
    ];

    protected $casts = [
        'prix' => 'integer',
        'ordre' => 'integer',
        'actif' => 'boolean',
    ];
}

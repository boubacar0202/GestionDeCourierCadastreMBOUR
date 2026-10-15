<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodeAcces extends Model
{
    protected $table = 'code_acces';
    protected $fillable = [
        'code', 
        'utilise'
    ];

 
    protected $casts = [
        'utilise' => 'boolean',
    ];
 
}

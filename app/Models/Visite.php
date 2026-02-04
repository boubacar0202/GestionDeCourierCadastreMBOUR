<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes; 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has; 

class Visite extends Model
{
    //
    use SoftDeletes;

    protected $table = 'visites';

    protected $fillable = [
        'dt_visite',
        'txt_traitementVisite',
        'txt_objetVisite',
        'txt_telVisite',
        'txt_prenomVisite',
        'txt_numdordreVisite'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'txt_numeroNote',
        'txt_typeNote',
        'txt_titreNote',
        'fichierPDF',
    ];
}

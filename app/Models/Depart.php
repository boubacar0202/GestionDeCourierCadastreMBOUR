<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Depart extends Model
{
    //
    use HasFactory;

    // Nom de la table (si Laravel ne devine pas automatiquement)
    protected $table = 'departs';
    protected $fillable = [
            'txt_bordereaucd',
            'txt_numdordrecd',
            'txt_caracterecd',
            'dt_datecouriercd',
            'txt_categoriecd',
            'txt_referencecourierarriveecd',
            'txt_referencecourierdepartcd',
            'txt_nombrepiececd',
            'txt_referencecd',
            'txt_objetcd',

            'txt_nicadcd', 
            'txt_situationcd',
            'txt_prenomcd', 
            'txt_nomcd',
            'txt_surfacecd', 
            'txt_numLotcd',

            'txt_destinatairecd',
            'dt_dateenvoicd',
            'txt_referencereceptioncd',
            'txt_observationcd',
            'txt_dureetraitementcd',
            'fichierPDFcd',
    ];
 
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has; 

class Arrivee extends Model
{
    //
    use HasFactory;
    // Nom de la table (si Laravel ne devine pas automatiquement)
    protected $table = 'arrivees';
    protected $fillable = [
        'txt_bordereau',
        'txt_numdordre', 
        'txt_caractere',
        'dt_datearrivee',
        'txt_numcourier',
        'dt_datecourier',
        'txt_reference',
        'txt_categorie',
        'txt_designation',
        'dt_date',
        'txt_heure',
        'txt_lieu',
        'txt_nombrepiece',
        'txt_objet',
        'txt_nicad', 
        'txt_situation',
        'txt_prenom', 
        'txt_nom',
        'txt_surface', 
        'txt_numLot',
        'txt_expediteur',
        'txt_agenttraiteur',
        'txt_observation', 
        'fichierPDF',
    ];
      
    
    
}

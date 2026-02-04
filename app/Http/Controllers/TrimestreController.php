<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Arrivee;
use App\Models\Depart; 
use App\Models\Trimestre;
use Carbon\Carbon; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TrimestreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $trimestres = Trimestre::all();
        $morcellementsCount = Arrivee::where(DB::raw('LOWER(txt_designation)'), 'like', '%Morcellements%')->count();
   
        return Inertia::render('trimestre/create', [
            'trimestres' => $trimestres,
            'morcellementsCount' => $morcellementsCount
        ]);
    }
     
    public function create()
    {
        $trimestres = Trimestre::orderBy('id')->get(); 
 
        $now = Carbon::now();
        $currentQuarter = $now->quarter;
        $year = $now->year;

        $startOfQuarter = Carbon::createFromDate($year, (($currentQuarter - 1) * 3) + 1, 1)->startOfDay();
        $endOfQuarter = (clone $startOfQuarter)->addMonths(3)->subDay()->endOfDay();

        // Récupérer toutes les arrivées de catégorie "Demande SERVICES"
        $courriersRetourners = Depart::where('txt_caracterecd', 'Dossier Retourne')
            ->whereBetween('dt_datecouriercd', [$startOfQuarter, $endOfQuarter])
            ->count();
 
        $designations = [
            "Morcellements",
            "Réquisition d\'immatriculation",
            'Demande Avis Technique',
            "Demande de terrain/Echange",
            "Prospection de terrain",
            "Autorisation de construction",
            "Autorisation de lotir",
            "Demande d\'états des lieux",
            "Demande de délimitation",
            "Demande de reconstruction",
            "Réquisition DSCOS", 
            "Tribunal", 
            "Litiges",
            "Demande d\'extraits de plan",
            "Demande de situation foncière", 
            "Demande de Cession définitive a Titre Gratuit",
            "Demande de Régularisation",
            "Demande d\'attestation du Cadastre",
            "Projets de Lotissements reçus",
            "Réceptions de lotissements",
            "Lotissements réalisés sans respect des procédures",
            "Duplication de CIC",
            "Demande de Titre foncier",
            "Autirisationde morceler",
            "Demande d\'évaluation",
            "Nombre de fiches de mise à jour reçues",
            "Nombre de dossiers techniques en attente de fiches de mise à jour",
        ];
  
        // 1. Stock Fin Précédent (correct)
        $stockFin = []; 
        foreach ($designations as $designation) {
            $stockFin[$designation] = Arrivee::where('txt_designation', 'like', "%{$designation}%")
                ->where('dt_datearrivee', '<', $startOfQuarter)
                ->count(); 
        }

        // 2. Reçus du trimestre (correct)
        $countsByTrimestre = [];
        foreach ($designations as $designation) {
            $countsByTrimestre[$designation] = Arrivee::where('txt_designation', 'like', "%{$designation}%")
                ->whereBetween('dt_datearrivee', [$startOfQuarter, $endOfQuarter])
                ->count();
        }

        // 3. Traités du trimestre (correct)
        $arriveeRefCol = 'txt_reference'; 
        $countsTraiterByTrimestre = [];  
        foreach ($designations as $designation) {
            $countsTraiterByTrimestre[$designation] = Arrivee::where('txt_designation', 'like', "%{$designation}%")
                ->whereBetween('dt_datearrivee', [$startOfQuarter, $endOfQuarter])
                ->whereExists(function ($query) use ($arriveeRefCol) {
                    $query->select(DB::raw(1))
                        ->from('departs')
                        ->whereColumn("departs.txt_referencecourierarriveecd", "arrivees.{$arriveeRefCol}");
                })
                ->count();
        }

        // 4. INSTANCES RESTANTES - CALCUL CORRIGÉ
        $instancesRestantes = []; 
        foreach ($designations as $designation) {
            // Calcul cohérent : Stock Fin = Stock Initial + Reçus - Traités
            $instancesRestantes[$designation] = $stockFin[$designation] + $countsByTrimestre[$designation] - $countsTraiterByTrimestre[$designation];
            
            // Éviter les valeurs négatives
            if ($instancesRestantes[$designation] < 0) {
                $instancesRestantes[$designation] = 0;
            }
        }

        return Inertia::render('trimestre/create', [ 
            'trimestres' => $trimestres,
            'countsByTrimestre' => $countsByTrimestre,
            'countsTraiterByTrimestre' => $countsTraiterByTrimestre,
            'stockFin' => $stockFin,
            'courriersRetourners' => $courriersRetourners,
            'instancesRestantes' => $instancesRestantes
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
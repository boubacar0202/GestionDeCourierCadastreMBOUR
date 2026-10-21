<?php

namespace App\Http\Controllers;

use App\Models\Arrivee;
use App\Models\Depart;
use App\Models\Visite;
use App\Models\Terrain;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {

        // Récupérer tous les TERRAINS 
        $Delimitation = Terrain::whereNotNull('slt_document_admin')
                        ->where('slt_document_admin', '=', 'Déliberation')
                        ->count();

        // Récupérer tous les NICAD
        $Nicad = Terrain::whereNotNull('txt_nicad')
                        ->where('txt_nicad', '!=', '')
                        ->count();

        $SansNicad = Terrain::whereNull('txt_nicad')
                ->orWhere('txt_nicad', '=', '')
                ->count();

        $instances = Arrivee::query()
            ->leftJoin('departs', 'departs.txt_referencecourierarriveecd', '=', 'arrivees.txt_reference')
            ->where('arrivees.txt_categorie', 'Demande SERVICES')
            ->whereNull('departs.id')
            ->count();

        return Inertia::render('Dashboard', [
            'totalTerrains' => Terrain::count(),
            'totalCourriersArrivees' => Arrivee::count(),
            'totalCourriersDeparts' => Depart::count(),
            'totalVisites' => Visite::count(),
            'totalInstances' => $instances,
            'totalDelimitation' => $Delimitation,
            'totalNicad' => $Nicad,
            'totalSansNicad' => $SansNicad,
        ]);
    }
}

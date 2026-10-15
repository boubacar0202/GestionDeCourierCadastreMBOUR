<?php

namespace App\Http\Controllers;

use App\Models\Arrivee;
use App\Models\Depart;
use App\Models\Visite;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $instances = Arrivee::query()
            ->leftJoin('departs', 'departs.txt_referencecourierarriveecd', '=', 'arrivees.txt_reference')
            ->where('arrivees.txt_categorie', 'Demande SERVICES')
            ->whereNull('departs.id')
            ->count();

        return Inertia::render('Dashboard', [
            'totalCourriersArrivees' => Arrivee::count(),
            'totalCourriersDeparts' => Depart::count(),
            'totalVisites' => Visite::count(),
            'totalInstances' => $instances,
        ]);
    }
}

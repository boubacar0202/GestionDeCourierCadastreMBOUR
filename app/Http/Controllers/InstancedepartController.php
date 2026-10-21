<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Arrivee;
use App\Models\CodeAcces;
use App\Models\Depart;

class InstancedepartController extends Controller
{
    //
        /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return Inertia::render('');
    }
 
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $arrivees = Arrivee::orderBy('id', 'desc')->get();
        $departs = Depart::orderBy('id', 'desc')->get();

        return Inertia::render('instancedepart/create', [
            'arrivees' => $arrivees,
            'departs' => $departs,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        if (! CodeAcces::consommer((string) $request->input('code_acces', ''))) {
            return back()->withErrors(['code_acces' => 'Code d’accès invalide ou déjà utilisé.']);
        }

        $depart = Depart::findOrFail($id);
        $numero = $depart->txt_numdordrecd;
        $depart->delete();

        return redirect()->back()->with('success', 'Courrier Départ N° ' . $numero . ' supprimé avec succès.');
    }

    
}

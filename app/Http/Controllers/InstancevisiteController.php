<?php

namespace App\Http\Controllers;

use App\Models\CodeAcces;
use App\Models\Visite;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InstancevisiteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $visites = Visite::orderBy('id', 'desc')->get(); 

        return Inertia::render('instancevisite/create', [
            'visites' => $visites, 
        ]);
    }

 
 
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        if (! CodeAcces::consommer((string) $request->input('code_acces', ''))) {
            return back()->withErrors(['code_acces' => 'Code d’accès invalide ou déjà utilisé.']);
        }

        $visite = Visite::findOrFail($id);
        $nom = $visite->txt_prenomVisite;
        $visite->delete();

        return redirect()->back()->with('success', 'Visite de ' . $nom . ' supprimée avec succès.');
    }
}

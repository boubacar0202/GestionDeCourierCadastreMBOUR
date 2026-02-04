<?php

namespace App\Http\Controllers;

use App\Models\Visite;
use Illuminate\Http\Request;
use Inertia\Inertia;


class VisiteController extends Controller
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
        return Inertia::render("visite/create");    

    }
    
    // NUmero  
    public function getNextOrdre($annee)
    {
 
        $last = Visite::whereYear('dt_visite', $annee)
        ->orderByRaw('CAST(SUBSTRING_INDEX(txt_numdordreVisite, "/", 1) AS UNSIGNED) DESC')
        ->first();
    
        $nextOrdre = $last ? ((int) explode('/', $last->txt_numdordreVisite)[0] + 1) : 1;

        $numOrdre = str_pad($nextOrdre, 5, '0', STR_PAD_LEFT);
    
        return response()->json([
            'num_dordre' => $numOrdre,      // ex: "00001" 
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        // dd($request->all());
        $validateData = $request->validate([ 
            'txt_numdordreVisite' => 'required|unique:visites,txt_numdordreVisite',  
            'txt_prenomVisite' => 'required|string',
            'txt_telVisite' => 'required|string',
            'txt_objetVisite' => 'required|string',
            'txt_traitementVisite' => 'required|',
            'dt_visite' => 'required|date', 
        ],[
            'txt_numdordreVisite.unique' => 'Le numéro d\'ordre existe déjà.',
            'txt_numdordreVisite.required' => 'Le numéro est requis.',
            'txt_prenomVisite.required' => 'Le numéro est requis.',
            'txt_telVisite.required' => 'Le numéro est requis.',
            'txt_objetVisite.required' => 'Le numéro est requis.',
            'txt_traitementVisite.required' => 'Le numéro est requis.', 
            'dt_visite.required' => 'Le numéro est requis.', 
        ]);
          
        Visite::create([ 
            'txt_numdordreVisite' =>  $validateData['txt_numdordreVisite'],  
            'txt_prenomVisite' => $validateData['txt_prenomVisite'],
            'txt_telVisite' => $validateData['txt_telVisite'],
            'txt_objetVisite' => $validateData['txt_objetVisite'],
            'txt_traitementVisite' => $validateData['txt_traitementVisite'],
            'dt_visite' => $validateData['dt_visite'], 
        ]);

        return redirect()->route('visite.create')->with('success', 'Courrier arrivée créé avec succès');
  
    }
    /**
     * Display the specified resource.
     */
    public function show(Visite $visite)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Visite $visite)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Visite $visite)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Visite $visite)
    {
        //
        // Vérifier code d'accès
        $code = CodeAcces::where('code', $request->code_acces)
            ->where('utilise', false)
            ->first();

        if (!$code) {
            return back()->withErrors(['code_acces' => 'Code d’accès invalide ou déjà utilisé.']);
        }
 

        // Supprimer le courrier
        $visite = InstanceVisite::findOrFail($id);
        $visite->delete();

        return back()->with('success', 'Courrier supprimé avec succès.');
    }
}

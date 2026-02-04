<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB; 
use App\Models\Depart;
use App\Models\Arrivee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DepartController extends Controller
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
        return Inertia::render("depart/create");
    }

    /**
     * Store a newly created resource in storage.
    */

    // retrouver le dernier numéro dordre 
    public function getNextOrdre($annee)
    {
         
        $last = Depart::whereYear('dt_datecouriercd', $annee) // 🔥 ici on filtre par champ dt_datearrivee, pas created_at !
        ->orderByRaw('CAST(SUBSTRING_INDEX(txt_numdordrecd, "/", 1) AS UNSIGNED) DESC')
        ->first();
    
        $nextOrdre = $last ? ((int) explode('/', $last->txt_numdordrecd)[0] + 1) : 1;

        $numOrdre = str_pad($nextOrdre, 4, '0', STR_PAD_LEFT);
    
        return response()->json([
            'num_dordre' => $numOrdre,      // ex: "00001" 
        ]);
    }
 
    public function fetchReferencesArrivee(Request $request): JsonResponse
    { 

        $departs = Depart::all();

        // Récupérer toutes les arrivées de catégorie "Demande SERVICES"
        $arrivees = Arrivee::where('txt_categorie', 'Demande SERVICES')->get();
 
        $arriveesFiltrees = $arrivees->filter(function ($arrivee) use ($departs) {
            $existeDansDepart = $departs->contains(function ($depart) use ($arrivee) {
                return $depart->txt_referencecourierarriveecd === $arrivee->txt_reference; 
            });
            return !$existeDansDepart;
        })->values();

        // références
        $references = $arriveesFiltrees->pluck('txt_reference')->all(); 

        // Association txt_reference et liste
        $map_ref_to_expediteur = Arrivee::pluck('txt_expediteur', 'txt_reference')->toArray();
        $map_ref_to_objet = Arrivee::pluck('txt_objet', 'txt_reference')->toArray();
        $map_ref_to_nombrePiece = Arrivee::pluck('txt_nombrepiece', 'txt_reference')->toArray();

        $map_ref_to_bordoreau = Arrivee::pluck('txt_bordereau', 'txt_reference')->toArray();
        $map_ref_to_numLot = Arrivee::pluck('txt_numLot', 'txt_reference')->toArray();
        $map_ref_to_prenom = Arrivee::pluck('txt_prenom', 'txt_reference')->toArray();
        $map_ref_to_nom = Arrivee::pluck('txt_nom', 'txt_reference')->toArray();
        $map_ref_to_surface = Arrivee::pluck('txt_surface', 'txt_reference')->toArray();
        $map_ref_to_situation = Arrivee::pluck('txt_situation', 'txt_reference')->toArray();
        $map_ref_to_nicad = Arrivee::pluck('txt_nicad', 'txt_reference')->toArray();

        $map_ref_to_reception = Arrivee::pluck('txt_reference', 'txt_reference')->toArray();

 
        return response()->json([
            'references' => $references,
            'map_ref_to_expediteur' => $map_ref_to_expediteur,
            'map_ref_to_objet' => $map_ref_to_objet,
            'map_ref_to_nombrePiece' => $map_ref_to_nombrePiece,
            'map_ref_to_reception' => $map_ref_to_reception,

            'map_ref_to_bordoreau' => $map_ref_to_bordoreau,
            'map_ref_to_numLot' => $map_ref_to_numLot,
            'map_ref_to_prenom' => $map_ref_to_prenom,
            'map_ref_to_nom' => $map_ref_to_nom,
            'map_ref_to_surface' => $map_ref_to_surface,
            'map_ref_to_situation' => $map_ref_to_situation,
            'map_ref_to_nicad' => $map_ref_to_nicad
        ]);
    }

    // Supprimer le fichier PDF
    public function deletePdf($id)
    {
        $depart = Depart::findOrFail($id);

        if ($depart->fichierPDFcd && Storage::disk('public')->exists($depart->fichierPDFcd)) {
            Storage::disk('public')->delete($depart->fichierPDFcd);
        }
 
        $depart->update(['fichierPDFcd' => null]);
 
        return back()->with('success', 'Fichier supprimé avec succès');
    }
    

    public function store(Request $request)
    {
        //
        $validateData   = $request->validate([  
            'txt_bordereaucd' => 'nullable|string',
            'txt_numdordrecd' => 'required|unique:departs,txt_numdordrecd',  
            'txt_caracterecd' => 'nullable|string|max:255',
            'dt_datecouriercd' => 'required|date',
            'txt_categoriecd' => 'required|string|max:255',
            'txt_referencecourierarriveecd' => 'nullable|string|max:255',
            'txt_referencecourierdepartcd'=> 'nullable|string|max:255',
            'txt_nombrepiececd' => 'required|integer|max:255',
            'txt_referencecd' => 'nullable|string|max:255',
            'txt_objetcd' => 'required|string|max:255',

            'txt_nicadcd' => 'nullable|string|max:255',
            'txt_situationcd' => 'nullable|string|max:255',
            'txt_prenomcd' => 'nullable|string|max:255',
            'txt_nomcd' => 'nullable|string|max:255', 
            'txt_surfacecd' => 'nullable|numeric',
            'txt_numLotcd' => 'nullable|string|max:255',

            'txt_destinatairecd' => 'required|string|max:255',
            'dt_dateenvoicd' => 'required|date|before_or_equal:today',
            'txt_referencereceptioncd' => 'nullable|string|max:255',
            'txt_observationcd' => 'nullable|string|max:255',
            'txt_dureetraitementcd' => 'nullable|string|max:255', 
            'fichierPDFcd' => 'nullable|file|mimes:pdf|max:120400',  // 100Mo * 100 x 1024
        ],[
             
            'txt_numdordrecd.required' => 'Le numéro d\'ordre est requis.',
            'txt_numdordrecd.unique' => 'Le numéro d\'ordre doit être unique.', 
            'dt_datecouriercd.required' => 'La date du courrier est requise.',
            'dt_datecouriercd.date' => 'La date du courrier doit être une date valide.',
            'txt_categoriecd.required' => 'La catégorie du courrier est requise.',
            'txt_nombrepiececd.required' => 'Le nombre de pièces est requis.',
            'txt_objetcd.required' => 'L\'objet du courrier est requis.',
            'txt_destinatairecd.required' => 'Le destinataire du courrier est requis.',
            'dt_dateenvoicd.required' => 'La date d\'envoi est requise.',
            'dt_dateenvoicd.date' => 'La date d\'envoi doit être une date valide.',
            'dt_dateenvoicd.before_or_equal' => 'La date d\'envoi ne peut pas être dans le futur.',
            'fichierPDFcd.file' => 'Le fichier doit être un fichier valide.',
            'fichierPDFcd.mimes' => 'Le fichier doit être au format PDF.',
            'fichierPDFcd.max' => 'La taille du fichier ne doit pas dépasser 100 Mo.',
        ]);

        // Validation conditionnelle
        if ($request->hasFile('fichierPDFcd')) {
            $validateData['fichierPDFcd'] = $request->file('fichierPDFcd')->store('courrierdepart', 'public');
        }

        Depart::create([
            'txt_bordereaucd' => $validateData['txt_bordereaucd'] ?? null,
            'txt_numdordrecd' => $validateData['txt_numdordrecd'],
            'txt_caracterecd' => $validateData['txt_caracterecd'] ?? null,
            'dt_datecouriercd' => $validateData['dt_datecouriercd'],
            'txt_categoriecd' => $validateData['txt_categoriecd'],
            'txt_referencecourierarriveecd' => $validateData['txt_referencecourierarriveecd'] ?? null,
            'txt_referencecourierdepartcd' => $validateData['txt_referencecourierdepartcd'] ?? null,
            'txt_nombrepiececd' => $validateData['txt_nombrepiececd'],
            'txt_referencecd' => $validateData['txt_referencecd'] ?? null,
            'txt_objetcd' => $validateData['txt_objetcd'],
            
            'txt_nicadcd' => $validateData['txt_nicadcd'] ?? null,
            'txt_situationcd' => $validateData['txt_situationcd'] ?? null,
            'txt_prenomcd' => $validateData['txt_prenomcd'] ?? null,
            'txt_nomcd' => $validateData['txt_nomcd'] ?? null, 
            'txt_surfacecd' => $validateData['txt_surfacecd'] ?? null,
            'txt_numLotcd' => $validateData['txt_numLotcd'] ?? null,

            'txt_destinatairecd' => $validateData['txt_destinatairecd'],
            'dt_dateenvoicd' => $validateData['dt_dateenvoicd'],
            'txt_referencereceptioncd' => $validateData['txt_referencereceptioncd'] ?? null,
            'txt_observationcd' => $validateData['txt_observationcd'] ?? null,
            'txt_dureetraitementcd' => $validateData['txt_dureetraitementcd'],
            'fichierPDFcd'=> $validateData['fichierPDFcd'] ?? null,
        ]);
        return redirect()->route('depart.create')->with('success', 'Dossier enregistré avec succès');

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
    public function editdepart($id)
    {
        //
        $departs = Depart::findOrFail($id); 
 
        return Inertia::render('depart/editdepart', [
            'departs' => $departs,   
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update (Request $request, string $id)
    {
        //
        $depart = Depart::findOrFail($id); 
  
        $validateData   = $request->validate([  
            // 'txt_numdordrecd' => 'required|unique:departs,txt_numdordrecd'.$id,  
            'txt_bordereaucd' => 'nullable|string|max:255',
            'txt_numdordrecd' => [
                'required',
                Rule::unique('departs', 'txt_numdordrecd')->ignore($depart->id),
            ],
            'txt_caracterecd' => 'nullable|string|max:255',
            'dt_datecouriercd' => 'required|date',
            'txt_categoriecd' => 'required|string|max:255',
            'txt_referencecourierarriveecd' => 'nullable|string|max:255',
            'txt_referencecourierdepartcd'=> 'nullable|string|max:255',
            'txt_nombrepiececd' => 'required|integer|max:255',
            'txt_referencecd' => 'nullable|string|max:255',
            'txt_objetcd' => 'required|string|max:255',
            
            'txt_nicadcd' => 'nullable|string|max:255',
            'txt_situationcd' => 'nullable|string|max:255',
            'txt_prenomcd' => 'nullable|string|max:255',
            'txt_nomcd' => 'nullable|string|max:255', 
            'txt_surfacecd' => 'nullable|numeric',
            'txt_numLotcd' => 'nullable|string|max:255',
            
            'txt_destinatairecd' => 'required|string|max:255',
            'dt_dateenvoicd' => 'required|date|before_or_equal:today',
            'txt_referencereceptioncd' => 'nullable|string|max:255',
            'txt_observationcd' => 'nullable|string|max:255',
            'txt_dureetraitementcd' => 'nullable|string|max:255', 
            'fichierPDFcd' => 'nullable|file|mimes:pdf|max:120400',  // 100Mo * 100 x 1024
        ],[
            'txt_numdordrecd.required' => 'Le numéro d\'ordre est requis.',
            'txt_numdordrecd.unique' => 'Le numéro d\'ordre doit être unique.', 
            'dt_datecouriercd.required' => 'La date du courrier est requise.',
            'dt_datecouriercd.date' => 'La date du courrier doit être une date valide.',
            'txt_categoriecd.required' => 'La catégorie du courrier est requise.',
            'txt_nombrepiececd.required' => 'Le nombre de pièces est requis.',
            'txt_objetcd.required' => 'L\'objet du courrier est requis.',
            'txt_destinatairecd.required' => 'Le destinataire du courrier est requis.',
            'dt_dateenvoicd.required' => 'La date d\'envoi est requise.',
            'dt_dateenvoicd.date' => 'La date d\'envoi doit être une date valide.',
            'dt_dateenvoicd.before_or_equal' => 'La date d\'envoi ne peut pas être dans le futur.',
            'fichierPDFcd.file' => 'Le fichier doit être un fichier valide.',
            'fichierPDFcd.mimes' => 'Le fichier doit être au format PDF.',
            'fichierPDFcd.max' => 'La taille du fichier ne doit pas dépasser 100 Mo.',
        ]);
       
        // ✅ GESTION DU FICHIER COMME DANS STORE()
        if ($request->hasFile('fichierPDFcd')) {
            // Supprimer l'ancien 
            if ($depart->fichierPDFcd && Storage::disk('public')->exists($depart->fichierPDFcd)) {
                Storage::disk('public')->delete($depart->fichierPDFcd);
            }
            
            // Stocker le nouveau fichier 
            $validateData['fichierPDFcd'] = $request->file('fichierPDFcd')->store('courrierdepart', 'public');
        } else {
            // Garder l'ancien fichier si aucun nouveau y est pas
            $validateData['fichierPDFcd'] = $depart->fichierPDFcd;
        }
 
        $depart->update($validateData);
        
        return redirect()->route('instancedepart.create')->with('success', 'Courrier '.$depart->txt_numdordrecd.' modifié avec succès');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        // Vérifier code d'accès
        $code = CodeAcces::where('code', $request->code_acces)
            ->where('utilise', false)
            ->first();

        if (!$code) {
            return back()->withErrors(['code_acces' => 'Code d’accès invalide ou déjà utilisé.']);
        }
 

        // Supprimer le courrier
        $depart = InstanceDepart::findOrFail($id);
        $depart->delete();

        return back()->with('success', 'Courrier supprimé avec succès.');
    }
}

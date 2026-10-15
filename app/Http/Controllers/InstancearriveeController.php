<?php

namespace App\Http\Controllers;

use App\Models\Arrivee;
use App\Models\CodeAcces;
use App\Models\Depart;
use Inertia\Inertia;
use Illuminate\Http\Request;

class InstancearriveeController extends Controller
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

        return Inertia::render('instancearrivee/create', [
            'arrivees' => $arrivees,
            'departs' => $departs,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $code = CodeAcces::where('code', $request->code_acces)
            ->where('utilise', false)
            ->first();

        if (!$code) {
            return back()->withErrors(['code_acces' => 'Code d’accès invalide ou déjà utilisé.']);
        }

        $code->update(['utilise' => true]);

        $arrivee = Arrivee::findOrFail($id);
        $numero = $arrivee->txt_numdordre;
        $arrivee->delete();

        return redirect()->back()->with('success', 'Courrier Arrivée N° ' . $numero . ' supprimé avec succès.');
    }

 
}

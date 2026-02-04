<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Visite;

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
    public function destroy(string $id)
    {
        // 
        $visite = Visite::findOrFail($id);
        $visite->delete();

        return redirect()->back()->with('success', 'Courrier Arrivée N° ' . $visite->txt_prenomVisite . ' supprimé avec succès.');
    }
}

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
        return Inertia::render('');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $perPage = 15;
        $filters = [
            'annee' => trim((string) $request->input('annee', '')),
            'courrier' => trim((string) $request->input('courrier', '')),
            'prenomNom' => trim((string) $request->input('prenomNom', '')),
            'designation' => trim((string) $request->input('designation', '')),
        ];

        $page = max(1, (int) $request->input('page', 1));

        $query = $this->filteredVisites($filters);
        $total = (clone $query)->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);

        $visites = $query
            ->paginate($perPage, ['*'], 'page', $page)
            ->appends(array_filter($filters, fn ($value) => $value !== ''));

        $totalCourrier = Visite::query()
            ->whereNotNull('txt_numdordreVisite')
            ->where('txt_numdordreVisite', '!=', '')
            ->count();

        $designations = Visite::query()
            ->whereNotNull('txt_traitementVisite')
            ->where('txt_traitementVisite', '!=', '')
            ->distinct()
            ->orderBy('txt_traitementVisite')
            ->pluck('txt_traitementVisite')
            ->values();

        return Inertia::render('instancevisite/create', [
            'visites' => $visites,
            'designations' => $designations,
            'totalCourrier' => $totalCourrier,
            'filters' => $filters,
        ]);
    }

    private function filteredVisites(array $filters)
    {
        $query = Visite::query();

        if (preg_match('/^\d{4}$/', $filters['annee'])) {
            // dt_visite est stocké en string : filtrer via LIKE année ou YEAR si date
            $query->where(function ($q) use ($filters) {
                $q->where('dt_visite', 'like', $filters['annee'].'%')
                    ->orWhere('dt_visite', 'like', '%/'.$filters['annee'])
                    ->orWhere('dt_visite', 'like', '%-'.$filters['annee'])
                    ->orWhereRaw('YEAR(dt_visite) = ?', [(int) $filters['annee']]);
            });
        }

        if ($filters['courrier'] !== '') {
            $query->where('txt_numdordreVisite', 'like', $this->likeTerm($filters['courrier']));
        }

        if ($filters['prenomNom'] !== '') {
            $term = $this->likeTerm($filters['prenomNom']);
            $query->where(function ($q) use ($term) {
                $q->where('txt_prenomVisite', 'like', $term)
                    ->orWhere('txt_telVisite', 'like', $term)
                    ->orWhere('txt_objetVisite', 'like', $term);
            });
        }

        if ($filters['designation'] !== '') {
            $query->where('txt_traitementVisite', $filters['designation']);
        }

        return $query
            ->orderByRaw('CAST(SUBSTRING_INDEX(COALESCE(txt_numdordreVisite, "0"), "/", 1) AS UNSIGNED) ASC')
            ->orderBy('id');
    }

    private function likeTerm(string $value): string
    {
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);

        return '%'.$escaped.'%';
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

        return redirect()->back()->with('success', 'Visite de '.$nom.' supprimée avec succès.');
    }
}

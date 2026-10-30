<?php

namespace App\Http\Controllers;

use App\Models\CodeAcces;
use App\Models\Depart;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InstancedepartController extends Controller
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

        $query = $this->filteredDeparts($filters);
        $total = (clone $query)->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);

        $departs = $query
            ->paginate($perPage, ['*'], 'page', $page)
            ->appends(array_filter($filters, fn ($value) => $value !== ''));

        $totalCourrier = Depart::query()
            ->whereNotNull('txt_numdordrecd')
            ->where('txt_numdordrecd', '!=', '')
            ->count();

        $designations = Depart::query()
            ->whereNotNull('txt_categoriecd')
            ->where('txt_categoriecd', '!=', '')
            ->distinct()
            ->orderBy('txt_categoriecd')
            ->pluck('txt_categoriecd')
            ->values();

        return Inertia::render('instancedepart/create', [
            'departs' => $departs,
            'designations' => $designations,
            'totalCourrier' => $totalCourrier,
            'filters' => $filters,
        ]);
    }

    private function filteredDeparts(array $filters)
    {
        $query = Depart::query();

        if (preg_match('/^\d{4}$/', $filters['annee'])) {
            $query->whereYear('dt_datecouriercd', (int) $filters['annee']);
        }

        if ($filters['courrier'] !== '') {
            $term = $this->likeTerm($filters['courrier']);
            $query->where(function ($q) use ($term) {
                $q->where('txt_numdordrecd', 'like', $term)
                    ->orWhere('txt_referencecd', 'like', $term)
                    ->orWhere('txt_referencecourierarriveecd', 'like', $term);
            });
        }

        if ($filters['prenomNom'] !== '') {
            $term = $this->likeTerm($filters['prenomNom']);
            $query->where(function ($q) use ($term) {
                $q->where('txt_prenomcd', 'like', $term)
                    ->orWhere('txt_nomcd', 'like', $term)
                    ->orWhere('txt_destinatairecd', 'like', $term);
            });
        }

        if ($filters['designation'] !== '') {
            $query->where('txt_categoriecd', $filters['designation']);
        }

        return $query
            ->orderByRaw('CAST(SUBSTRING_INDEX(COALESCE(txt_numdordrecd, "0"), "/", 1) AS UNSIGNED) ASC')
            ->orderBy('id');
    }

    private function likeTerm(string $value): string
    {
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);

        return '%'.$escaped.'%';
    }

    public function destroy(Request $request, $id)
    {
        if (! CodeAcces::consommer((string) $request->input('code_acces', ''))) {
            return back()->withErrors(['code_acces' => 'Code d’accès invalide ou déjà utilisé.']);
        }

        $depart = Depart::findOrFail($id);
        $numero = $depart->txt_numdordrecd;
        $depart->delete();

        return redirect()->back()->with('success', 'Courrier Départ N° '.$numero.' supprimé avec succès.');
    }
}

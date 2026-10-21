<?php

namespace App\Http\Controllers;

use App\Models\Arrivee;
use App\Models\CodeAcces;
use Carbon\Carbon;
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
    public function create(Request $request)
    {
        $perPage = 15;
        $filters = [
            'annee' => trim((string) $request->input('annee', '')),
            'courrier' => trim((string) $request->input('courrier', '')),
            'prenomNom' => trim((string) $request->input('prenomNom', '')),
            'designation' => trim((string) $request->input('designation', '')),
        ];

        $focus = trim((string) $request->input('focus', ''));
        $page = max(1, (int) $request->input('page', 1));

        if ($focus !== '') {
            $foundPage = $this->pageForNumero($this->filteredArrivees($filters), $focus, $perPage);

            if ($foundPage === null) {
                $filters = [
                    'annee' => '',
                    'courrier' => '',
                    'prenomNom' => '',
                    'designation' => '',
                ];
                $foundPage = $this->pageForNumero($this->filteredArrivees($filters), $focus, $perPage);
            }

            if ($foundPage !== null) {
                $page = $foundPage;
            }
        }

        $query = $this->filteredArrivees($filters);
        $total = (clone $query)->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);

        $arrivees = $query
            ->paginate($perPage, ['*'], 'page', $page)
            ->appends(array_filter($filters, fn ($value) => $value !== ''));

        $now = now();
        $limit72 = $now->copy()->addHours(72);

        $arrivees->getCollection()->transform(function ($arrivee) use ($now, $limit72) {
            $date = $arrivee->dt_date ? Carbon::parse($arrivee->dt_date) : null;
            $arrivee->setAttribute(
                'isLateConvocation',
                $arrivee->txt_categorie === 'Convocation - Invitation'
                    && $date
                    && $date->greaterThan($now)
                    && $date->lessThanOrEqualTo($limit72)
            );

            return $arrivee;
        });

        $totalCourrier = Arrivee::query()
            ->whereNotNull('txt_numdordre')
            ->where('txt_numdordre', '!=', '')
            ->count();

        $totalConvocationsImminentes = Arrivee::query()
            ->where('txt_categorie', 'Convocation - Invitation')
            ->where('dt_date', '>', $now)
            ->where('dt_date', '<=', $limit72)
            ->count();

        $convocationsDans48h = Arrivee::query()
            ->where('txt_categorie', 'Convocation - Invitation')
            ->where('dt_date', '>', $now)
            ->where('dt_date', '<=', $now->copy()->addHours(48))
            ->orderBy('dt_date')
            ->get(['id', 'txt_numdordre', 'dt_date', 'txt_heure']);

        $convocationProche = [];
        if ($convocationsDans48h->isNotEmpty()) {
            $minDate = $convocationsDans48h->min(fn ($courrier) => Carbon::parse($courrier->dt_date)->getTimestamp());
            $convocationProche = $convocationsDans48h
                ->filter(fn ($courrier) => Carbon::parse($courrier->dt_date)->getTimestamp() === $minDate)
                ->values();
        }

        $designations = Arrivee::query()
            ->whereNotNull('txt_designation')
            ->where('txt_designation', '!=', '')
            ->distinct()
            ->orderBy('txt_designation')
            ->pluck('txt_designation')
            ->values();

        return Inertia::render('instancearrivee/create', [
            'arrivees' => $arrivees,
            'designations' => $designations,
            'totalCourrier' => $totalCourrier,
            'totalConvocationsImminentes' => $totalConvocationsImminentes,
            'convocationProche' => $convocationProche,
            'filters' => $filters,
            'focus' => $focus,
        ]);
    }

    private function filteredArrivees(array $filters)
    {
        $query = Arrivee::query();

        if (preg_match('/^\d{4}$/', $filters['annee'])) {
            $query->whereYear('dt_datearrivee', (int) $filters['annee']);
        }

        if ($filters['courrier'] !== '') {
            $query->where('txt_numcourier', 'like', $this->likeTerm($filters['courrier']));
        }

        if ($filters['prenomNom'] !== '') {
            $term = $this->likeTerm($filters['prenomNom']);
            $query->where(function ($q) use ($term) {
                $q->where('txt_prenom', 'like', $term)
                    ->orWhere('txt_nom', 'like', $term)
                    ->orWhere('txt_agenttraiteur', 'like', $term);
            });
        }

        if ($filters['designation'] !== '') {
            $query->where('txt_designation', $filters['designation']);
        }

        return $query
            ->orderByRaw('CAST(SUBSTRING_INDEX(COALESCE(txt_numdordre, "0"), "/", 1) AS UNSIGNED) ASC')
            ->orderBy('id');
    }

    private function pageForNumero($query, string $numero, int $perPage): ?int
    {
        $exists = (clone $query)->where('txt_numdordre', $numero)->exists();
        if (! $exists) {
            return null;
        }

        $ordre = (int) explode('/', $numero)[0];
        $before = (clone $query)
            ->whereRaw('CAST(SUBSTRING_INDEX(COALESCE(txt_numdordre, "0"), "/", 1) AS UNSIGNED) < ?', [$ordre])
            ->count();

        return intdiv($before, $perPage) + 1;
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

        $arrivee = Arrivee::findOrFail($id);
        $numero = $arrivee->txt_numdordre;
        $arrivee->delete();

        return redirect()->back()->with('success', 'Courrier Arrivée N° ' . $numero . ' supprimé avec succès.');
    }

 
}

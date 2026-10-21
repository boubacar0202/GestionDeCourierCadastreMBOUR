<?php

namespace App\Http\Controllers;

use App\Models\ActiviteUtilisateur;
use App\Models\Arrivee;
use App\Models\Arrondissement;
use App\Models\CodeAcces;
use App\Models\Commune;
use App\Models\Depart;
use App\Models\Departement;
use App\Models\Lotissement;
use App\Models\Mutation;
use App\Models\PointDensifier;
use App\Models\User;
use App\Models\Visite;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class ParametreController extends Controller
{
    public function index()
    {
        return Inertia::render('parametre/lotissement/index', [
            'lotissements' => Lotissement::with(['departement', 'arrondissement', 'commune'])->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('parametre/lotissement/create', [
            'departements' => Departement::all(),
            'arrondissements' => [],
        ]);
    }

    public function edit($id)
    {
        $lotissement = Lotissement::with(['departement', 'arrondissement', 'commune'])->findOrFail($id);

        return Inertia::render('parametre/lotissement/edit', [
            'lotissements' => $lotissement,
            'departements' => Departement::all(),
            'arrondissements' => Arrondissement::where('departement_id', $lotissement->slt_departement)->get(),
            'communes' => Commune::where('arrondissement_id', $lotissement->slt_arrondissement)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'txt_denomination' => 'required|string',
            'txt_nombreParcelle' => 'required|string',
            'slt_departement' => 'required|integer|exists:departements,id',
            'slt_arrondissement' => 'required|integer|exists:arrondissements,id',
            'slt_commune' => 'required|integer|exists:communes,id',
            'txt_surfaceLotissement' => 'required|string',
            'txt_documentsAfferants' => 'nullable|file|mimes:pdf',
        ], [
            'txt_denomination.required' => 'Le champ "Denomination" est obligatoire.',
            'txt_nombreParcelle.required' => 'Le champ "Nombre de parcelles" est obligatoire.',
            'slt_departement.required' => 'Le champ "Departement" est obligatoire.',
            'slt_arrondissement.required' => 'Le champ "Arrondissement" est obligatoire.',
            'slt_commune.required' => 'Le champ "Commune" est obligatoire.',
            'txt_surfaceLotissement.required' => 'Le champ "Surface" est obligatoire.',
        ]);

        if ($request->hasFile('txt_documentsAfferants')) {
            $validated['txt_documentsAfferants'] = $request->file('txt_documentsAfferants')->store('lotissement', 'public');
        }

        Lotissement::create([
            'txt_denomination' => $validated['txt_denomination'],
            'txt_nombreParcelle' => $validated['txt_nombreParcelle'],
            'slt_departement' => $validated['slt_departement'],
            'slt_arrondissement' => $validated['slt_arrondissement'],
            'slt_commune' => $validated['slt_commune'],
            'txt_surfaceLotissement' => $validated['txt_surfaceLotissement'],
            'txt_documentsAfferants' => $validated['txt_documentsAfferants'] ?? null,
        ]);

        return redirect()->route('parametre.lotissement.create')
            ->with('success', 'Données lotissement enregistrées.');
    }

    public function update(Request $request, string $id)
    {
        $lotissement = Lotissement::findOrFail($id);

        $validated = $request->validate([
            'txt_denomination' => 'required|string',
            'txt_nombreParcelle' => 'required|string',
            'slt_departement' => 'required|integer|exists:departements,id',
            'slt_arrondissement' => 'required|integer|exists:arrondissements,id',
            'slt_commune' => 'required|integer|exists:communes,id',
            'txt_surfaceLotissement' => 'required|string',
            'txt_documentsAfferants' => 'nullable|file|mimes:pdf',
        ]);

        if ($request->hasFile('txt_documentsAfferants')) {
            if ($lotissement->txt_documentsAfferants && Storage::disk('public')->exists($lotissement->txt_documentsAfferants)) {
                Storage::disk('public')->delete($lotissement->txt_documentsAfferants);
            }
            $validated['txt_documentsAfferants'] = $request->file('txt_documentsAfferants')->store('lotissement', 'public');
        } else {
            $validated['txt_documentsAfferants'] = $lotissement->txt_documentsAfferants;
        }

        $lotissement->update($validated);

        return redirect()->route('parametre.lotissement.index')
            ->with('success', 'Lotissement '.$lotissement->txt_denomination.' modifié avec succès');
    }

    public function destroy(Request $request, $id)
    {
        if (! $this->codeValide($request, 3)) {
            return response()->json([
                'success' => false,
                'message' => "Code d'accès invalide. Contacter l'administrateur !",
            ], 403);
        }

        $lotissement = Lotissement::findOrFail($id);

        if ($lotissement->txt_documentsAfferants && Storage::disk('public')->exists($lotissement->txt_documentsAfferants)) {
            Storage::disk('public')->delete($lotissement->txt_documentsAfferants);
        }

        $nom = $lotissement->txt_denomination;
        $lotissement->delete();

        return response()->json([
            'success' => true,
            'message' => "Lotissement \"$nom\" supprimé avec succès.",
        ]);
    }

    public function editMutation($id)
    {
        $mutation = Mutation::findOrFail($id);

        return Inertia::render('parametre/mutationEdit', [
            'mutation' => $mutation,
        ]);
    }

    public function destroyMutation(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $code = CodeAcces::where('id', 3)
            ->where('code', $request->code)
            ->first();

        if (! $code) {
            return response()->json([
                'success' => false,
                'message' => "Code d'accès invalide. Contacter l'administrateur !",
            ], 403);
        }

        $mutation = Mutation::findOrFail($id);
        $mutation->delete();

        return response()->json([
            'success' => true,
            'message' => "{$mutation->txt_numeroMutation} supprimé avec succès.",
        ]);
    }

    public function admin()
    {
        return Inertia::render('parametre/admin', [
            'users' => User::where('role', 'admin')->where('is_validated', true)->orderBy('name')->get(),
            'pendingUsers' => User::where('is_validated', false)->latest()->get(),
        ]);
    }

    public function user()
    {
        return Inertia::render('parametre/user', [
            'users' => User::where('role', 'user')->where('is_validated', true)->orderBy('name')->get(),
        ]);
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);

        if ($this->compteProtege($user)) {
            return redirect()->route('parametre.admin')
                ->with('error', 'Impossible de modifier le compte de '.$user->name.' car il est protégé.');
        }

        return Inertia::render('parametre/adminEdit', [
            'user' => $user,
        ]);
    }

    public function updateUser(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $request->merge([
            'role' => User::normalizeRole($request->input('role')),
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|string|in:admin,user',
        ], [
            'name.required' => 'Le nom complet est obligatoire.',
            'email.required' => "L'adresse email est obligatoire.",
            'email.email' => "L'adresse email n'est pas valide.",
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'role.required' => 'Le rôle est obligatoire.',
            'role.in' => 'Le rôle choisi est invalide.',
        ]);

        if ($this->compteProtege($user)) {
            $message = 'La modification de l\'utilisateur '.$user->name.' n\'est pas autorisée.';

            return back()->withErrors(['role' => $message])->with('error', $message);
        }

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        $user->update($data);

        return redirect()
            ->route($user->role === 'user' ? 'parametre.user' : 'parametre.admin')
            ->with('success', 'Utilisateur '.$user->name.' modifié avec succès');
    }

    public function storeUser(Request $request)
    {
        $authUser = $request->user();

        if (! $authUser || $authUser->role !== 'admin') {
            return back()->with('error', 'Seuls les administrateurs peuvent créer un compte.');
        }

        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
            'role' => User::normalizeRole($request->input('role')),
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|string|in:admin,user',
        ], [
            'name.required' => 'Le nom complet est obligatoire.',
            'email.required' => "L'adresse email est obligatoire.",
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'role.required' => 'Le rôle est obligatoire.',
            'role.in' => 'Le rôle choisi est invalide.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'is_validated' => true,
        ]);

        $liste = $user->role === 'user' ? 'utilisateurs' : 'administrateurs';

        return redirect()->route('parametre.admin')
            ->with('success', 'Le compte de '.$user->name.' a été créé. Il figure dans la liste des '.$liste.'.');
    }

    public function validateUser(Request $request, $id)
    {
        $authUser = $request->user();

        if (! $authUser || $authUser->role !== 'admin' || ! $authUser->is_validated) {
            return response()->json([
                'success' => false,
                'message' => 'Seuls les administrateurs peuvent valider un compte.',
            ], 403);
        }

        $request->merge([
            'role' => User::normalizeRole($request->input('role')),
        ]);

        $validated = $request->validate([
            'role' => 'required|string|in:admin,user',
        ], [
            'role.required' => 'Choisissez si le compte devient administrateur ou utilisateur.',
            'role.in' => 'Le rôle choisi est invalide.',
        ]);

        $user = User::findOrFail($id);

        if ($user->is_validated) {
            return response()->json([
                'success' => false,
                'message' => 'Ce compte est déjà validé.',
            ], 422);
        }

        $user->update([
            'is_validated' => true,
            'role' => $validated['role'],
        ]);

        $libelle = $user->role === 'admin' ? 'administrateur' : 'utilisateur';

        return response()->json([
            'success' => true,
            'message' => 'Le compte de '.$user->name.' a été validé en tant que '.$libelle.'. Il peut maintenant se connecter.',
        ]);
    }

    public function destroyUser(Request $request, $id)
    {
        if (! $this->codeValide($request, 3)) {
            return response()->json([
                'success' => false,
                'message' => "Code d'accès invalide. Contacter l'administrateur !",
            ], 403);
        }

        $authUser = $request->user();

        if (! $authUser || $authUser->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Impossible : seuls les administrateurs peuvent supprimer un utilisateur.',
            ], 403);
        }

        $user = User::findOrFail($id);

        if ($this->compteProtege($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer le compte de '.$user->name.' car il est protégé.',
            ], 403);
        }

        try {
            $user->delete();
        } catch (QueryException $e) {
            Log::error('Suppression utilisateur impossible', [
                'id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => "Impossible de supprimer {$user->name} : des données y sont encore liées.",
            ], 409);
        }

        return response()->json([
            'success' => true,
            'message' => "{$user->name} supprimé avec succès.",
        ]);
    }

    public function createPoint()
    {
        return Inertia::render('parametre/pointDensifier/createPoint', [
            'departements' => Departement::all(),
            'arrondissements' => [],
            'communes' => [],
            'point_densifiers' => PointDensifier::with(['departement', 'arrondissement', 'commune'])->get(),
        ]);
    }

    public function editPoint($id)
    {
        $point = PointDensifier::with(['departement', 'arrondissement', 'commune'])->findOrFail($id);

        return Inertia::render('parametre/pointDensifier/editPoint', [
            'point_densifiers' => $point,
            'departements' => Departement::all(),
            'arrondissements' => Arrondissement::where('departement_id', $point->slt_departement)->get(),
            'communes' => Commune::where('arrondissement_id', $point->slt_arrondissement)->get(),
        ]);
    }

    public function storePoint(Request $request)
    {
        $validated = $this->validerPoint($request);

        if ($request->hasFile('fichierPDFPoint')) {
            $validated['fichierPDFPoint'] = $request->file('fichierPDFPoint')->store('pointDensifier', 'public');
        }

        PointDensifier::create($validated);

        return redirect()->route('parametre.pointDensifier.createPoint')
            ->with('success', 'Point '.$validated['txt_nomPoint'].' enregistré avec succès.');
    }

    public function updatePoint(Request $request, string $id)
    {
        $point = PointDensifier::findOrFail($id);
        $validated = $this->validerPoint($request, false);

        if ($request->hasFile('fichierPDFPoint')) {
            if ($point->fichierPDFPoint && Storage::disk('public')->exists($point->fichierPDFPoint)) {
                Storage::disk('public')->delete($point->fichierPDFPoint);
            }
            $validated['fichierPDFPoint'] = $request->file('fichierPDFPoint')->store('pointDensifier', 'public');
        } else {
            $validated['fichierPDFPoint'] = $point->fichierPDFPoint;
        }

        $point->update($validated);

        return redirect()->route('parametre.pointDensifier.createPoint')
            ->with('success', 'Point densifié mis à jour avec succès.');
    }

    public function destroyPoint(Request $request, $id)
    {
        if (! $this->codeValide($request, 3)) {
            return response()->json([
                'success' => false,
                'message' => "Code d'accès invalide. Contacter l'administrateur !",
            ], 403);
        }

        $point = PointDensifier::findOrFail($id);

        if ($point->fichierPDFPoint && Storage::disk('public')->exists($point->fichierPDFPoint)) {
            Storage::disk('public')->delete($point->fichierPDFPoint);
        }

        $nom = $point->txt_nomPoint;
        $point->delete();

        return response()->json([
            'success' => true,
            'message' => "Point \"$nom\" supprimé avec succès.",
        ]);
    }

    public function getDepartements()
    {
        return response()->json([
            'departements' => Departement::all(),
        ]);
    }

    public function getArrondissementByDepartement(int $id)
    {
        return response()->json([
            'arrondissements' => Arrondissement::where('departement_id', $id)->get(),
        ]);
    }

    public function getCommunesByArrondissement(int $id)
    {
        return response()->json([
            'communes' => Commune::where('arrondissement_id', $id)->get(),
        ]);
    }

    public function chart()
    {
        return Inertia::render('parametre/chart', [
            'pieData' => $this->getPieChartData(),
            'barData' => $this->getBarChartData(),
            'evaluation' => $this->getEvaluationUtilisateurs(),
            'stats' => $this->getStats(),
        ]);
    }

    public function getChartData(Request $request)
    {
        return response()->json([
            'pie' => $this->getPieChartData(),
            'bar' => $this->getBarChartData(),
            'evaluation' => $this->getEvaluationUtilisateurs(
                $request->integer('annee') ?: null,
                $request->integer('trimestre') ?: null,
            ),
        ]);
    }

    public function getStatsData()
    {
        return response()->json($this->getStats());
    }

    public function getCessionData(Request $request)
    {
        $designation = (string) $request->input('slt_service_rendu', 'Délimitation');
        $offset = min(0, (int) $request->input('offset', 0));
        $months = $this->getQuarterMonths($offset);

        $monthData = [];
        foreach ($months as $month) {
            $monthData[] = $this->compterCourriersDuMois($month['month'], $month['year'], $designation);
        }

        $total = [
            'nombre_unitaire' => array_sum(array_column($monthData, 'nombre_unitaire')),
            'montant_total' => array_sum(array_column($monthData, 'montant_total')),
            'montant_etat' => 0,
            'montant_unitaire' => 0,
            'details' => [],
        ];

        $response = ['months' => [], 'total' => $total, 'types' => []];

        foreach ($months as $index => $month) {
            $response['months'][] = [
                'name' => $month['name'],
                'nombre_unitaire' => $monthData[$index]['nombre_unitaire'],
                'montant_total' => $monthData[$index]['montant_total'],
                'montant_etat' => 0,
                'montant_unitaire' => 0,
                'details' => $monthData[$index]['details'],
            ];
        }

        return response()->json($response);
    }

    private function validerPoint(Request $request, bool $fichierNouveau = true): array
    {
        return $request->validate([
            'txt_nomPoint' => 'required|string|max:255',
            'txt_dateCreationPoint' => 'required|date',
            'slt_departement' => 'required|exists:departements,id',
            'slt_arrondissement' => 'required|exists:arrondissements,id',
            'slt_commune' => 'required|exists:communes,id',
            'txt_descriptionPoint' => 'required|string|max:255',
            'txt_situationPoint' => 'required|string|max:255',
            'txt_estPoint' => 'required|string|max:255',
            'txt_nordPoint' => 'required|string|max:255',
            'txt_hauteurPoint' => 'required|string|max:255',
            'fichierPDFPoint' => $fichierNouveau ? 'nullable|file|mimes:pdf|max:102400' : 'nullable',
        ], [
            'txt_nomPoint.required' => 'Le nom du point est requis.',
            'txt_dateCreationPoint.required' => 'La date de création est requise.',
            'slt_departement.required' => 'Le département est requis.',
            'slt_arrondissement.required' => "L'arrondissement est requis.",
            'slt_commune.required' => 'La commune est requise.',
            'txt_descriptionPoint.required' => 'La description est requise.',
            'txt_situationPoint.required' => 'La situation est requise.',
            'txt_estPoint.required' => 'La coordonnée Est est requise.',
            'txt_nordPoint.required' => 'La coordonnée Nord est requise.',
            'txt_hauteurPoint.required' => 'La hauteur est requise.',
            'fichierPDFPoint.mimes' => 'Le document doit être un fichier PDF.',
        ]);
    }

    private function getPieChartData(): array
    {
        $rows = Arrivee::query()
            ->select('txt_categorie', DB::raw('count(*) as total'))
            ->groupBy('txt_categorie')
            ->get();

        if ($rows->isEmpty()) {
            return [
                'labels' => ['Aucun courrier'],
                'data' => [0],
                'colors' => ['#c6a15b'],
            ];
        }

        $colors = ['#a35b24', '#7a3e12', '#c6a15b', '#5c3a22', '#9b3a2f', '#2c1810'];

        return [
            'labels' => $rows->map(fn ($row) => $row->txt_categorie ?: 'Sans catégorie')->all(),
            'data' => $rows->map(fn ($row) => (int) $row->total)->all(),
            'colors' => $rows->values()->map(fn ($row, $index) => $colors[$index % count($colors)])->all(),
        ];
    }

    private function getBarChartData(): array
    {
        $labels = [];
        $arrivees = [];
        $departs = [];
        $cursor = now()->subMonths(5)->startOfMonth();

        for ($i = 0; $i < 6; $i++) {
            $labels[] = $cursor->translatedFormat('M');
            $arrivees[] = Arrivee::whereBetween('dt_datearrivee', [$cursor->copy()->startOfMonth(), $cursor->copy()->endOfMonth()])->count();
            $departs[] = Depart::whereBetween('dt_datecouriercd', [$cursor->copy()->startOfMonth(), $cursor->copy()->endOfMonth()])->count();
            $cursor->addMonth();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Arrivées',
                    'data' => $arrivees,
                    'backgroundColor' => 'rgba(163, 91, 36, 0.75)',
                    'borderColor' => '#a35b24',
                    'borderWidth' => 2,
                    'borderRadius' => 4,
                ],
                [
                    'label' => 'Départs',
                    'data' => $departs,
                    'backgroundColor' => 'rgba(198, 161, 91, 0.75)',
                    'borderColor' => '#c6a15b',
                    'borderWidth' => 2,
                    'borderRadius' => 4,
                ],
            ],
        ];
    }

    private function getStats(): array
    {
        $arrivees = Arrivee::count();
        $departs = Depart::count();
        $visites = Visite::count();
        $instances = Arrivee::query()
            ->leftJoin('departs', 'departs.txt_referencecourierarriveecd', '=', 'arrivees.txt_reference')
            ->where('arrivees.txt_categorie', 'Demande SERVICES')
            ->whereNull('departs.id')
            ->count();

        return [
            'courriers_arrivees' => $arrivees,
            'courriers_departs' => $departs,
            'visites' => $visites,
            'instances' => $instances,
            'dossiers_delimitation' => $arrivees,
            'dossiers_extrait_plan' => $departs,
            'dossiers_implantation' => $visites,
            'dossiers_terrain_enqueter' => $instances,
            'dossiers_pourcentage_terrain_enqueter' => $arrivees > 0 ? min(100, round(($instances / $arrivees) * 100, 1)) : 0,
            'total_lotissements' => Lotissement::count(),
            'total_utilisateurs' => User::count(),
        ];
    }

    private function getEvaluationUtilisateurs(?int $annee = null, ?int $trimestre = null): array
    {
        $annee = $annee && $annee > 2000 ? $annee : (int) now()->year;
        $trimestre = $trimestre && $trimestre >= 1 && $trimestre <= 4
            ? $trimestre
            : (int) ceil(now()->month / 3);

        $debut = Carbon::create($annee, (($trimestre - 1) * 3) + 1, 1)->startOfDay();
        $fin = $debut->copy()->addMonths(3)->subSecond();
        $label = ['1er', '2e', '3e', '4e'][$trimestre - 1].' trimestre '.$annee;

        $counts = collect();
        if (Schema::hasTable('activites_utilisateurs')) {
            $counts = ActiviteUtilisateur::query()
                ->select('user_id', 'action', DB::raw('COUNT(*) as total'))
                ->whereBetween('created_at', [$debut, $fin])
                ->groupBy('user_id', 'action')
                ->get()
                ->groupBy('user_id');
        }

        $lignes = User::query()->orderBy('name')->get(['id', 'name', 'role'])->map(function ($user) use ($counts, $label) {
            $byAction = collect($counts->get($user->id, []))->keyBy('action');
            $enregistrements = (int) ($byAction->get('enregistrement')->total ?? 0);
            $modifications = (int) ($byAction->get('modification')->total ?? 0);
            $recherches = (int) ($byAction->get('recherche')->total ?? 0);
            $score = ($enregistrements * 3) + ($modifications * 2);

            return [
                'user_id' => $user->id,
                'utilisateur' => $user->name,
                'role' => $user->role ?: '—',
                'trimestre' => $label,
                'enregistrements' => $enregistrements,
                'modifications' => $modifications,
                'recherches' => $recherches,
                'score' => $score,
                'appreciation' => match (true) {
                    $score >= 50 => 'Excellente',
                    $score >= 25 => 'Bonne',
                    $score >= 10 => 'Moyenne',
                    $score >= 1 => 'Faible',
                    default => 'Aucune activité',
                },
            ];
        })->sortByDesc('score')->values()->all();

        return [
            'annee' => $annee,
            'trimestre' => $trimestre,
            'label' => $label,
            'lignes' => $lignes,
        ];
    }

    public function getEvaluationUtilisateur(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'annee' => ['nullable', 'integer'],
            'trimestre' => ['nullable', 'integer', 'between:1,4'],
        ]);

        $annee = ! empty($validated['annee']) ? (int) $validated['annee'] : (int) now()->year;
        $trimestre = ! empty($validated['trimestre']) ? (int) $validated['trimestre'] : (int) ceil(now()->month / 3);
        $debut = Carbon::create($annee, (($trimestre - 1) * 3) + 1, 1)->startOfDay();
        $fin = $debut->copy()->addMonths(3)->subSecond();
        $label = ['1er', '2e', '3e', '4e'][$trimestre - 1].' trimestre '.$annee;

        $user = User::query()->findOrFail($validated['user_id']);
        $hasDetail = Schema::hasColumn('activites_utilisateurs', 'libelle');

        $base = ActiviteUtilisateur::query()
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$debut, $fin]);

        $totauxBruts = (clone $base)
            ->select('action', DB::raw('COUNT(*) as total'))
            ->groupBy('action')
            ->pluck('total', 'action');

        $total = (int) $totauxBruts->sum();
        $lignes = (clone $base)->orderByDesc('created_at')->limit(300)->get();

        $traitements = $lignes->map(function (ActiviteUtilisateur $activite) use ($hasDetail) {
            return [
                'date' => optional($activite->created_at)->format('d/m/Y H:i'),
                'action' => $activite->action,
                'module' => $hasDetail ? ($activite->module ?: null) : null,
                'libelle' => $hasDetail && $activite->libelle ? $activite->libelle : 'Traitement non détaillé',
                'reference' => $hasDetail ? ($activite->reference ?: null) : null,
                'chemin' => $hasDetail ? ($activite->chemin ?: null) : null,
            ];
        })->values();

        return response()->json([
            'user_id' => $user->id,
            'utilisateur' => $user->name,
            'role' => $user->role ?: '—',
            'trimestre' => $label,
            'totaux' => [
                'enregistrements' => (int) ($totauxBruts['enregistrement'] ?? 0),
                'modifications' => (int) ($totauxBruts['modification'] ?? 0),
                'suppressions' => (int) ($totauxBruts['suppression'] ?? 0),
                'recherches' => (int) ($totauxBruts['recherche'] ?? 0),
            ],
            'total' => $total,
            'tronque' => $total > $traitements->count(),
            'traitements' => $traitements,
        ]);
    }

    private function compterCourriersDuMois(int $month, int $year, string $designation): array
    {
        $total = Arrivee::query()
            ->whereMonth('dt_datearrivee', $month)
            ->whereYear('dt_datearrivee', $year)
            ->where('txt_designation', 'like', '%'.$designation.'%')
            ->count();

        return [
            'nombre_unitaire' => $total,
            'montant_total' => 0,
            'details' => $total > 0 ? [
                $designation => [
                    'type' => $designation,
                    'nombre' => $total,
                    'montant_total' => 0,
                    'montant_unitaire' => 0,
                ],
            ] : [],
        ];
    }

    private function getQuarterMonths(int $offset = 0): array
    {
        $start = now()->startOfQuarter()->addQuarters($offset);
        $names = [1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'];
        $months = [];

        for ($i = 0; $i < 3; $i++) {
            $date = $start->copy()->addMonths($i);
            $months[] = [
                'month' => $date->month,
                'year' => $date->year,
                'name' => $names[$date->month],
            ];
        }

        return $months;
    }

    private function codeValide(Request $request, int $id): bool
    {
        $request->validate(['code' => 'required|string']);

        return CodeAcces::correspond((string) $request->input('code'), $id);
    }

    private function compteProtege(User $user): bool
    {
        return strcasecmp(trim((string) $user->email), 'mbengueboubacar095@gmail.com') === 0;
    }
}

<?php

use App\Http\Controllers\ArriveeController;
use App\Http\Controllers\BaremeEvaluationController;
use App\Http\Controllers\CodeAccesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartController;
use App\Http\Controllers\DonneeController;
use App\Http\Controllers\DossierController;
use App\Http\Controllers\FichierController;
use App\Http\Controllers\GeometreController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MatriceCadastraleController;
use App\Http\Controllers\MutationController;
use App\Http\Controllers\ParametreController;
use App\Http\Controllers\SecretariatController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ShapefileController;
use App\Http\Controllers\TerrainController;
use App\Http\Controllers\InstancearriveeController;
use App\Http\Controllers\InstanceController;
use App\Http\Controllers\InstancedepartController;
use App\Http\Controllers\InstancevisiteController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TrimestreController;
use App\Http\Controllers\VisiteController;
use App\Models\Dossier;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;

 
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'validated'])
    ->name('dashboard');

Route::middleware(['auth', 'validated'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
 
Route::middleware(['auth', 'validated'])->group(function () {
    Route::get('/fichiers/{path}', [FichierController::class, 'show'])
        ->where('path', '.*')
        ->name('fichiers.show');

    Route::get('/arrivee/next/{annee}', [ArriveeController::class, 'getNextOrdre'])->where('annee', '[0-9]{4}');
    Route::get('/arrivee/editarrivee/{id}', [ArriveeController::class, 'editarrivee'])->whereNumber('id')->name('arrivee.editarrivee');
    Route::match(['put', 'post'], '/arrivee/update/{id}', [ArriveeController::class, 'update'])->whereNumber('id')->name('arrivee.update');
    Route::delete('/arrivee/delete-pdf/{id}', [ArriveeController::class, 'deletePdf'])->whereNumber('id')->name('arrivee.deletePdf');
    Route::get('/arrivee', [ArriveeController::class, 'create'])->name('arrivee.create');
    Route::post('/arrivee', [ArriveeController::class, 'store'])->name('arrivee.store');

    Route::get('/depart/next/{annee}', [DepartController::class, 'getNextOrdre'])->where('annee', '[0-9]{4}');
    Route::get('/depart/editdepart/{id}', [DepartController::class, 'editdepart'])->whereNumber('id')->name('depart.editdepart');
    Route::match(['put', 'post'], '/depart/update/{id}', [DepartController::class, 'update'])->whereNumber('id')->name('depart.update');
    Route::delete('/depart/delete-pdf/{id}', [DepartController::class, 'deletePdf'])->whereNumber('id')->name('depart.deletePdf');
    Route::get('/references-arrivee', [DepartController::class, 'fetchReferencesArrivee']);
    Route::post('/fetch-references-arrivee', [DepartController::class, 'fetchReferencesArrivee']);
    Route::get('/depart', [DepartController::class, 'create'])->name('depart.create');
    Route::post('/depart', [DepartController::class, 'store'])->name('depart.store');

    Route::get('/visite/next/{annee}', [VisiteController::class, 'getNextOrdre'])->where('annee', '[0-9]{4}');
    Route::get('/visite/editvisite/{id}', [VisiteController::class, 'editvisite'])->whereNumber('id')->name('visite.editvisite');
    Route::match(['put', 'post'], '/visite/update/{id}', [VisiteController::class, 'update'])->whereNumber('id')->name('visite.update');
    Route::get('/visite', [VisiteController::class, 'create'])->name('visite.create');
    Route::post('/visite', [VisiteController::class, 'store'])->name('visite.store');

    Route::get('/instance', [InstanceController::class, 'create'])->name('instance.create');
    Route::get('/instance/create', [InstanceController::class, 'create']);

    Route::delete('/instancearrivee/{id}', [InstancearriveeController::class, 'destroy'])->whereNumber('id')->name('instancearrivee.destroy');
    Route::get('/instancearrivee', [InstancearriveeController::class, 'create'])->name('instancearrivee.create');
    Route::get('/instancearrivee/create', [InstancearriveeController::class, 'create']);

    Route::delete('/instancedepart/{id}', [InstancedepartController::class, 'destroy'])->whereNumber('id')->name('instancedepart.destroy');
    Route::get('/instancedepart', [InstancedepartController::class, 'create'])->name('instancedepart.create');
    Route::get('/instancedepart/create', [InstancedepartController::class, 'create']);

    Route::delete('/instancevisite/{id}', [InstancevisiteController::class, 'destroy'])->whereNumber('id')->name('instancevisite.destroy');
    Route::get('/instancevisite', [InstancevisiteController::class, 'create'])->name('instancevisite.create');
    Route::get('/instancevisite/create', [InstancevisiteController::class, 'create']);

    Route::get('/trimestre', [TrimestreController::class, 'create'])->name('trimestre.create');
    Route::get('/trimestre/create', [TrimestreController::class, 'create']);

    Route::get('/messages/unread-total', [MessageController::class, 'unreadTotal']);
    Route::get('/messages/unread-by-user', [MessageController::class, 'unreadByUser']);
    Route::get('/messages/unread-per-user', [MessageController::class, 'unreadPerUser']);
    Route::get('/messages/{receiverId}', [MessageController::class, 'messages'])->whereNumber('receiverId');
    Route::get('/api/messages/{withUserId}', [MessageController::class, 'apiIndex'])->whereNumber('withUserId');
    Route::post('/message/markRead/{senderId}', [MessageController::class, 'markRead'])->whereNumber('senderId')->name('messages.markRead');
    Route::post('/message/markReceived/{senderId}', [MessageController::class, 'markReceived'])->whereNumber('senderId');
    Route::get('/message/create', [MessageController::class, 'create'])->name('message.create');
    Route::get('/message', [MessageController::class, 'index'])->name('message.index');
    Route::post('/message', [MessageController::class, 'store'])->name('message.store');
    Route::put('/message/{id}', [MessageController::class, 'update'])->whereNumber('id')->name('message.update');
    Route::delete('/message/{id}', [MessageController::class, 'destroy'])->whereNumber('id')->name('message.destroy');

    Route::get('/departements/{region}', [LocationController::class, 'getDepartementsByRegion'])->whereNumber('region');
    Route::get('/arrondissements/{departement}', [LocationController::class, 'getArrondissementByDepartement'])->whereNumber('departement');
    Route::get('/communes/{arrondissement}', [LocationController::class, 'getCommunesByArrondissement'])->whereNumber('arrondissement');
    Route::get('/lotissements/{id}', [LocationController::class, 'getLotissementsByCommune'])->whereNumber('id');
    Route::get('/dossier/next', [DossierController::class, 'getNextDossier']);
    Route::get('/dossier/last', function () {
        $lastDossier = Dossier::latest('id')->first();

        return response()->json([
            'num_dossier' => $lastDossier ? $lastDossier->txt_num_dossier : '',
        ]);
    });

    Route::get('/secretariat/edit/{id}', [SecretariatController::class, 'edit'])->whereNumber('id')->name('secretariat.edit');
    Route::get('/secretariat/dernier-ordre/{annee}', [SecretariatController::class, 'dernierOrdre'])->where('annee', '[0-9]{4}');
    Route::get('/secretariat', [SecretariatController::class, 'create'])->name('secretariat.create');
    Route::post('/secretariat', [SecretariatController::class, 'store'])->name('secretariat.store');
    Route::put('/secretariat/{id}', [SecretariatController::class, 'update'])->whereNumber('id')->name('secretariat.update');
    Route::post('/secretariat/{terrain}/upload-pdf', [SecretariatController::class, 'uploadPDF'])->whereNumber('terrain')->name('secretariat.uploadPDF');
    Route::post('/dossier/verifyDossier', [SecretariatController::class, 'verifyDossier'])->name('dossier.verifyDossier');

    Route::get('/geometre/create', [GeometreController::class, 'create'])->name('geometre.create');
    Route::post('/geometre', [GeometreController::class, 'store'])->name('geometre.store');
    Route::post('/dossier/verificationEnquete', [GeometreController::class, 'verify'])->name('dossier.verify');
    Route::get('/verifier-nicad/{nicad}', [GeometreController::class, 'verifierNicad']);

    Route::get('/donnee/create', [DonneeController::class, 'create'])->name('donnee.create');
    Route::get('/donnees', [DonneeController::class, 'create'])->name('donnees.create');
    Route::delete('/terrains/{id}', [TerrainController::class, 'destroyTerrain'])->whereNumber('id')->name('terrain.destroy');
    Route::post('/terrain/delete/{id}', [TerrainController::class, 'destroyTerrain'])->whereNumber('id')->name('terrain.destroyTerrain');
    Route::post('/terrain/search', [TerrainController::class, 'searchTerrain']);

    Route::get('/matriceCadastrale/create', [MatriceCadastraleController::class, 'create'])->name('matriceCadastrale.create');
    Route::get('/matrice', [MatriceCadastraleController::class, 'create'])->name('matrice.create');
    Route::delete('/matrice/{id}', [MatriceCadastraleController::class, 'destroy'])->whereNumber('id')->name('matrice.destroy');

    Route::get('/mutation', [MutationController::class, 'index'])->name('mutation.index');
    Route::get('/mutation/create', [MutationController::class, 'create'])->name('mutation.create');
    Route::post('/mutation/store', [MutationController::class, 'store'])->name('mutation.store');
    Route::put('/mutation/{id}', [MutationController::class, 'update'])->whereNumber('id')->name('mutation.update');
    Route::post('/fetchReferencesLotissement', [MutationController::class, 'fetchReferencesLotissement']);
    Route::post('/dossier/verifyLotissement', [MutationController::class, 'verifyLotissement'])->name('dossier.verifyLotissement');

    Route::get('/lotissement', [ParametreController::class, 'index'])->name('lotissement.index');

    Route::get('/plan/create', [ShapefileController::class, 'index'])->name('plan.create');
    Route::get('/shapefiles', [ShapefileController::class, 'list'])->name('shapefiles.list');
    Route::post('/shapefiles', [ShapefileController::class, 'store'])->name('shapefiles.store');
    Route::patch('/shapefiles/{shapefile}', [ShapefileController::class, 'update'])->whereNumber('shapefile')->name('shapefiles.update');
    Route::delete('/shapefiles/{shapefile}', [ShapefileController::class, 'destroy'])->whereNumber('shapefile')->name('shapefiles.destroy');
    Route::get('/api/shapefiles/{shapefile}/geojson', [ShapefileController::class, 'geojson'])->whereNumber('shapefile')->name('shapefiles.geojson');
    Route::post('/api/plan/previsualiser', [ShapefileController::class, 'previsualiser'])->name('plan.previsualiser');
    Route::post('/api/plan/parcelles', [TerrainController::class, 'consulterParcelles'])->name('plan.parcelles');
    Route::post('/generer-angles', [ShapefileController::class, 'genererAngles'])->name('generer.angles');
    Route::delete('/pointDensifier/destroy/{id}', [ParametreController::class, 'destroyPoint'])->whereNumber('id')->middleware('role:admin');

    Route::post('/code/verify', [CodeAccesController::class, 'verify'])->middleware('throttle:8,1')->name('code.verify');
});

Route::middleware(['auth', 'validated', 'role:admin'])->group(function () {
    Route::get('/departements', [ParametreController::class, 'getDepartements']);
    Route::get('/arrondissements/{departement}', [ParametreController::class, 'getArrondissementByDepartement']);
    Route::get('/communes/{arrondissement}', [ParametreController::class, 'getCommunesByArrondissement']);

    Route::prefix('parametre')->group(function () {
        Route::get('/admin', [ParametreController::class, 'admin'])->name('parametre.admin');
        Route::get('/user', [ParametreController::class, 'user'])->name('parametre.user');
        Route::post('/admin/store', [ParametreController::class, 'storeUser'])->name('parametre.admin.store');
        Route::post('/admin/{id}/validate', [ParametreController::class, 'validateUser'])->name('parametre.admin.validate');
        Route::delete('/admin/{id}', [ParametreController::class, 'destroyUser'])->name('parametre.admin.destroy');
        Route::get('/adminEdit/{id}', [ParametreController::class, 'editUser'])->name('parametre.admin.edit');
        Route::put('/admin/update/{id}', [ParametreController::class, 'updateUser'])->name('parametre.updateUser');

        Route::get('/lotissement/index', [ParametreController::class, 'index'])->name('parametre.lotissement.index');
        Route::post('/store', [ParametreController::class, 'store'])->name('parametre.store');
        Route::get('/lotissement/create', [ParametreController::class, 'create'])->name('parametre.lotissement.create');
        Route::get('/lotissement/edit/{id}', [ParametreController::class, 'edit'])->name('parametre.lotissement.edit');
        Route::match(['put', 'post'], '/lotissement/update/{id}', [ParametreController::class, 'update'])->name('parametre.lotissement.update');
        Route::delete('/lotissement/destroy/{id}', [ParametreController::class, 'destroy'])->name('parametre.lotissement.destroy');

        Route::get('/pointDensifier/createPoint', [ParametreController::class, 'createPoint'])->name('parametre.pointDensifier.createPoint');
        Route::post('/pointDensifier/storePoint', [ParametreController::class, 'storePoint'])->name('parametre.pointDensifier.storePoint');
        Route::get('/pointDensifier/edit/{id}', [ParametreController::class, 'editPoint'])->name('parametre.pointDensifier.editPoint');
        Route::match(['put', 'post'], '/pointDensifier/updatePoint/{id}', [ParametreController::class, 'updatePoint'])->name('parametre.pointDensifier.updatePoint');
        Route::delete('/pointDensifier/destroy/{id}', [ParametreController::class, 'destroyPoint'])->name('parametre.pointDensifier.destroy');

        Route::get('/chart', [ParametreController::class, 'chart'])->name('parametre.chart');

        Route::get('/service/create', [ServiceController::class, 'create'])->name('parametre.service.create');
        Route::post('/service', [ServiceController::class, 'store'])->name('parametre.service.store');
        Route::get('/service/edit/{service}', [ServiceController::class, 'edit'])->name('parametre.service.edit');
        Route::match(['put', 'post'], '/service/{service}', [ServiceController::class, 'update'])->name('parametre.service.update');
        Route::delete('/service/{service}', [ServiceController::class, 'destroy'])->name('parametre.service.destroy');

        Route::get('/trimestre/create', [TrimestreController::class, 'parametre'])->name('parametre.trimestre.create');

        Route::get('/bareme', [BaremeEvaluationController::class, 'index'])->name('parametre.bareme.index');
        Route::post('/bareme', [BaremeEvaluationController::class, 'store'])->name('parametre.bareme.store');
        Route::put('/bareme/{bareme}', [BaremeEvaluationController::class, 'update'])->name('parametre.bareme.update');
        Route::delete('/bareme/{bareme}', [BaremeEvaluationController::class, 'destroy'])->name('parametre.bareme.destroy');

        Route::post('/code/verify-lotissement', [CodeAccesController::class, 'verify'])->middleware('throttle:8,1')->name('code.verifyLotissement');
        Route::post('/code/verify-point', [CodeAccesController::class, 'verify'])->middleware('throttle:8,1')->name('code.verifyPoint');

        Route::get('/mutationEdit/{id}', [ParametreController::class, 'editMutation'])->whereNumber('id')->name('parametre.mutation.edit');
        Route::delete('/mutation/destroy/{id}', [ParametreController::class, 'destroyMutation'])->whereNumber('id')->name('parametre.mutation.destroy');
    });

    Route::get('/api/chart-data', [ParametreController::class, 'getChartData'])->name('api.chart.data');
    Route::get('/api/evaluation-utilisateur', [ParametreController::class, 'getEvaluationUtilisateur'])->name('api.evaluation.utilisateur');
    Route::get('/api/stats', [ParametreController::class, 'getStatsData'])->name('api.stats');
    Route::get('/api/cession-data', [ParametreController::class, 'getCessionData'])->name('api.cession.data');
});

require __DIR__ . '/auth.php';

<?php

use App\Http\Controllers\ArriveeController;
use App\Http\Controllers\DepartController;
use App\Http\Controllers\InstancearriveeController;
use App\Http\Controllers\InstanceController; 
use App\Http\Controllers\Auth\AuthenticatedSessionController; 
use App\Http\Controllers\InstancedepartController;
use App\Http\Controllers\InstancevisiteController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TrimestreController;
use App\Http\Controllers\VisiteController;
use Illuminate\Container\Attributes\Auth; 
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

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::post('/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');



Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
 
//courierNouvelle appli
Route::resource('arrivee', ArriveeController::class);
Route::resource('depart', DepartController::class);
Route::resource('instance', InstanceController::class);
Route::resource('/instancearrivee', InstancearriveeController::class);
Route::resource('/instancedepart', InstancedepartController::class);
Route::resource('/trimestre', TrimestreController::class);
Route::resource('/message', MessageController::class); 
Route::resource('/visite', VisiteController::class);  
Route::get('/arrivee', [ArriveeController::class, 'create'])->name('arrivee.create');
Route::get('/depart', [DepartController::class, 'create'])->name('depart.create');
Route::get('/instance', [InstanceController::class, 'create'])->name('instance.create'); 
Route::get('/trimestre', [TrimestreController::class, 'create'])->name('trimestre.create'); 
Route::get('/instancearrivee',[InstancearriveeController::class,'create'])->name('instancearrivee.create');
Route::get('/instancedepart',[InstancedepartController::class,'create'])->name('instancedepart.create'); 
Route::get('/instancevisite',[InstancevisiteController::class,'create'])->name('instancevisite.create');
  
Route::get('/arrivee/next/{annee}', [ArriveeController::class, 'getNextOrdre']);  
Route::get('/depart/next/{annee}', [DepartController::class, 'getNextOrdre']);  
Route::get('/visite/next/{annee}', [visiteController::class, 'getNextOrdre']);   
 
Route::get('/references-arrivee', [DepartController::class, 'fetchReferencesArrivee']);  
Route::post('/fetch-references-arrivee', [DepartController::class, 'fetchReferencesArrivee']); 
 
Route::post('/arrivee',[ArriveeController::class, 'store'])->name('arrivee.store');
Route::post('/depart',[DepartController::class, 'store'])->name('depart.store'); 
Route::post('/visite',[VisiteController::class, 'store'])->name('visite.store');

Route::get('/instance/create', [InstanceController::class, 'create'])->name('instance.create');   
Route::get('/instancearrivee/create', [InstancearriveeController::class, 'create'])->name('instancearrivee.create'); 
Route::get('/instancedepart/create', [InstancedepartController::class, 'create'])->name('instancedepart.create');
Route::get('/instancevisite/create', [InstancevisiteController::class, 'create'])->name('instancevisite.create');
Route::get('/trimestre/create', [TrimestreController::class, 'create'])->name('trimestre.create');
 
Route::get('/count-morcellements', [TrimestreController::class, 'countMorcellementsThisQuarter']);
 
Route::delete('/instance/arrivee/{id}', [InstanceController::class, 'destroy'])->name('instance.destroyArrivee');
Route::delete('/instance/depart/{id}', [InstanceController::class, 'destroy'])->name('instance.destroyDepart');  
Route::delete('/instance/visite/{id}', [VisiteController::class, 'destroy'])->name('visite.destroyDepart'); 

Route::delete('/instancearrivee/arrivee/{id}', [InstancearriveeController::class, 'destroy'])->name('instancearrivee.destroyArrivee');
Route::delete('/instancedepart/depart/{id}', [InstancedepartController::class, 'destroy'])->name('instancedepart.destroyDepart');  
Route::delete('/instancevisite/visite/{id}', [InstanceVisiteController::class, 'destroy'])->name('instancevisite.destroy'); 
Route::get('/instancearrivee/arrivee/{txt_numdordre}', [InstancearriveeController::class, 'show'])->name('instancearrivee.show');

Route::get('/arrivee/editarrivee/{id}', [ArriveeController::class, 'editarrivee'])->name('arrivee.editarrivee');
Route::get('/depart/editdepart/{id}', [DepartController::class, 'editdepart'])->name('depart.editdepart');
Route::delete('/depart/delete-pdf/{id}', [DepartController::class, 'deletePdf'])->name('depart.deletePdf');
Route::delete('/arrivee/delete-pdf/{id}', [ArriveeController::class, 'deletePdf'])->name('arrivee.deletePdf');
 
Route::put('/arrivee/update/{id}', [ArriveeController::class, 'update'])->name('arrivee.update');  
Route::put('/depart/update/{id}', [DepartController::class, 'update'])->name('depart.update'); 
 
Route::get('/message/create', [MessageController::class, 'create'])->name('message.create');

Route::get('/message', [MessageController::class, 'index']);
Route::post('/message', [MessageController::class, 'store']);

Route::get('/message', [MessageController::class, 'index'])->middleware('auth');
Route::post('/message', [MessageController::class, 'store'])->middleware('auth');
 
Route::get('/api/messages/{withUserId}', [MessageController::class, 'apiIndex'])->middleware('auth');
Route::get('/message/{id}', [MessageController::class, 'show'])->name('message.show');

Route::middleware('auth')->group(function () {
    Route::get('/messages/unread-total', [MessageController::class, 'unreadTotal']);
    Route::get('/messages/unread-by-user', [MessageController::class, 'unreadByUser']);
});
 
Route::post('/message/markRead/{senderId}', [MessageController::class, 'markRead'])->name('messages.markRead');
 
Route::get('/api/messages/{id}', [MessageController::class, 'fetchMessages']); 
Route::post('/message', [MessageController::class, 'store']);
Route::post('/message/markReceived/{senderId}', [MessageController::class, 'markReceived']);
Route::middleware('auth:sanctum')->get('/messages/{receiverId}', [MessageController::class, 'messages']);
Route::get('/messages/unread-per-user', [MessageController::class, 'unreadPerUser'])->middleware('auth:sanctum');
Route::delete('/message/{id}', [MessageController::class, 'destroy'])->middleware('auth');
Route::put('/message/{id}', [MessageController::class, 'update'])->middleware('auth');
  
 
 
require __DIR__ . '/auth.php';

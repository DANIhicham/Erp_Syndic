<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BudgetController;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

// Affichage du formulaire (GET)
Route::get('/test', function () {
    return view('example'); 
})->name('test.form');

//Page login
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
// Traitement du formulaire (POST)
Route::post('/test-submit', function () {
    // simulation d’un traitement
    sleep(2);
    return redirect()->route('test.form')->with('success', 'Formulaire envoyé !');
})->name('test.submit');



// Partie Syndic ERP
Route::get('/dashboard_syndic', function () {
    return view('syndic.dashboard_syndic'); 
})->name('dashboard_syndic');

Route::get('/cotisations', function () {
    return view('syndic.cotisations'); 
})->name('cotisations');

Route::get('/charges_depenses', function () {
    return view('syndic.depenses'); 
})->name('depenses');

Route::get('/budget_annuel', [BudgetController::class, 'index'])->name('budget.index');
Route::post('/budget_annuel', [BudgetController::class, 'store'])->name('budget.store');

Route::get('/coproprietaires', function () {
    return view('syndic.coproprietaires');  
})->name('Coproprietaires');

Route::get('/reclamations', function () {
    return view('syndic.reclamations');  
})->name('Reclamations');
//fin partie synic


Route::get('/voir', function () {
    return view('voir_appartement'); 
})->name('voir_appartement');

Route::get('/contrats', function () {
    return view('contrats');
})->name('contrats');

Route::get('/paiements_loyers', function () {
    return view('paiements_loyers');
})->name('paiements_loyers');

Route::get('/charges', function () {
    return view('pages.charges');
})->name('charges');

//partie resident

Route::get('/resident_dashboard', function () {
    return view('resident.resident_dash');
})->name('resident_dash');

//-----------------------IMPORT USERS-----------------------------------------------

// use Maatwebsite\Excel\Facades\Excel;
// use App\Imports\UsersImport;

// Route::get('/import-users', function () {
//     Excel::import(new UsersImport, public_path('users.xlsx'));

//     return "Import terminé";
// });

//-----------------------IMPORT APPARTEMENTS----------------------------------------

// use App\Imports\AppartementsImport;
// use Maatwebsite\Excel\Facades\Excel;

// Route::get('/import-appartements', function () {
//     Excel::import(new AppartementsImport, public_path('appartements.xlsx'));

//     return "Import appartements terminé";
// });

Route::post('/set-residence', function (Request $request) {
    session(['residence_id' => $request->residence_id]);
    return back();
})->name('set.residence');
require __DIR__.'/auth.php';
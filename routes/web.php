<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CotisationController;
use App\Http\Controllers\CoproprietaireController;
use App\Http\Controllers\ReclamationController;
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


Route::get('/charges_depenses', function () {
    return view('syndic.depenses'); 
})->name('depenses');

Route::get('/budget_annuel', [BudgetController::class, 'index'])->name('budget.index');
Route::post('/budget_annuel', [BudgetController::class, 'store'])->name('budget.store');


// 📊 Liste cotisations   
Route::get('/cotisations', [CotisationController::class, 'index'])
->name('cotisations.index');

// 💰 Enregistrer paiement
 Route::post('/cotisations/payer', [CotisationController::class, 'payer'])
->name('cotisations.payer');

// 📜 Historique (AJAX modal)
Route::get('/cotisations/historique/{appartement}', [CotisationController::class, 'historique'])
->name('cotisations.historique');

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


Route::prefix('syndic')->name('syndic.')->middleware(['auth'])->group(function () {
 
    // ── Cotisations ──────────────────────────────────────────────
    Route::prefix('cotisations')->name('cotisations.')->group(function () {
 
        // Page principale (avec filtres GET)
        Route::get('/',         [CotisationController::class, 'index'])            ->name('index');
 
        // Enregistrer un paiement (POST + upload fichier)
        Route::post('/payer',   [CotisationController::class, 'payer'])            ->name('payer');
 
        // Historique des transactions d'un appartement (AJAX/JSON)
        Route::get('/historique/{appartement}', [CotisationController::class, 'historique']) ->name('historique');
 
        // Supprimer une transaction
        Route::delete('/transaction/{id}',      [CotisationController::class, 'supprimerTransaction']) ->name('supprimer');
 
        // Export CSV
        Route::get('/export-csv',  [CotisationController::class, 'exportCsv'])    ->name('export');
 
        // Relancer un propriétaire par email
        Route::post('/relancer/{appartement}',  [CotisationController::class, 'relancer'])   ->name('relancer');
        //telechargement de recu
        Route::get('/recu/{transaction}',[CotisationController::class, 'recu'])  ->name('recu');

    });
 
});


// Liste des copropriétaires
Route::get('/coproprietaires', [CoproprietaireController::class, 'index'])
->name('coproprietaires.index');

Route::post('/set-residence', function (Request $request) {
    session(['residence_id' => $request->residence_id]);
    return back();
})->name('set.residence');

// RECLAMATIONS

Route::get('/reclamations', [ReclamationController::class, 'index'])
    ->name('reclamations.index');

Route::post('/reclamations/store', [ReclamationController::class, 'store'])
    ->name('reclamations.store');

Route::put('/reclamations/{id}/status', [ReclamationController::class, 'updateStatus'])
    ->name('reclamations.updateStatus');

require __DIR__.'/auth.php';
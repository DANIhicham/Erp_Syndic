<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CotisationController;
use App\Http\Controllers\CoproprietaireController;
use App\Http\Controllers\ReclamationController;
use App\Http\Controllers\DepenseResidenceController;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

//Page login
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
// Traitement du formulaire (POST)
Route::post('/test-submit', function () {
    // simulation d’un traitement
    sleep(2);
    return redirect()->route('test.form')->with('success', 'Formulaire envoyé !');
})->name('test.submit');


//-------------------------------------------
// PARTIE SYNDIC ERP
// -------------------------------------------




Route::middleware(['auth', 'role:syndic'])->group(function () {
    
    Route::post('/set-residence', function (Request $request) {
    session(['residence_id' => $request->residence_id]);
    return back();
    })->name('set.residence');

    Route::get('/dashboard_syndic', function () {
        return view('syndic.dashboard_syndic'); 
    })->name('dashboard_syndic');

    // ── Cotisations ──────────────────────────────────────────────
    Route::prefix('cotisations')->name('syndic.cotisations.')->group(function () {
 
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
 
    
    Route::get('/budget_annuel', [BudgetController::class, 'index'])->name('budget.index');
    Route::post('/budget_annuel', [BudgetController::class, 'store'])->name('budget.store');


    // Liste des copropriétaires
    Route::get('/coproprietaires', [CoproprietaireController::class, 'index'])
    ->name('coproprietaires.index');

    Route::prefix('depenses')->name('depenses.')->group(function () {

        Route::get('/', [DepenseresidenceController::class, 'index'])
            ->name('index');

        Route::post('/', [DepenseresidenceController::class, 'store'])
            ->name('store');

        Route::put('/{depense}', [DepenseresidenceController::class, 'update'])
            ->name('update');

        Route::delete('/{depense}', [DepenseresidenceController::class, 'destroy'])
            ->name('destroy');

        Route::post('/paiements/{paiement}/payer', [DepenseresidenceController::class, 'payer'])
            ->name('payer');

        Route::get('/export/csv', [DepenseresidenceController::class, 'exportCsv'])
            ->name('export.csv');
    });

    // RECLAMATIONS

    Route::get('/reclamations', [ReclamationController::class, 'index'])
        ->name('reclamations.index');

    Route::post('/reclamations/store', [ReclamationController::class, 'store'])
        ->name('reclamations.store');

    Route::put('/reclamations/{id}/status', [ReclamationController::class, 'updateStatus'])
        ->name('reclamations.updateStatus');

});



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








Route::get('/contrats', function () {
    return view('contrats');
})->name('contrats');

Route::get('/paiements_loyers', function () {
    return view('paiements_loyers');
})->name('paiements_loyers');

require __DIR__.'/auth.php';
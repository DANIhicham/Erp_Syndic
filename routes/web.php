<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Affichage du formulaire (GET)
Route::get('/test', function () {
    return view('example'); // ta vue Blade avec le loader
})->name('test.form');

//Page login
Route::get('/login', function () {
    return view('login'); // ta vue Blade avec le loader
})->name('test.form');

// Traitement du formulaire (POST)
Route::post('/test-submit', function () {
    // simulation d’un traitement
    sleep(2);
    return redirect()->route('test.form')->with('success', 'Formulaire envoyé !');
})->name('test.submit');



// Partie Syndic ERP
Route::get('/dashboard_syndic', function () {
    return view('syndic.dashboard_syndic'); // correspond à resources/views/dashboard.blade.php
})->name('dashboard_syndic');

Route::get('/cotisations', function () {
    return view('syndic.cotisations'); 
})->name('cotisations');

Route::get('/charges_depenses', function () {
    return view('syndic.depenses'); 
})->name('depenses');

Route::get('/budget_annuel', function () {
    return view('syndic.budget'); 
})->name('budget');
 
Route::get('/coproprietaires', function () {
    return view('syndic.coproprietaires');  
})->name('Coproprietaires');

Route::get('/reclamations', function () {
    return view('syndic.reclamations');  
})->name('Reclamations');


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

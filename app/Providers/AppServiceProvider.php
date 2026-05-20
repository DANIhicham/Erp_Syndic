<?php

namespace App\Providers;

use App\Models\Residence;
use App\Models\Reclamation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Résidences globales
        View::share('residences', Residence::all());

        // Réclamations ouvertes globales
        View::composer('*', function ($view) {

            $nbReclamationsOuvertes = 0;

            if (session('residence_id')) {

                $nbReclamationsOuvertes = Reclamation::whereHas('appartement', function ($q) {

                    $q->where('residence_id', session('residence_id'));

                })
                ->where('statut', 'ouverte')
                ->count();
            }

            $view->with('nbReclamationsOuvertes', $nbReclamationsOuvertes);
        });
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetAnnuelResume extends Model
{
    protected $table = 'budget_annuel_resume';
    protected $fillable = ['residence_id', 'annee', 'total_cotisations', 'total_depenses', 'solde'];

    // Méthode pour recalculer le bilan (à appeler via un job ou un bouton)
    public static function refreshBilan($residenceId, $annee)
    {
        $cotisations = PaiementCotisation::whereHas('appartement', function($q) use ($residenceId) {
            $q->where('residence_id', $residenceId);
        })->where('annee_concernee', $annee)->sum('montant_paye');

        $depenses = EcheanceDepense::whereHas('depense', function($q) use ($residenceId, $annee) {
            $q->where('residence_id', $residenceId)->where('annee', $annee);
        })->where('statut', 'payé')->sum('montant');

        return self::updateOrCreate(
            ['residence_id' => $residenceId, 'annee' => $annee],
            [
                'total_cotisations' => $cotisations,
                'total_depenses' => $depenses,
                'solde' => $cotisations - $depenses
            ]
        );
    }
}

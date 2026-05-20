<?php

namespace App\Console\Commands;

use App\Models\PaiementCotisation;
use App\Models\ConfigurationBudget;
use App\Models\Appartement;
use App\Models\TransactionPaiement;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateCotisationsMontant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:update_cotisations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mettre à jour les montants et statuts des cotisations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cotisations = PaiementCotisation::all();

        foreach ($cotisations as $cotisation) {

            $appartement = Appartement::find($cotisation->appartement_id);

            // Ignorer résidence ID = 1
            if (!$appartement || $appartement->residence_id == 1) {
                continue;
            }

            $budget = ConfigurationBudget::where('residence_id', $appartement->residence_id)
                ->where('annee', $cotisation->annee_concernee)
                ->first();

            if (!$budget) {

                $this->warn(
                    "Budget manquant pour appartement {$appartement->id}"
                );

                continue;
            }

            $montantAnnuel = (float) $budget->montant_annuel_fixe;

            $dateSignature = Carbon::parse($appartement->date_signature_contrat);
            $anneeDebut = $dateSignature->year;

            // ─────────────────────────────────────
            // PRORATA
            // ─────────────────────────────────────

            if ($cotisation->annee_concernee == $anneeDebut) {

                $debut = $dateSignature->copy();
                $fin = Carbon::create($anneeDebut, 12, 31);

                $joursRestants = $debut->diffInDays($fin) + 1;
                $joursAnnee = $fin->dayOfYear;

                $montant = $joursAnnee > 0
                    ? ($montantAnnuel / $joursAnnee) * $joursRestants
                    : 0;

            } else {

                $montant = $montantAnnuel;
            }

            $montantAttendu = round($montant, 2);

            // ─────────────────────────────────────
            // TOTAL PAYÉ
            // ─────────────────────────────────────

            $montantPaye = (float) TransactionPaiement::where(
                'appartement_id',
                $appartement->id
            )
                ->where('annee', $cotisation->annee_concernee)
                ->sum('montant');

            // ─────────────────────────────────────
            // CALCUL COUVERTURE
            // ─────────────────────────────────────

            $dateDebut = $cotisation->annee_concernee == $anneeDebut
                ? $dateSignature->copy()
                : Carbon::create($cotisation->annee_concernee, 1, 1);

            $dateFinTheorique = Carbon::create(
                $cotisation->annee_concernee,
                12,
                31
            );

            $joursPeriode = $dateDebut->diffInDays($dateFinTheorique) + 1;

            $coutJournalier = $joursPeriode > 0
                ? $montantAttendu / $joursPeriode
                : 0;

            $joursCouverts = $coutJournalier > 0
                ? floor($montantPaye / $coutJournalier)
                : 0;

            $dateEcheance = null;

            if ($joursCouverts > 0) {

                $dateEcheance = $dateDebut->copy()
                    ->addDays($joursCouverts - 1);

                if ($dateEcheance->gt($dateFinTheorique)) {
                    $dateEcheance = $dateFinTheorique->copy();
                }
            }

            // ─────────────────────────────────────
            // CALCUL STATUT
            // ─────────────────────────────────────

            if (
                $montantPaye >= $montantAttendu &&
                $montantAttendu > 0
            ) {

                $statut = 'payé';

            } elseif ($montantPaye <= 0) {

                $statut = 'en_retard';

            } elseif ($dateEcheance && now()->lte($dateEcheance)) {

                $statut = 'partiel';

            } else {

                $statut = 'en_retard';
            }

            // ─────────────────────────────────────
            // UPDATE
            // ─────────────────────────────────────

            $cotisation->update([
                'montant_attendu' => $montantAttendu,
                'montant_paye'    => $montantPaye,
                'statut'          => $statut,
            ]);
        }

        $this->info('Cotisations mises à jour avec succès ✅');
    }
}
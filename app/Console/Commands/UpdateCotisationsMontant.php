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
     **/

    public function handle()
        {
            $residenceId = $this->argument('residence_id');
            $anneeActuelle = now()->year;

            $appartementsQuery = Appartement::query();

            if ($residenceId) {
                $appartementsQuery->where('residence_id', $residenceId);
            }

            $appartements = $appartementsQuery->get();

            foreach ($appartements as $appartement) {

                if (!$appartement->date_signature_contrat) {
                    continue;
                }

                $dateSignature = Carbon::parse($appartement->date_signature_contrat);
                $anneeDebut = $dateSignature->year;

                // ─────────────────────────────
                // 1. CREATE SI MANQUANT
                // ─────────────────────────────

                for ($annee = $anneeDebut; $annee <= $anneeActuelle; $annee++) {

                    $cotisation = PaiementCotisation::firstOrNew([
                        'appartement_id'   => $appartement->id,
                        'annee_concernee'  => $annee,
                    ]);

                    $budget = ConfigurationBudget::where('residence_id', $appartement->residence_id)
                        ->where('annee', $annee)
                        ->first();

                    $montantAnnuel = $budget
                        ? (float) $budget->montant_annuel_fixe
                        : 0;

                    // PRORATA
                    if ($annee == $anneeDebut) {

                        $debut = $dateSignature->copy();
                        $fin = Carbon::create($annee, 12, 31);

                        $joursRestants = $debut->diffInDays($fin) + 1;
                        $joursAnnee = $fin->dayOfYear;

                        $montantAttendu = $joursAnnee > 0
                            ? ($montantAnnuel / $joursAnnee) * $joursRestants
                            : 0;

                    } else {
                        $montantAttendu = $montantAnnuel;
                    }

                    $montantAttendu = round($montantAttendu, 2);

                    // ─────────────────────────────
                    // 2. CALCUL PAIEMENTS
                    // ─────────────────────────────

                    $montantPaye = (float) TransactionPaiement::where(
                        'appartement_id',
                        $appartement->id
                    )
                    ->where('annee', $annee)
                    ->sum('montant');

                    // ─────────────────────────────
                    // 3. STATUT
                    // ─────────────────────────────

                    if ($montantPaye >= $montantAttendu && $montantAttendu > 0) {
                        $statut = 'payé';
                    } elseif ($montantPaye <= 0) {
                        $statut = 'en_retard';
                    } else {
                        $statut = 'partiel';
                    }

                    // ─────────────────────────────
                    // 4. USER (résident lié appartement)
                    // ─────────────────────────────

                    $userId = $appartement->proprietaire_id; 
                    // ou relation future si tu changes

                    // ─────────────────────────────
                    // 5. SAVE
                    // ─────────────────────────────

                    $cotisation->fill([
                        'user_id'          => $userId,
                        'montant_attendu'  => $montantAttendu,
                        'montant_paye'     => $montantPaye,
                        'statut'           => $statut,
                        'commentaire'      => 'Synchronisation automatique',
                    ]);

                    $cotisation->save();
                }
            }

            $this->info('Synchronisation cotisations terminée ✅');
        }
}
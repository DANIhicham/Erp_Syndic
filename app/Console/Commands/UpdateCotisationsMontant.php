<?php

namespace App\Console\Commands;
use App\Models\PaiementCotisation;
use App\Models\ConfigurationBudget;
use App\Models\Appartement;
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
    protected $description = 'Command Update Cotisations Montant';

    /**
     * Execute the console command.
     */

        public function handle()
        {
            $cotisations = PaiementCotisation::all();

            foreach ($cotisations as $cotisation) {

                $appartement = Appartement::find($cotisation->appartement_id);
                if (!$appartement) continue;

                $budget = ConfigurationBudget::where('residence_id', $appartement->residence_id)
                    ->where('annee', $cotisation->annee_concernee)
                    ->first();

                if (!$budget) {
                    $this->warn("Budget manquant pour appart {$appartement->id}");
                    continue;
                }

                $montantAnnuel = $budget->montant_annuel_fixe;

                $dateSignature = Carbon::parse($appartement->date_signature_contrat);
                $anneeDebut = $dateSignature->year;

                // 🔥 PRORATA SI 1ère année
                if ($cotisation->annee_concernee == $anneeDebut) {

                    $debut = $dateSignature->copy();
                    $fin = Carbon::create($anneeDebut, 12, 31);

                    $joursRestants = $debut->diffInDays($fin) + 1;
                    $joursAnnee = $fin->dayOfYear;

                    $montant = ($montantAnnuel / $joursAnnee) * $joursRestants;

                } else {
                    $montant = $montantAnnuel;
                }

                $cotisation->montant_attendu = round($montant, 2);

                // 🔄 recalcul statut
                if ($cotisation->montant_paye >= $cotisation->montant_attendu) {
                    $cotisation->statut = 'payé';
                } elseif ($cotisation->montant_paye > 0) {
                    $cotisation->statut = 'partiel';
                } else {
                    $cotisation->statut = 'en_retard';
                }

                $cotisation->save();
            }

            $this->info("Cotisations mises à jour avec budget + prorata ✅");
        }
}

<?php

namespace App\Console\Commands;

use App\Models\Appartement;
use App\Models\PaiementCotisation;
use App\Models\ConfigurationBudget;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateCotisations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:cotisations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Générer les cotisations automatiquement';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $anneeActuelle = now()->year;

        // Ignorer résidence ID = 1
        $appartements = Appartement::whereNotIn('residence_id', [1, 2])->get();

        foreach ($appartements as $appartement) {

            if (!$appartement->date_signature_contrat) {
                continue;
            }

            $dateSignature = Carbon::parse($appartement->date_signature_contrat);
            $anneeDebut = $dateSignature->year;

            for ($annee = $anneeDebut; $annee <= $anneeActuelle; $annee++) {

                // éviter doublon
                $exists = PaiementCotisation::where('appartement_id', $appartement->id)
                    ->where('annee_concernee', $annee)
                    ->exists();

                if ($exists) {
                    continue;
                }

                // récupérer budget
                $budget = ConfigurationBudget::where('residence_id', $appartement->residence_id)
                    ->where('annee', $annee)
                    ->first();

                $montantAnnuel = $budget
                    ? (float) $budget->montant_annuel_fixe
                    : 0;

                // ─────────────────────────────────────
                // PRORATA
                // ─────────────────────────────────────

                if ($annee == $anneeDebut) {

                    $debut = $dateSignature->copy();
                    $fin = Carbon::create($annee, 12, 31);

                    $joursRestants = $debut->diffInDays($fin) + 1;
                    $joursAnnee = $fin->dayOfYear;

                    $montant = $joursAnnee > 0
                        ? ($montantAnnuel / $joursAnnee) * $joursRestants
                        : 0;

                } else {

                    $montant = $montantAnnuel;
                }

                $montant = round($montant, 2);

                PaiementCotisation::create([
                    'appartement_id'   => $appartement->id,
                    'user_id'          => $appartement->proprietaire_id,
                    'annee_concernee' => $annee,
                    'montant_attendu' => $montant,
                    'montant_paye'    => 0,
                    'statut'          => 'en_retard',
                    'commentaire'     => 'Généré automatiquement avec prorata',
                ]);
            }
        }

        $this->info('Cotisations générées avec succès ✅');
    }
}
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
    protected $signature = 'app:generate-cotisations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */


        public function handle()
        {
            $anneeActuelle = now()->year;
            $anneeFutureLimit = $anneeActuelle + 2;

            $appartements = Appartement::all();

            foreach ($appartements as $appartement) {

                if (!$appartement->date_signature_contrat) continue;

                $dateSignature = Carbon::parse($appartement->date_signature_contrat);
                $anneeDebut = $dateSignature->year;

                for ($annee = $anneeDebut; $annee <= $anneeFutureLimit; $annee++) {

                    // éviter doublon
                    $exists = PaiementCotisation::where('appartement_id', $appartement->id)
                        ->where('annee_concernee', $annee)
                        ->exists();

                    if ($exists) continue;

                    // récupérer budget
                    $budget = ConfigurationBudget::where('residence_id', $appartement->residence_id)
                        ->where('annee', $annee)
                        ->first();

                    $montantAnnuel = $budget ? $budget->montant_annuel_fixe : 0;

                    // 🔥 PRORATA
                    if ($annee == $anneeDebut) {

                        $debut = $dateSignature->copy();
                        $fin = Carbon::create($annee, 12, 31);

                        $joursRestants = $debut->diffInDays($fin) + 1;
                        $joursAnnee = $fin->dayOfYear;

                        $montant = ($montantAnnuel / $joursAnnee) * $joursRestants;

                    } else {
                        $montant = $montantAnnuel;
                    }

                    $montant = round($montant, 2);

                    // statut
                    if ($annee < $anneeActuelle) {
                        $statut = 'en_retard';
                    } elseif ($annee == $anneeActuelle) {
                        $statut = 'en_retard';
                    } else {
                        $statut = 'en_attente';
                    }

                    PaiementCotisation::create([
                        'appartement_id' => $appartement->id,
                        'user_id' => $appartement->proprietaire_id,
                        'annee_concernee' => $annee,
                        'montant_attendu' => $montant,
                        'montant_paye' => 0,
                        'statut' => $statut,
                        'commentaire' => 'Généré avec prorata journalier',
                    ]);
                }
            }

            $this->info("Cotisations générées avec prorata");
        }
}

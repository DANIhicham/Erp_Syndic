<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DepenseResidence extends Model
{
    protected $table = 'depenses_residences';

    protected $fillable = [
        'residence_id',
        'titre',
        'fournisseur',
        'categorie',
        'type',
        'montant',
        'description',
        'date_debut',
        'date_fin',
        'is_active',
    ];

    protected $casts = [
        'date_debut'  => 'date',
        'date_fin'    => 'date',
        'is_active'   => 'boolean',
        'montant'     => 'decimal:2',
    ];

    // ─── Relations ────────────────────────────────────────────────

    public function residence(): BelongsTo
    {
        return $this->belongsTo(Residence::class);
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(PaiementDepense::class, 'depense_id')->orderBy('periode_debut');
    }

    // ─── Business helpers ─────────────────────────────────────────

    /** Total déjà payé sur cette dépense (toutes périodes confondues). */
    public function totalPaye(): float
    {
        return (float) $this->paiements()->where('statut', 'paye')->sum('montant');
    }

    /** Montant total attendu (somme de toutes les périodes générées). */
    public function totalAttendu(): float
    {
        return (float) $this->paiements()->sum('montant');
    }

    /** Génère et persiste les lignes paiements_depenses selon le type. */
    public function genererPeriodes(): void
    {
        match ($this->type) {
            'mensuel'       => $this->genererMensuel(),
            'trimestriel'   => $this->genererTrimestriel(),
            'unique'        => $this->genererUnique(),
            'variable'      => $this->genererVariable(),
        };
    }

    /** Regénère UNIQUEMENT les périodes futures non payées. */
    public function regenererFutures(): void
    {
        $this->paiements()
             ->where('statut', '!=', 'paye')
             ->where('periode_debut', '>', now())
             ->delete();

        $this->genererPeriodes();
    }

    // ─── Génération interne ───────────────────────────────────────

    private function genererMensuel(): void
    {
        $cursor = Carbon::parse($this->date_debut)->startOfMonth();
        $fin    = $this->date_fin
                    ? Carbon::parse($this->date_fin)->endOfMonth()
                    : Carbon::parse($this->date_debut)->endOfYear();

        while ($cursor->lessThanOrEqualTo($fin)) {
            $debutPeriode = $cursor->copy()->startOfMonth();
            $finPeriode   = $cursor->copy()->endOfMonth();

            // Ne pas créer de doublon si période déjà présente
            $existe = $this->paiements()
                ->where('periode_debut', $debutPeriode->toDateString())
                ->exists();

            if (! $existe) {
                $this->paiements()->create([
                    'periode_debut'  => $debutPeriode,
                    'periode_fin'    => $finPeriode,
                    'montant'        => $this->montant ?? 0,
                    'statut'         => $this->calculerStatutInitial($finPeriode),
                    'date_paiement'  => null,
                ]);
            }

            $cursor->addMonth();
        }
    }

    private function genererTrimestriel(): void
    {
        // Trimestres calendaires : T1=Jan-Mar, T2=Avr-Jun, T3=Jul-Sep, T4=Oct-Déc
        $anneeDebut = (int) Carbon::parse($this->date_debut)->format('Y');
        $anneeFin   = $this->date_fin
                        ? (int) Carbon::parse($this->date_fin)->format('Y')
                        : $anneeDebut;

        $trimestres = [
            ['start' => '01-01', 'end' => '03-31'],
            ['start' => '04-01', 'end' => '06-30'],
            ['start' => '07-01', 'end' => '09-30'],
            ['start' => '10-01', 'end' => '12-31'],
        ];

        for ($annee = $anneeDebut; $annee <= $anneeFin; $annee++) {
            foreach ($trimestres as $t) {
                $debut = Carbon::parse("{$annee}-{$t['start']}");
                $fin   = Carbon::parse("{$annee}-{$t['end']}");

                // Vérifier que la période chevauche l'intervalle de la dépense
                $dateDebut = Carbon::parse($this->date_debut);
                $dateFin   = $this->date_fin ? Carbon::parse($this->date_fin) : Carbon::parse($this->date_debut)->endOfYear();

                if ($fin->lessThan($dateDebut) || $debut->greaterThan($dateFin)) {
                    continue;
                }

                $existe = $this->paiements()
                    ->where('periode_debut', $debut->toDateString())
                    ->exists();

                if (! $existe) {
                    $this->paiements()->create([
                        'periode_debut'  => $debut,
                        'periode_fin'    => $fin,
                        'montant'        => $this->montant ?? 0,
                        'statut'         => $this->calculerStatutInitial($fin),
                        'date_paiement'  => null,
                    ]);
                }
            }
        }
    }

    private function genererUnique(): void
    {
        $existe = $this->paiements()->exists();
        if ($existe) {
            return;
        }

        $debut = Carbon::parse($this->date_debut);
        $fin   = $this->date_fin ? Carbon::parse($this->date_fin) : $debut->copy();

        $this->paiements()->create([
            'periode_debut'  => $debut,
            'periode_fin'    => $fin,
            'montant'        => $this->montant ?? 0,
            'statut'         => $this->calculerStatutInitial($fin),
            'date_paiement'  => null,
        ]);
    }

    /**
     * Variable = même rythme que mensuel MAIS montant = 0
     * Le montant réel est saisi ultérieurement sur chaque paiement.
     */
    private function genererVariable(): void
    {
        $cursor = Carbon::parse($this->date_debut)->startOfMonth();
        $fin    = $this->date_fin
                    ? Carbon::parse($this->date_fin)->endOfMonth()
                    : Carbon::parse($this->date_debut)->endOfYear();

        while ($cursor->lessThanOrEqualTo($fin)) {
            $debutPeriode = $cursor->copy()->startOfMonth();
            $finPeriode   = $cursor->copy()->endOfMonth();

            $existe = $this->paiements()
                ->where('periode_debut', $debutPeriode->toDateString())
                ->exists();

            if (! $existe) {
                $this->paiements()->create([
                    'periode_debut'  => $debutPeriode,
                    'periode_fin'    => $finPeriode,
                    'montant'        => 0, // sera mis à jour à la réception de la facture
                    'statut'         => 'en_attente',
                    'date_paiement'  => null,
                ]);
            }

            $cursor->addMonth();
        }
    }

    private function calculerStatutInitial(Carbon $finPeriode): string
    {
        return $finPeriode->isPast() ? 'en_retard' : 'en_attente';
    }
}
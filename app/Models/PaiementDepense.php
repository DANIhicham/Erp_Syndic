<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class PaiementDepense extends Model
{
    protected $table = 'paiements_depenses';

    protected $fillable = [
        'depense_id',
        'periode_debut',
        'periode_fin',
        'date_paiement',
        'montant',
        'montant_paye',
        'mode_paiement',
        'reference',
        'statut',
        'commentaire',
    ];

    protected $casts = [
        'periode_debut' => 'date',
        'periode_fin'   => 'date',
        'date_paiement' => 'date',
        'montant'       => 'decimal:2',
    ];

    // ─── Relations ────────────────────────────────────────────────

    public function depense(): BelongsTo
    {
        return $this->belongsTo(DepenseResidence::class, 'depense_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'paiement_depense_id');
    }

    // ─── Recalcul statut automatique ─────────────────────────────

    /**
     * Recalcule le statut en fonction de la date actuelle et du paiement.
     * À appeler après toute modification du paiement.
     */
    public function recalculerStatut(): void
    {
        if ($this->date_paiement && $this->montant > 0) {
            $this->statut = 'paye';
        } elseif ($this->periode_fin && Carbon::parse($this->periode_fin)->isPast()) {
            $this->statut = 'en_retard';
        } else {
            $this->statut = 'en_attente';
        }

        $this->save();
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeEnRetard($query)
    {
        return $query->where('statut', 'en_retard');
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopePaye($query)
    {
        return $query->where('statut', 'paye');
    }

    public function scopeParAnnee($query, int $annee)
    {
        return $query->whereYear('periode_debut', $annee);
    }

    public function scopeParMois($query, int $annee, int $mois)
    {
        return $query->whereYear('periode_debut', $annee)
                     ->whereMonth('periode_debut', $mois);
    }

    /** Paiements dont l'échéance est dans les N prochains jours. */
    public function scopeBientot($query, int $jours = 15)
    {
        return $query->where('statut', 'en_attente')
                     ->whereBetween('periode_fin', [now(), now()->addDays($jours)]);
    }
}
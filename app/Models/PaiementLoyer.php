<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaiementLoyer extends Model
{
    use HasFactory;

    protected $table = 'paiements_loyer';

    /**
     * Les attributs assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'contrat_id',
        'periode_debut',
        'periode_fin',
        'montant',
        'montant_paye',
        'date_echeance',
        'statut',
        'commentaire',
    ];

    /**
     * Les attributs castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'periode_debut' => 'date',
        'periode_fin'   => 'date',
        'date_echeance' => 'date',
        'montant'       => 'decimal:2',
        'montant_paye'  => 'decimal:2',
    ];

    // ══════════════════════════════════════════════════════════════
    // RELATIONS
    // ══════════════════════════════════════════════════════════════

    /**
     * L'échéance appartient à un contrat.
     */
    public function contrat(): BelongsTo
    {
        return $this->belongsTo(Contrat::class);
    }

    /**
     * L'échéance possède plusieurs transactions de paiement.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(TransactionLoyer::class, 'paiement_loyer_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'paiement_loyer_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionLoyer extends Model
{
    use HasFactory;

    protected $table = 'transactions_loyer';

    /**
     * Les attributs assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'paiement_loyer_id',
        'contrat_id',
        'montant',
        'date_paiement',
        'mode_paiement',
        'reference',
        'commentaire',
    ];

    /**
     * Les attributs castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_paiement' => 'date',
        'montant'       => 'decimal:2',
    ];

    // ══════════════════════════════════════════════════════════════
    // RELATIONS
    // ══════════════════════════════════════════════════════════════

    /**
     * La transaction appartient à une échéance de loyer.
     */
    public function paiementLoyer(): BelongsTo
    {
        return $this->belongsTo(PaiementLoyer::class, 'paiement_loyer_id');
    }

    /**
     * La transaction appartient à un contrat.
     */
    public function contrat(): BelongsTo
    {
        return $this->belongsTo(Contrat::class);
    }
}
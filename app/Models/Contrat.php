<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contrat extends Model
{
    use HasFactory;

    /**
     * Les attributs assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'appartement_id',
        'proprietaire_id',
        'locataire_id',
        'loyer',
        'caution',
        'date_debut',
        'date_fin',
        'type_contrat',
        'type_paiement',
        'statut',
    ];

    /**
     * Les attributs castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_debut' => 'date',
        'date_fin'   => 'date',
        'loyer'      => 'decimal:2',
        'caution'    => 'decimal:2',
    ];

    // ══════════════════════════════════════════════════════════════
    // RELATIONS
    // ══════════════════════════════════════════════════════════════

    /**
     * Le contrat appartient à un appartement.
     */
    public function appartement(): BelongsTo
    {
        return $this->belongsTo(Appartement::class);
    }

    /**
     * Le contrat appartient à un propriétaire (user).
     */
    public function proprietaire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proprietaire_id');
    }

    /**
     * Le contrat appartient à un locataire (user).
     */
    public function locataire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locataire_id');
    }

    /**
     * Le contrat possède plusieurs échéances de loyer.
     */
    public function paiementsLoyer(): HasMany
    {
        return $this->hasMany(PaiementLoyer::class);
    }

    /**
     * Le contrat possède plusieurs transactions de loyer.
     */
    public function transactionsLoyer(): HasMany
    {
        return $this->hasMany(TransactionLoyer::class);
    }
}
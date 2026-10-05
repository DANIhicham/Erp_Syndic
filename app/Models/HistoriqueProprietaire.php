<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class HistoriqueProprietaire extends Model
{
    protected $table = 'historique_proprietaires';

    protected $fillable = [
        'appartement_id',
        'user_id',
        'date_debut',
        'date_fin',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin'   => 'date',
    ];

    // ── Relations ─────────────────────────────────────────────────

    public function appartement(): BelongsTo
    {
        return $this->belongsTo(Appartement::class);
    }

    public function proprietaire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ── Accesseur durée ───────────────────────────────────────────

    /**
     * Durée en jours entre date_debut et date_fin (ou aujourd'hui si en cours).
     */
    public function getDureeAttribute(): string
    {
        $debut = Carbon::parse($this->date_debut);
        $fin   = $this->date_fin ? Carbon::parse($this->date_fin) : Carbon::today();
        $jours = $debut->diffInDays($fin);

        if ($jours >= 365) {
            $annees = floor($jours / 365);
            $reste  = $jours % 365;
            return $annees . ' an' . ($annees > 1 ? 's' : '')
                . ($reste > 0 ? ' ' . $reste . 'j' : '');
        }

        return $jours . ' jour' . ($jours > 1 ? 's' : '');
    }

    /**
     * Vrai si ce propriétaire est encore en cours (date_fin = null).
     */
    public function getEnCoursAttribute(): bool
    {
        return is_null($this->date_fin);
    }
}
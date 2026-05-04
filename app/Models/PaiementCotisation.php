<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaiementCotisation extends Model
{
    protected $fillable = ['appartement_id', 'user_id', 'annee_concernee', 'montant_paye', 'date_paiement', 'mode_paiement', 'reference_paiement', 'statut', 'commentaire'];

    public function appartement() { return $this->belongsTo(Appartement::class); }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaiementCotisation extends Model
{
    protected $fillable = ['appartement_id', 'user_id', 'annee_concernee', 'montant_paye','montant_attendu', 'date_paiement', 'mode_paiement', 'reference_paiement', 'statut', 'commentaire'];

    public function appartement() 
    { 
        return $this->belongsTo(Appartement::class); 
    }

        public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function transactions()
    {
        return $this->hasMany(TransactionPaiement::class, 'appartement_id', 'appartement_id')
            ->whereColumn('annee', 'annee_concernee');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionPaiement extends Model
{
    protected $table = 'transactions_paiements';

    protected $fillable = [
        'appartement_id',
        'user_id',
        'annee',
        'montant',
        'date_paiement',
        'mode_paiement',
        'reference',
        'commentaire',
    ];


    public function appartement()
    {
        return $this->belongsTo(Appartement::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'transaction_paiement_id');
    }
}
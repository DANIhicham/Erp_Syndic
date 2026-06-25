<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'user_id',
        'nom_fichier',
        'chemin_stockage',
        'type_document',
        'residence_id',
        'appartement_id',
        'transaction_paiement_id',
        'paiement_loyer_id',
        'paiement_depense_id',
        'date_upload'
    ];

    protected $dates = ['date_upload'];

    public function residence()
    {
        return $this->belongsTo(Residence::class);
    }

    public function transaction()
    {
        return $this->belongsTo(TransactionPaiement::class, 'transaction_paiement_id');
    }

    public function appartement()
    {
        return $this->belongsTo(Appartement::class);
    }
    
    public function paiementDepense()
    {
        return $this->belongsTo(PaiementDepense::class, 'paiement_depense_id');
    }

    public function paiementLoyer(): BelongsTo
    {
        return $this->belongsTo(PaiementLoyer::class);
    }

    // Helper pour obtenir l'URL complète du fichier
    public function getFileUrlAttribute()
    {
        return Storage::url($this->chemin_stockage);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
}

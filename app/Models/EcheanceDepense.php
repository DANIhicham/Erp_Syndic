<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcheanceDepense extends Model
{
    protected $fillable = ['depense_id', 'date_echeance', 'montant', 'statut'];

    public function depense() { return $this->belongsTo(DepenseResidence::class, 'depense_id'); }
}

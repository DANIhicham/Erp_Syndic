<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepenseResidence extends Model
{
    protected $table = 'depenses_residences';
    protected $fillable = ['residence_id', 'annee', 'titre', 'fournisseur', 'categorie', 'montant_reel', 'frequence', 'description', 'date_debut', 'date_fin', 'is_active'];

    public function echeances() { return $this->hasMany(EcheanceDepense::class, 'depense_id'); }
}

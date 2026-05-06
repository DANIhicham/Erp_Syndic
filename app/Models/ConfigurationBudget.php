<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigurationBudget extends Model
{
    protected $table = 'configuration_budgets';

    protected $fillable = [
        'residence_id',
        'annee',
        'montant_annuel_fixe'
    ];

    public function residence()
    {
        return $this->belongsTo(Residence::class);
    }
}

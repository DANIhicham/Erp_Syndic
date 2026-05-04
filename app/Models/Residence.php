<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Residence extends Model
{
    protected $fillable = ['nom', 'adresse', 'code_postal'];

    public function appartements() { return $this->hasMany(Appartement::class); }
    public function configurationBudget() { return $this->hasOne(ConfigurationBudget::class); }
    public function depenses() { return $this->hasMany(DepenseResidence::class); }
}

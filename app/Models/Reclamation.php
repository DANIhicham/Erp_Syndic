<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reclamation extends Model
{
    protected $fillable = [
        'user_id',
        'appartement_id',
        'titre',
        'description',
        'priorite',
        'statut',
        'date_creation'
    ];

    // Dates à traiter comme des objets Carbon
    protected $dates = ['date_creation'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appartement()
    {
        return $this->belongsTo(Appartement::class);
    }
}

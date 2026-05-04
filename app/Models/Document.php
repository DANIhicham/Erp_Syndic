<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'nom_fichier',
        'chemin_stockage',
        'type_document',
        'residence_id',
        'appartement_id',
        'date_upload'
    ];

    protected $dates = ['date_upload'];

    public function residence()
    {
        return $this->belongsTo(Residence::class);
    }

    public function appartement()
    {
        return $this->belongsTo(Appartement::class);
    }

    // Helper pour obtenir l'URL complète du fichier
    public function getFileUrlAttribute()
    {
        return Storage::url($this->chemin_stockage);
    }
}

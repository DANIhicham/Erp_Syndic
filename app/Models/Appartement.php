<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appartement extends Model
{   
    protected $fillable = ['residence_id', 'proprietaire_id', 'numero', 'etage', 'statut_occupation', 'date_signature_contrat'];

    public function residence() { return $this->belongsTo(Residence::class); }
    public function proprietaire() { return $this->belongsTo(User::class, 'proprietaire_id'); }
    public function paiements() { return $this->hasMany(PaiementCotisation::class); }

    // Logique pour calculer la date d'échéance dynamique
    public function getDateEcheanceDynamiqueAttribute()
    {
    $annee = Carbon::now()->year;
        $budgetAnnuel = ConfigurationBudget::where('residence_id', $this->residence_id)->where('annee', $annee)->first();
        
        if (!$budgetAnnuel) return null;

        $totalPaye = $this->paiements()->where('annee_concernee', $annee)->sum('montant_paye');
        
        // Calcul : (Total Payé * 365) / 7680
        $joursCouverts = ($totalPaye * 365) / $budgetAnnuel->montant_annuel_fixe;
        
        // On part de la date de signature du contrat
        return Carbon::parse($this->date_signature_contrat)->addDays(floor($joursCouverts));
    }
}

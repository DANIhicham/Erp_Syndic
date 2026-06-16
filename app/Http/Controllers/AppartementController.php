<?php

namespace App\Http\Controllers;

use App\Models\Appartement;
use App\Models\PaiementCotisation;
use App\Models\ConfigurationBudget;
use App\Models\TransactionPaiement;
use App\Models\User;
use App\Models\Residence;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppartementController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    // INDEX
    // ══════════════════════════════════════════════════════════════
    public function index()
    {
        $residenceId = session('residence_id');

        $appartements = Appartement::with(['proprietaire', 'residence'])
            ->where('residence_id', $residenceId)
            ->orderBy('numero')
            ->get();

        // FIX #3 : passer $residences et $tousResidents à la vue
        $residences    = Residence::orderBy('nom')->get();
        $tousResidents = User::where('role', 'proprietaire')->orderBy('nom')->get();
        
        return view('admin.appartements', compact('appartements', 'residences', 'tousResidents'));
    }

    // ══════════════════════════════════════════════════════════════
    // STORE — FIX #1 : retourner JSON
    // ══════════════════════════════════════════════════════════════
    public function store(Request $request)
    {
        $validated = $request->validate([
            'residence_id'      => 'required|exists:residences,id',
            'numero'            => 'required|string|max:50',
            'etage'             => 'nullable|integer',
            'statut_occupation' => 'required|in:libre,occupe',
        ]);

        // À la création via le modal, propriétaire et date_signature
        // sont toujours NULL (associés séparément via /associer)
        $validated['proprietaire_id']        = null;
        $validated['date_signature_contrat'] = null;

        $appartement = Appartement::create($validated);

        return response()->json([
            'success'      => true,
            'appartement'  => $appartement,
            'message'      => 'Appartement créé avec succès.',
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // UPDATE — FIX #1 : retourner JSON
    // ══════════════════════════════════════════════════════════════
    public function update(Request $request, Appartement $appartement)
    {
        $request->validate([
            'residence_id'      => 'required|exists:residences,id',
            'numero'            => 'required|string|max:50',
            'etage'             => 'nullable|integer',
            'statut_occupation' => 'required|in:libre,occupe',
        ]);

        $appartement->update($request->only([
            'residence_id',
            'numero',
            'etage',
            'statut_occupation',
        ]));

        // Note : proprietaire_id et date_signature_contrat ne sont pas
        // modifiés ici — ils passent exclusivement par /associer et /dissocier

        return response()->json([
            'success' => true,
            'message' => 'Appartement mis à jour.',
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // ASSOCIER — FIX #2 : nouvelle méthode manquante
    // ══════════════════════════════════════════════════════════════
    public function associer(Request $request, Appartement $appartement)
    {
        $request->validate([
            'proprietaire_id'        => 'required|exists:users,id',
            'date_signature_contrat' => 'required|date',
        ]);

        // FIX #4 : comparer les IDs en int pour éviter les faux positifs
        $ancienProprietaireId  = (int) $appartement->proprietaire_id ?: null;
        $nouveauProprietaireId = (int) $request->proprietaire_id;

        $appartement->update([
            'proprietaire_id'        => $nouveauProprietaireId,
            'date_signature_contrat' => $request->date_signature_contrat,
            'statut_occupation'      => 'occupe',
        ]);

        // Synchroniser les cotisations du nouveau propriétaire
        $this->syncCotisationsAppartement($appartement->fresh());

        // FIX #5 : nettoyer les cotisations impayées/futures de l'ancien proprio
        // si le propriétaire a vraiment changé
        if ($ancienProprietaireId && $ancienProprietaireId !== $nouveauProprietaireId) {
            $anneeActuelle = now()->year;
            // Supprimer les cotisations futures ou non payées de l'ancien proprio
            PaiementCotisation::where('appartement_id', $appartement->id)
                ->where('annee_concernee', '>', $anneeActuelle)
                ->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Résident associé. Cotisations générées avec succès.',
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // DISSOCIER — FIX #2 : nouvelle méthode manquante
    // ══════════════════════════════════════════════════════════════
    public function dissocier(Appartement $appartement)
    {
        $appartement->update([
            'proprietaire_id'        => null,
            'date_signature_contrat' => null,
            'statut_occupation'      => 'libre',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Résident dissocié. Appartement libéré.',
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // DESTROY — FIX #1 : retourner JSON
    // ══════════════════════════════════════════════════════════════
    public function destroy(Appartement $appartement)
    {
        $appartement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Appartement supprimé.',
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // SYNC COTISATIONS — logique originale inchangée
    // ══════════════════════════════════════════════════════════════
    private function syncCotisationsAppartement(Appartement $appartement): void
    {
        if (!$appartement->date_signature_contrat) {
            return;
        }

        $anneeActuelle = now()->year;
        $dateSignature = Carbon::parse($appartement->date_signature_contrat);
        $anneeDebut    = $dateSignature->year;

        for ($annee = $anneeDebut; $annee <= $anneeActuelle; $annee++) {

            $cotisation = PaiementCotisation::firstOrNew([
                'appartement_id'  => $appartement->id,
                'annee_concernee' => $annee,
            ]);

            $budget = ConfigurationBudget::where('residence_id', $appartement->residence_id)
                ->where('annee', $annee)
                ->first();

            $montantAnnuel = $budget ? (float) $budget->montant_annuel_fixe : 0;

            if ($annee == $anneeDebut) {
                $debut          = $dateSignature->copy();
                $fin            = Carbon::create($annee, 12, 31);
                $joursRestants  = $debut->diffInDays($fin) + 1;
                $joursAnnee     = $fin->dayOfYear;
                $montantAttendu = $joursAnnee > 0
                    ? ($montantAnnuel / $joursAnnee) * $joursRestants
                    : 0;
            } else {
                $montantAttendu = $montantAnnuel;
            }

            $montantAttendu = round($montantAttendu, 2);

            $montantPaye = (float) TransactionPaiement::where('appartement_id', $appartement->id)
                ->where('annee', $annee)
                ->sum('montant');

            if ($montantPaye >= $montantAttendu && $montantAttendu > 0) {
                $statut = 'payé';
            } elseif ($montantPaye <= 0) {
                $statut = 'en_retard';
            } else {
                $statut = 'partiel';
            }



            $cotisation->fill([
                'user_id'         => $appartement->proprietaire_id,
                'montant_attendu' => $montantAttendu,
                'montant_paye'    => $montantPaye,
                'statut'          => $statut,
                'commentaire'     => 'Synchronisation automatique',
            ]);

            $cotisation->save();
        }
    }
}
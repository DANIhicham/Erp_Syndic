<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Appartement;
use App\Models\PaiementCotisation;
use App\Models\ConfigurationBudget;
use App\Models\TransactionPaiement;
use App\Models\HistoriqueProprietaire;
use App\Http\Requests\TransfererProprieteRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ResidentController extends Controller
{


    // ══════════════════════════════════════════════════════════════
    // INDEX
    // ══════════════════════════════════════════════════════════════
    public function index()
    {
        $residenceId = session('residence_id');

        // Tous les résidents (propriétaires) de la résidence active
        $residents = User::where('role', 'proprietaire')
            ->where(function ($q) use ($residenceId) {
                if ($residenceId) {
                    $q->whereHas('appartements', function ($a) use ($residenceId) {
                        $a->where('residence_id', $residenceId);
                    })->orWhereDoesntHave('appartements');
                }
            })
            ->with(['appartements.residence', 'paiements'])
            ->orderBy('nom')
            ->get();

        // Appartements libres pour l'association
        $appartementsLibres = Appartement::with('residence')
            ->whereNull('proprietaire_id')
            ->when($residenceId, fn($q) => $q->where('residence_id', $residenceId))
            ->orderBy('numero')
            ->get();
        $tousProprietaires = User::where('role','proprietaire')
        ->where('etat','active')
        ->orderBy('nom')
        ->get();
  
        return view('admin.residents', compact('residents', 'appartementsLibres','tousProprietaires'));
    }

    // ══════════════════════════════════════════════════════════════
    // STORE — Créer un résident
    // ══════════════════════════════════════════════════════════════
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nom'       => 'required|string|max:255',
            'prenom'    => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'telephone' => 'nullable|string|max:20',
            'cin'       => 'nullable|string|max:20',
            'password'  => 'required|string|min:8',
        ]);

        $resident = User::create([
            'nom'       => $request->nom,
            'prenom'    => $request->prenom,
            'email'     => $request->email,
            'telephone' => $request->telephone,
            'cin'       => $request->cin,
            'password'  => Hash::make($request->password),
            'role'      => 'proprietaire',
            'etat'      => 'active',
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Résident créé avec succès.',
            'resident' => $resident,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // UPDATE — Modifier un résident
    // ══════════════════════════════════════════════════════════════
    public function update(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'nom'       => 'required|string|max:255',
            'prenom'    => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'telephone' => 'nullable|string|max:20',
            'cin'       => 'nullable|string|max:20',
        ]);

        $data = $request->only(['nom', 'prenom', 'email', 'telephone', 'cin']);

        // Mot de passe seulement si fourni
        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8']);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'success'  => true,
            'message'  => 'Résident modifié avec succès.',
            'resident' => $user->fresh(['appartements.residence']),
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // DÉSACTIVER / RÉACTIVER
    // ══════════════════════════════════════════════════════════════
    public function toggleEtat(User $user): JsonResponse
    {
        $nouvelEtat = $user->etat === 'active' ? 'desactive' : 'active';
        $user->update(['etat' => $nouvelEtat]);

        return response()->json([
            'success' => true,
            'message' => $nouvelEtat === 'active'
                ? 'Résident réactivé.'
                : 'Résident désactivé.',
            'etat'    => $nouvelEtat,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // ASSOCIER APPARTEMENT ← POINT CRITIQUE
    // Met à jour appartement.proprietaire_id + date_signature_contrat
    // PUIS synchronise automatiquement les cotisations
    // ══════════════════════════════════════════════════════════════
    public function assignAppartement(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'appartement_id'         => 'required|exists:appartements,id',
            'date_signature_contrat' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $appartement = Appartement::findOrFail($request->appartement_id);

            // Refuser si l'appartement est déjà occupé par quelqu'un d'autre
            if ($appartement->proprietaire_id && $appartement->proprietaire_id != $user->id) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Cet appartement est déjà associé à un autre résident.',
                ], 422);
            }

            // Un résident peut posséder plusieurs appartements : on n'en libère
            // aucun autre, on associe simplement celui sélectionné.
            $appartement->update([
                'proprietaire_id'        => $user->id,
                'date_signature_contrat' => $request->date_signature_contrat,
                'statut_occupation'      => 'occupe',
            ]);

            // ── SYNCHRONISATION COTISATIONS ───────────────────────
            // Logique exacte de UpdateCotisationsMontant.php
            $this->syncCotisations($appartement->fresh());

            DB::commit();

            return response()->json([
                'success'     => true,
                'message'     => 'Appartement associé et cotisations générées.',
                'appartement' => $appartement->fresh(['residence']),
                'resident'    => $user->fresh(['appartements.residence']),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // DESTROY — Supprimer un résident
    // ══════════════════════════════════════════════════════════════
    public function destroy(User $user): JsonResponse
    {
        // Libérer son appartement avant suppression
        Appartement::where('proprietaire_id', $user->id)
            ->update([
                'proprietaire_id'        => null,
                'date_signature_contrat' => null,
                'statut_occupation'      => 'libre',
            ]);

        $nom = $user->prenom . ' ' . $user->nom;
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Résident ' . $nom . ' supprimé avec succès.',
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // SYNC COTISATIONS — logique EXACTE de UpdateCotisationsMontant
    // Zéro modification des calculs, prorata, statuts
    // ══════════════════════════════════════════════════════════════
    private function syncCotisations(Appartement $appartement): void
    {
        if (!$appartement->date_signature_contrat) {
            return;
        }

        $anneeActuelle = now()->year;
        $dateSignature = Carbon::parse($appartement->date_signature_contrat);
        $anneeDebut    = $dateSignature->year;

        for ($annee = $anneeDebut; $annee <= $anneeActuelle; $annee++) {

            // 1. CREATE SI MANQUANT
            $cotisation = PaiementCotisation::firstOrNew([
                'appartement_id'  => $appartement->id,
                'annee_concernee' => $annee,
            ]);

            $budget = ConfigurationBudget::where('residence_id', $appartement->residence_id)
                ->where('annee', $annee)
                ->first();

            $montantAnnuel = $budget ? (float) $budget->montant_annuel_fixe : 0;

            // PRORATA première année
            if ($annee == $anneeDebut) {
                $debut         = $dateSignature->copy();
                $fin           = Carbon::create($annee, 12, 31);
                $joursRestants = $debut->diffInDays($fin) + 1;
                $joursAnnee    = $fin->dayOfYear;
                $montantAttendu = $joursAnnee > 0
                    ? ($montantAnnuel / $joursAnnee) * $joursRestants
                    : 0;
            } else {
                $montantAttendu = $montantAnnuel;
            }

            $montantAttendu = round($montantAttendu, 2);

            // 2. CALCUL PAIEMENTS
            $montantPaye = (float) TransactionPaiement::where('appartement_id', $appartement->id)
                ->where('annee', $annee)
                ->sum('montant');

            // 3. STATUT
            if ($montantPaye >= $montantAttendu && $montantAttendu > 0) {
                $statut = 'payé';
            } elseif ($montantPaye <= 0) {
                $statut = 'en_retard';
            } else {
                $statut = 'partiel';
            }

            // 4. USER
            $userId = $appartement->proprietaire_id;

            // 5. SAVE
            $cotisation->fill([
                'user_id'         => $userId,
                'montant_attendu' => $montantAttendu,
                'montant_paye'    => $montantPaye,
                'statut'          => $statut,
                'commentaire'     => 'Synchronisation automatique',
            ]);
            $cotisation->save();
        }
    }
    // ══════════════════════════════════════════════════════════════
    // HISTORIQUE PROPRIÉTAIRES — pour l'onglet dans le modal détail
    // ══════════════════════════════════════════════════════════════
    public function historiqueProprietaires(Appartement $appartement): JsonResponse
    {
        $historique = HistoriqueProprietaire::with('proprietaire')
            ->where('appartement_id', $appartement->id)
            ->orderByDesc('date_debut')
            ->get()
            ->map(function ($h) {
                $h->duree_label  = $h->duree;
                $h->en_cours     = $h->en_cours;
                $h->initiales    = strtoupper(
                    substr($h->proprietaire->prenom ?? '', 0, 1) .
                    substr($h->proprietaire->nom    ?? '', 0, 1)
                );
                return $h;
            });
 
        return response()->json([
            'success'    => true,
            'historique' => $historique,
        ]);
    }
 
    // ══════════════════════════════════════════════════════════════
    // TRANSFERER PROPRIETE
    // ══════════════════════════════════════════════════════════════
    public function transfererPropriete(TransfererProprieteRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $appartement         = Appartement::findOrFail($request->appartement_id);
            $ancienProprietaire  = User::findOrFail($appartement->proprietaire_id);
            $nouveauProprietaire = User::findOrFail($request->nouveau_proprietaire_id);
            $dateVente           = Carbon::parse($request->date_vente);
            $anneeVente          = $dateVente->year;
 
            // ── ÉTAPE 8 : Contrôle de sécurité ────────────────────
            // Vérifier si des cotisations futures ont des paiements
            $cotisationsFuturesPayees = PaiementCotisation::where('appartement_id', $appartement->id)
                ->where('user_id', $ancienProprietaire->id)
                ->where('annee_concernee', '>', $anneeVente)
                ->where(function ($q) {
                    $q->where('montant_paye', '>', 0)
                      ->orWhereHas('transactions');
                })
                ->exists();
 
            if ($cotisationsFuturesPayees) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de transférer cet appartement car des paiements existent déjà sur les années futures.',
                ], 422);
            }
 
            // ── ÉTAPE 2 : Fermer l'historique de l'ancien propriétaire ──
            HistoriqueProprietaire::where('appartement_id', $appartement->id)
                ->whereNull('date_fin')
                ->update(['date_fin' => $dateVente->copy()->subDay()->format('Y-m-d')]);
 
            // ── ÉTAPE 3 : Créer le nouvel historique ──────────────
            HistoriqueProprietaire::create([
                'appartement_id' => $appartement->id,
                'user_id'        => $nouveauProprietaire->id,
                'date_debut'     => $dateVente->format('Y-m-d'),
                'date_fin'       => null,
            ]);
 
            // ── ÉTAPE 4 : Mettre à jour appartements.proprietaire_id ──
            $appartement->update([
                'proprietaire_id'        => $nouveauProprietaire->id,
                'date_signature_contrat' => $dateVente->format('Y-m-d'),
            ]);
 
            // ── ÉTAPE 6 : Prorata année de transition ─────────────
            $this->recalculerProrataTransition(
                $appartement,
                $ancienProprietaire,
                $nouveauProprietaire,
                $dateVente
            );
 
            // ── ÉTAPE 7 : Supprimer cotisations futures non payées (ancien) ──
            PaiementCotisation::where('appartement_id', $appartement->id)
                ->where('user_id', $ancienProprietaire->id)
                ->where('annee_concernee', '>', $anneeVente)
                ->where('montant_paye', 0)
                ->whereDoesntHave('transactions')
                ->delete();
 
            // ── ÉTAPE 9 : Générer cotisations futures (nouveau) ────
            $this->genererCotisationsFutures($appartement, $nouveauProprietaire, $dateVente);
 
            DB::commit();
 
            return response()->json([
                'success' => true,
                'message' => 'Transfert de propriété effectué avec succès. '
                           . 'Les cotisations ont été recalculées au prorata.',
            ]);
 
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du transfert : ' . $e->getMessage(),
            ], 500);
        }
    }
 
    // ══════════════════════════════════════════════════════════════
    // PRIVATE — Prorata année de transition (Étape 6)
    // ══════════════════════════════════════════════════════════════
    private function recalculerProrataTransition(
        Appartement $appartement,
        User $ancienProp,
        User $nouveauProp,
        Carbon $dateVente
    ): void {
        $annee  = $dateVente->year;
        $budget = ConfigurationBudget::where('residence_id', $appartement->residence_id)
            ->where('annee', $annee)
            ->first();
 
        $montantAnnuel = $budget ? (float) $budget->montant_annuel_fixe : 0;
 
        // Jours totaux de l'année
        $debutAnnee  = Carbon::create($annee, 1, 1);
        $finAnnee    = Carbon::create($annee, 12, 31);
        $joursAnnee  = $debutAnnee->diffInDays($finAnnee) + 1;
 
        // ── Ancien propriétaire : 01/01 → dateVente - 1 jour ──────
        $finAncien       = $dateVente->copy()->subDay();
        $joursAncien     = max(0, $debutAnnee->diffInDays($finAncien) + 1);
        $montantAncien   = $joursAnnee > 0
            ? round(($montantAnnuel / $joursAnnee) * $joursAncien, 2)
            : 0;
 
        $this->upsertCotisation($appartement->id, $ancienProp->id, $annee, $montantAncien);
 
        // ── Nouveau propriétaire : dateVente → 31/12 ──────────────
        $joursNouveau    = max(0, $dateVente->diffInDays($finAnnee) + 1);
        $montantNouveau  = $joursAnnee > 0
            ? round(($montantAnnuel / $joursAnnee) * $joursNouveau, 2)
            : 0;
 
        $this->upsertCotisation($appartement->id, $nouveauProp->id, $annee, $montantNouveau);
    }
 
    // ══════════════════════════════════════════════════════════════
    // PRIVATE — Upsert cotisation (create ou update montant_attendu)
    // ══════════════════════════════════════════════════════════════
    private function upsertCotisation(
        int $appartementId,
        int $userId,
        int $annee,
        float $montantAttendu
    ): void {
        $cotisation = PaiementCotisation::firstOrNew([
            'appartement_id'  => $appartementId,
            'user_id'         => $userId,
            'annee_concernee' => $annee,
        ]);
 
        $montantPaye = (float) TransactionPaiement::where('appartement_id', $appartementId)
            ->where('annee', $annee)
            ->sum('montant');
 
        // Recalcul du statut
        if ($montantPaye >= $montantAttendu && $montantAttendu > 0) {
            $statut = 'payé';
        } elseif ($montantPaye > 0) {
            $statut = 'partiel';
        } else {
            $statut = 'en_retard';
        }
 
        $cotisation->fill([
            'montant_attendu' => $montantAttendu,
            'montant_paye'    => $montantPaye,
            'statut'          => $statut,
            'commentaire'     => 'Prorata suite à transfert de propriété',
        ]);
        $cotisation->save();
    }
 
    // ══════════════════════════════════════════════════════════════
    // PRIVATE — Générer cotisations futures pour nouveau propriétaire
    // (Étape 9 — même logique que syncCotisations)
    // ══════════════════════════════════════════════════════════════
    private function genererCotisationsFutures(
        Appartement $appartement,
        User $nouveauProp,
        Carbon $dateVente
    ): void {
        $anneeDebut    = $dateVente->year + 1; // Année transition déjà gérée
        $anneeCourante = now()->year;
 
        // Générer uniquement l'année courante et les années passées depuis la vente
        // (les futures seront générées par syncCotisations au besoin)
        for ($annee = $anneeDebut; $annee <= $anneeCourante; $annee++) {
            $budget = ConfigurationBudget::where('residence_id', $appartement->residence_id)
                ->where('annee', $annee)
                ->first();
 
            $montantAttendu = $budget ? (float) $budget->montant_annuel_fixe : 0;
 
            $this->upsertCotisation(
                $appartement->id,
                $nouveauProp->id,
                $annee,
                $montantAttendu
            );
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Appartement;
use App\Models\PaiementCotisation;
use App\Models\ConfigurationBudget;
use App\Models\TransactionPaiement;
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
  
        return view('admin.residents', compact('residents', 'appartementsLibres'));
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
}
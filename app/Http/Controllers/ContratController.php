<?php

namespace App\Http\Controllers;

use App\Models\Contrat;
use App\Models\Appartement;
use App\Models\User;
use App\Models\PaiementLoyer;
use App\Models\TransactionLoyer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContratController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    // INDEX — Page unique + données
    // ══════════════════════════════════════════════════════════════
    public function index()
    {
        $residenceId = session('residence_id');

        // ── Auto-terminaison des contrats expirés ──────────────────
        // Règle 12 : si date_fin < aujourd'hui et statut = actif → termine
        $this->autoTerminerContratsExpires($residenceId);

        // ── Liste des contrats filtrée par résidence active ────────
        $contrats = Contrat::query()
            ->with(['appartement.residence', 'proprietaire', 'locataire'])
            ->when($residenceId, function ($q) use ($residenceId) {
                $q->whereHas('appartement', function ($a) use ($residenceId) {
                    $a->where('residence_id', $residenceId);
                });
            })
            ->orderByDesc('created_at')
            ->get();

        // ── Appartements disponibles pour un nouveau contrat ────────
        $appartementsDisponibles = Appartement::query()
            ->with('residence')
            ->where('statut_location', 'Disponible')
            ->when($residenceId, fn($q) => $q->where('residence_id', $residenceId))
            ->orderBy('numero')
            ->get();

        // ── Locataires existants (role = locataire) ─────────────────
        $locataires = User::where('role', 'locataire')
            ->orderBy('nom')
            ->get();

        // ── KPIs ──────────────────────────────────────────────────
        $kpis = [
            'actifs'           => $contrats->where('statut', 'actif')->count(),
            'en_attente'       => $contrats->where('statut', 'en_attente')->count(),
            'resilies'         => $contrats->where('statut', 'resilie')->count(),
            'revenus_mensuels' => $contrats->where('statut', 'actif')
                ->where('type_paiement', 'mensuel')
                ->sum('loyer'),
            'impayes'          => PaiementLoyer::whereIn(
                    'contrat_id',
                    $contrats->pluck('id')
                )
                ->whereIn('statut', ['en_retard', 'partiel'])
                ->sum(DB::raw('montant - montant_paye')),
        ];

        return view('location.contrats', compact('contrats', 'appartementsDisponibles', 'locataires', 'kpis'));
    }

    // ══════════════════════════════════════════════════════════════
    // STORE — Créer un contrat
    // ══════════════════════════════════════════════════════════════
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'appartement_id' => 'required|exists:appartements,id',
            'locataire_id'   => 'required|exists:users,id',
            'date_debut'     => 'required|date',
            'date_fin'       => 'nullable|date|after_or_equal:date_debut',
            'loyer'          => 'required|numeric|gt:0',
            'caution'        => 'required|numeric|gte:0',
            'type_contrat'   => 'required|in:bail_residentiel,bail_commercial',
            'type_paiement'  => 'required|in:mensuel,trimestriel,total',
            'commentaire'    => 'nullable|string',
        ], [
            'date_fin.after_or_equal' => 'La date de fin doit être supérieure ou égale à la date de début.',
            'loyer.gt'                => 'Le loyer doit être supérieur à 0.',
            'caution.gte'             => 'La caution doit être supérieure ou égale à 0.',
        ]);

        DB::beginTransaction();
        try {
            $appartement = Appartement::findOrFail($validated['appartement_id']);

            // ── Règle 4 : seuls les appartements Disponibles peuvent être loués ──
            if ($appartement->statut_location !== 'Disponible') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Cet appartement n\'est pas disponible à la location.',
                ], 422);
            }

            // ── Règle 1 : un appartement ne peut avoir qu'un seul contrat actif ──
            $dejaActif = Contrat::where('appartement_id', $appartement->id)
                ->where('statut', 'actif')
                ->exists();

            if ($dejaActif) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Cet appartement possède déjà un contrat actif.',
                ], 422);
            }

            // ── Propriétaire = propriétaire actuel de l'appartement ──────
            $proprietaireId = $appartement->proprietaire_id;

            $contrat = Contrat::create([
                'appartement_id'  => $appartement->id,
                'proprietaire_id' => $proprietaireId,
                'locataire_id'    => $validated['locataire_id'],
                'loyer'           => $validated['loyer'],
                'caution'         => $validated['caution'],
                'date_debut'      => $validated['date_debut'],
                'date_fin'        => $validated['date_fin'] ?? null,
                'type_contrat'    => $validated['type_contrat'],
                'type_paiement'   => $validated['type_paiement'],
                'statut'          => 'actif',
            ]);

            // ── Règle 4 : appartement devient Loué ───────────────────────
            $appartement->update(['statut_location' => 'Loué']);

            // ── Génération des échéances de loyer (paiements_loyer) ──────
            $this->genererEcheances($contrat);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contrat créé avec succès.',
                'contrat' => $contrat->fresh(['appartement.residence', 'proprietaire', 'locataire']),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création : ' . $e->getMessage(),
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // UPDATE — Modifier uniquement loyer, date_fin, caution, commentaire
    // ══════════════════════════════════════════════════════════════
    public function update(Request $request, Contrat $contrat): JsonResponse
    {
        $validated = $request->validate([
            'loyer'       => 'required|numeric|gt:0',
            'caution'     => 'required|numeric|gte:0',
            'date_fin'    => 'nullable|date|after_or_equal:' . $contrat->date_debut->format('Y-m-d'),
            'commentaire' => 'nullable|string',
        ], [
            'date_fin.after_or_equal' => 'La date de fin doit être supérieure ou égale à la date de début.',
            'loyer.gt'                => 'Le loyer doit être supérieur à 0.',
            'caution.gte'             => 'La caution doit être supérieure ou égale à 0.',
        ]);

        DB::beginTransaction();
        try {
            $contrat->update([
                'loyer'    => $validated['loyer'],
                'caution'  => $validated['caution'],
                'date_fin' => $validated['date_fin'] ?? $contrat->date_fin,
            ]);

            // Note : 'commentaire' n'existe pas sur contrats, on l'ignore
            // silencieusement ici (champ prévu pour résiliation / échéances).

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contrat modifié avec succès.',
                'contrat' => $contrat->fresh(['appartement.residence', 'proprietaire', 'locataire']),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification : ' . $e->getMessage(),
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // SHOW — Détail complet (onglets Informations / Paiements / Transactions / Historique)
    // ══════════════════════════════════════════════════════════════
    public function show(Contrat $contrat): JsonResponse
    {
        $contrat->load(['appartement.residence', 'proprietaire', 'locataire']);

        $paiements = PaiementLoyer::where('contrat_id', $contrat->id)
            ->orderBy('date_echeance')
            ->get();

        $transactions = TransactionLoyer::where('contrat_id', $contrat->id)
            ->orderByDesc('date_paiement')
            ->get();

        return response()->json([
            'success'      => true,
            'contrat'      => $contrat,
            'paiements'    => $paiements,
            'transactions' => $transactions,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // RESILIER — Passage à statut = resilie + libération de l'appartement
    // ══════════════════════════════════════════════════════════════
    public function resilier(Request $request, Contrat $contrat): JsonResponse
    {
        $validated = $request->validate([
            'date_resiliation' => 'required|date',
            'motif'            => 'required|string|max:255',
            'commentaire'      => 'nullable|string',
        ]);

        if ($contrat->statut === 'resilie') {
            return response()->json([
                'success' => false,
                'message' => 'Ce contrat est déjà résilié.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // ── Règle 11 : jamais de delete(), uniquement statut = resilie ──
            $contrat->update([
                'statut'   => 'resilie',
                'date_fin' => $validated['date_resiliation'],
            ]);

            // ── Règle 4 : appartement redevient Disponible ───────────────
            $contrat->appartement->update(['statut_location' => 'Disponible']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contrat résilié avec succès.',
                'contrat' => $contrat->fresh(['appartement.residence', 'proprietaire', 'locataire']),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la résiliation : ' . $e->getMessage(),
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // AUTO-TERMINAISON — Règle 12
    // Si date_fin < aujourd'hui et statut = actif → statut = termine
    // ══════════════════════════════════════════════════════════════
    private function autoTerminerContratsExpires(?int $residenceId): void
    {
        $contratsExpires = Contrat::where('statut', 'actif')
            ->whereNotNull('date_fin')
            ->where('date_fin', '<', Carbon::today())
            ->when($residenceId, function ($q) use ($residenceId) {
                $q->whereHas('appartement', function ($a) use ($residenceId) {
                    $a->where('residence_id', $residenceId);
                });
            })
            ->with('appartement')
            ->get();

        foreach ($contratsExpires as $contrat) {
            DB::transaction(function () use ($contrat) {
                $contrat->update(['statut' => 'termine']);
                $contrat->appartement->update(['statut_location' => 'Disponible']);
            });
        }
    }

    // ══════════════════════════════════════════════════════════════
    // GÉNÉRATION DES ÉCHÉANCES (paiements_loyer) selon type_paiement
    // ══════════════════════════════════════════════════════════════
    private function genererEcheances(Contrat $contrat): void
    {
        $debut = Carbon::parse($contrat->date_debut);
        $fin   = $contrat->date_fin
            ? Carbon::parse($contrat->date_fin)
            : $debut->copy()->addYear(); // par défaut 1 an si pas de date_fin

        $intervalleMois = match ($contrat->type_paiement) {
            'mensuel'     => 1,
            'trimestriel' => 3,
            'total'       => null, // une seule échéance globale
            default       => 1,
        };

        if ($intervalleMois === null) {
            // Paiement total : une seule échéance sur toute la période
            PaiementLoyer::create([
                'contrat_id'     => $contrat->id,
                'periode_debut'  => $debut->format('Y-m-d'),
                'periode_fin'    => $fin->format('Y-m-d'),
                'montant'        => $contrat->loyer,
                'montant_paye'   => 0,
                'date_echeance'  => $debut->format('Y-m-d'),
                'statut'         => 'en_attente',
            ]);
            return;
        }

        $curseur = $debut->copy();

        while ($curseur->lessThan($fin)) {
            $periodeFin = $curseur->copy()->addMonths($intervalleMois)->subDay();
            if ($periodeFin->greaterThan($fin)) {
                $periodeFin = $fin->copy();
            }

            PaiementLoyer::create([
                'contrat_id'    => $contrat->id,
                'periode_debut' => $curseur->format('Y-m-d'),
                'periode_fin'   => $periodeFin->format('Y-m-d'),
                'montant'       => $contrat->loyer,
                'montant_paye'  => 0,
                'date_echeance' => $curseur->format('Y-m-d'),
                'statut'        => 'en_attente',
            ]);

            $curseur->addMonths($intervalleMois);
        }
    }
}
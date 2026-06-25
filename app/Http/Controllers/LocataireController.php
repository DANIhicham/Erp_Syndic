<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Contrat;
use App\Models\Document;
use App\Models\PaiementLoyer;
use App\Models\TransactionLoyer;
use App\Models\Reclamation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LocataireController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    // INDEX — Liste des locataires filtrée par résidence active
    // ══════════════════════════════════════════════════════════════
    public function index()
    {
        $residenceId = session('residence_id');

        $locataires = User::where('role', 'locataire')
            ->with([
                'contratsLocataire' => fn($q) => $q->with('appartement.residence'),
                'documents'
            ])
            ->when($residenceId, function ($q) use ($residenceId) {
                $q->where(function ($query) use ($residenceId) {
                    $query->whereHas('contratsLocataire.appartement', function ($a) use ($residenceId) {
                        $a->where('residence_id', $residenceId);
                    })
                    ->orWhereDoesntHave('contratsLocataire');
                });
            })
            ->orderBy('nom')
            ->get();

        // ── KPIs ──────────────────────────────────────────────────
        $kpis = [
            'total'           => $locataires->count(),
            'actifs'          => $locataires->where('etat', 'active')->count(),
            'inactifs'        => $locataires->where('etat', 'desactive')->count(),
            'contrats_actifs' => Contrat::where('statut', 'actif')
                ->when($residenceId, function ($q) use ($residenceId) {
                    $q->whereHas('appartement', fn($a) => $a->where('residence_id', $residenceId));
                })
                ->count(),
        ];

        return view('location.locataires', compact('locataires', 'kpis'));
    }

    // ══════════════════════════════════════════════════════════════
    // STORE — Créer un locataire + upload documents CIN
    // ══════════════════════════════════════════════════════════════
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nom'        => 'required|string|max:255',
            'prenom'     => 'required|string|max:255',
            'cin'        => 'required|string|max:20|unique:users,cin',
            'telephone'  => 'required|string|max:20|unique:users,telephone',
            'email'      => 'required|email|unique:users,email',
            'cin_recto'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'cin_verso'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'cin.unique'       => 'Ce numéro de CIN est déjà utilisé.',
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'email.unique'     => 'Cet email est déjà utilisé.',
            'cin_recto.mimes'  => 'Le fichier CIN recto doit être une image (jpg, png) ou un PDF.',
            'cin_verso.mimes'  => 'Le fichier CIN verso doit être une image (jpg, png) ou un PDF.',
            'cin_recto.max'    => 'Le fichier ne doit pas dépasser 5 Mo.',
            'cin_verso.max'    => 'Le fichier ne doit pas dépasser 5 Mo.',
        ]);

        DB::beginTransaction();
        try {
            // ── Créer le locataire ──────────────────────────────────
            $locataire = User::create([
                'nom'       => $request->nom,
                'prenom'    => $request->prenom,
                'cin'       => $request->cin,
                'telephone' => $request->telephone,
                'email'     => $request->email,
                'password'  => Hash::make(Str::random(16)),
                'role'      => 'locataire',
                'etat'      => 'active',
            ]);

            // ── Upload CIN recto ───────────────────────────────────
            if ($request->hasFile('cin_recto')) {
                $this->stockerDocument($request->file('cin_recto'), $locataire->id, 'CIN Recto');
            }

            // ── Upload CIN verso ───────────────────────────────────
            if ($request->hasFile('cin_verso')) {
                $this->stockerDocument($request->file('cin_verso'), $locataire->id, 'CIN Verso');
            }

            DB::commit();

            return response()->json([
                'success'   => true,
                'message'   => 'Locataire ' . $locataire->prenom . ' ' . $locataire->nom . ' créé avec succès.',
                'locataire' => $locataire,
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
    // UPDATE — Modifier nom, prénom, téléphone, email, état
    // + upload nouveaux documents CIN si fournis
    // ══════════════════════════════════════════════════════════════
    public function update(Request $request, User $locataire): JsonResponse
    {
        if ($locataire->role !== 'locataire') {
            return response()->json(['success' => false, 'message' => 'Action non autorisée.'], 403);
        }

        $request->validate([
            'nom'       => 'required|string|max:255',
            'prenom'    => 'required|string|max:255',
            'telephone' => 'required|string|max:20|unique:users,telephone,' . $locataire->id,
            'email'     => 'required|email|unique:users,email,' . $locataire->id,
            'etat'      => 'required|in:active,desactive',
            'cin_recto' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'cin_verso' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'email.unique'     => 'Cet email est déjà utilisé.',
        ]);

        DB::beginTransaction();
        try {
            $locataire->update([
                'nom'       => $request->nom,
                'prenom'    => $request->prenom,
                'telephone' => $request->telephone,
                'email'     => $request->email,
                'etat'      => $request->etat,
            ]);

            // ── Nouveaux uploads CIN (remplacent les anciens du même type) ──
            if ($request->hasFile('cin_recto')) {
                $this->remplacerDocument($request->file('cin_recto'), $locataire->id, 'CIN Recto');
            }
            if ($request->hasFile('cin_verso')) {
                $this->remplacerDocument($request->file('cin_verso'), $locataire->id, 'CIN Verso');
            }

            DB::commit();

            return response()->json([
                'success'   => true,
                'message'   => 'Locataire modifié avec succès.',
                'locataire' => $locataire->fresh(['documents']),
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
    // SHOW — Détail complet (onglets : infos, contrats, paiements, réclamations, documents)
    // ══════════════════════════════════════════════════════════════
    public function show(User $locataire): JsonResponse
    {
        if ($locataire->role !== 'locataire') {
            return response()->json(['success' => false, 'message' => 'Action non autorisée.'], 403);
        }

        $locataire->load(['documents']);
        // ── Contrats du locataire ──────────────────────────────────
        $contrats = Contrat::where('locataire_id', $locataire->id)
            ->with('appartement.residence')
            ->orderByDesc('created_at')
            ->get();

        $contratIds = $contrats->pluck('id');

        // ── Échéances de loyer ─────────────────────────────────────
        $paiements = PaiementLoyer::whereIn('contrat_id', $contratIds)
            ->orderBy('date_echeance')
            ->get()
            ->map(function ($p) {
                $p->reste = max(0, $p->montant - $p->montant_paye);
                return $p;
            });

        // ── Transactions réelles ───────────────────────────────────
        $transactions = TransactionLoyer::whereIn('contrat_id', $contratIds)
            ->orderByDesc('date_paiement')
            ->get();

        //── Réclamations ───────────────────────────────────────────
        $reclamations = Reclamation::where('user_id', $locataire->id)
            ->with('appartement')
            ->orderByDesc('date_creation')
            ->get();

        // ── Documents (avec URL publique si stocké dans storage) ───
        $documents = $locataire->documents->map(function ($doc) {
            $doc->url = Storage::url($doc->chemin_stockage);
            
            return $doc;
        });
        return response()->json([
            'success'      => true,
            'locataire'    => $locataire,
            'contrats'     => $contrats,
            'paiements'    => $paiements,
            'transactions' => $transactions,
            'reclamations' => $reclamations,
            'documents'    => $documents,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // TOGGLE ÉTAT — Activer / Désactiver
    // Interdit si contrat actif en cours
    // ══════════════════════════════════════════════════════════════
    public function toggleEtat(User $locataire): JsonResponse
    {
        if ($locataire->role !== 'locataire') {
            return response()->json(['success' => false, 'message' => 'Action non autorisée.'], 403);
        }

        // ── Règle métier : interdiction si contrat actif ────────────
        if ($locataire->etat === 'active') {
            $aContratActif = Contrat::where('locataire_id', $locataire->id)
                ->where('statut', 'actif')
                ->exists();

            if ($aContratActif) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de désactiver ce locataire : il possède un contrat actif en cours.',
                ], 422);
            }
        }

        $nouvelEtat = $locataire->etat === 'active' ? 'desactive' : 'active';
        $locataire->update(['etat' => $nouvelEtat]);

        return response()->json([
            'success' => true,
            'message' => $nouvelEtat === 'active' ? 'Locataire réactivé.' : 'Locataire désactivé.',
            'etat'    => $nouvelEtat,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // PRIVATE — Stocker un document CIN dans storage + table documents
    // ══════════════════════════════════════════════════════════════
    private function stockerDocument($file, int $userId, string $nomType): void
    {
        $nomFichier   = $nomType . '_' . $userId . '_' . time() . '.' . $file->getClientOriginalExtension();
        $chemin       = $file->storeAs('documents/cin/' . $userId, $nomFichier, 'public');

        Document::create([
            'user_id'          => $userId,
            'nom_fichier'      => $nomFichier,
            'chemin_stockage'  => $chemin,
            'type_document'    => 'carte_nationale',
            'residence_id'     => null,
            'appartement_id'   => null,
            'date_upload'      => now(),
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // PRIVATE — Remplacer un document CIN existant du même type
    // (supprime l'ancien fichier physique + enregistrement)
    // ══════════════════════════════════════════════════════════════
    private function remplacerDocument($file, int $userId, string $nomType): void
    {
        // Supprimer l'ancien document du même nom de type s'il existe
        $ancien = Document::where('user_id', $userId)
            ->where('type_document', 'carte_nationale')
            ->where('nom_fichier', 'like', $nomType . '%')
            ->first();

        if ($ancien) {
            Storage::disk('public')->delete($ancien->chemin_stockage);
            $ancien->delete();
        }

        $this->stockerDocument($file, $userId, $nomType);
    }
}
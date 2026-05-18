<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepenseRequest;
use App\Http\Requests\UpdateDepenseRequest;
use App\Http\Requests\PayerDepenseRequest;
use App\Models\DepenseResidence;
use App\Models\PaiementDepense;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DepenseResidenceController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // INDEX — Vue principale avec KPIs et tableau filtré
    // ─────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $residenceId = session('residence_id');

        $annee  = (int) $request->input('annee', now()->year);
        $mois   = $request->input('mois');
        $statut = $request->input('statut');
        $type   = $request->input('type');
        $search = $request->input('search');

        // ── Requête principale ─────────────────────
        $query = PaiementDepense::with(['depense', 'documents'])
            ->whereHas('depense', function ($q) use ($residenceId, $type, $search) {

                $q->where('residence_id',$residenceId)
                ->where('is_active',true);

                if ($type) {
                    $q->where('type', $type);
                }

                if ($search) {
                    $q->where(function ($q2) use ($search) {
                        $q2->where('titre', 'like', "%{$search}%")
                        ->orWhere('fournisseur', 'like', "%{$search}%")
                        ->orWhere('categorie', 'like', "%{$search}%");
                    });
                }
            })
            ->whereYear('periode_debut', $annee);

        // ── Filtre mois ────────────────────────────
        if ($mois) {
            $query->whereMonth('periode_debut', $mois);
        }

        // ── Filtre statut ──────────────────────────
        if ($statut) {
            $query->where('statut', $statut);
        }

        // ── Recalcul retards ───────────────────────
        $this->recalculerStatutsRetard($residenceId, $annee);

        $paiements = $query
            ->orderBy('periode_debut')
            ->get();

        // ── KPIs ───────────────────────────────────
        $kpis = $this->calculerKpis($residenceId, $annee);

        // ── Alertes ────────────────────────────────
        $alertes = $this->genererAlertes($residenceId);

        // ── Années disponibles ─────────────────────
        $annees = PaiementDepense::whereHas('depense', function ($q) use ($residenceId) {
                $q->where('residence_id', $residenceId);
            })
            ->selectRaw('YEAR(periode_debut) as annee')
            ->distinct()
            ->orderByDesc('annee')
            ->pluck('annee');

        return view('syndic.depenses', compact(
            'paiements',
            'kpis',
            'alertes',
            'annees',
            'annee',
            'mois',
            'statut',
            'type',
            'search',
            'residenceId'
        ));
    }
    // ─────────────────────────────────────────────────────────────
    // STORE — Créer dépense + générer périodes automatiquement
    // ─────────────────────────────────────────────────────────────

    public function store(StoreDepenseRequest $request)
    {
        DB::transaction(function () use ($request) {
            $depense = DepenseResidence::create([
                'residence_id' => $request->residence_id,
                'titre'        => $request->titre,
                'fournisseur'  => $request->fournisseur,
                'categorie'    => $request->categorie,
                'type'         => $request->type,
                'montant'      => $request->type === 'variable' ? null : $request->montant,
                'description'  => $request->description,
                'date_debut'   => $request->date_debut,
                'date_fin'     => $request->date_fin,
                'is_active'    => true,
            ]);

            $depense->genererPeriodes();
        });

        return back()->with('success', 'Dépense créée et périodes générées avec succès.');
    }

    // ─────────────────────────────────────────────────────────────
    // UPDATE — Modifier dépense (protège les périodes payées)
    // ─────────────────────────────────────────────────────────────

    public function update(UpdateDepenseRequest $request, DepenseResidence $depense)
    {
        DB::transaction(function () use ($request, $depense) {
            $depense->update([
                'titre'       => $request->titre,
                'fournisseur' => $request->fournisseur,
                'categorie'   => $request->categorie,
                'montant'     => $depense->type === 'variable' ? null : $request->montant,
                'description' => $request->description,
                'date_debut'  => $request->date_debut,
                'date_fin'    => $request->date_fin,
                'is_active'   => $request->boolean('is_active', true),
            ]);

            // Supprimer uniquement les périodes FUTURES non payées
            $depense->paiements()
                    ->where('statut', '!=', 'paye')
                    ->where('periode_debut', '>', now())
                    ->delete();

            // Regénérer les périodes manquantes
            $depense->genererPeriodes();
        });

        return back()->with('success', 'Charge modifiée. Les échéances futures ont été recalculées.');
    }

    // ─────────────────────────────────────────────────────────────
    // DESTROY — Archiver (soft) ou supprimer
    // ─────────────────────────────────────────────────────────────

    public function destroy(DepenseResidence $depense)
    {
        // Archivage logique
        $depense->update(['is_active' => false]);

        return back()->with('success', "Dépense « {$depense->titre} » archivée.");
    }

    // ─────────────────────────────────────────────────────────────
    // PAYER — Enregistrer paiement d'une période
    // ─────────────────────────────────────────────────────────────

    public function payer(PayerDepenseRequest $request, PaiementDepense $paiement)
    {
        DB::transaction(function () use ($request, $paiement) {

            $type = $paiement->depense->type;

            // Vérification pour dépense unique
            if ($type === 'unique') {

                $reste = $paiement->montant - $paiement->montant_paye;

                if ($request->montant > $reste) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'montant' => "Le montant dépasse le reste à payer ({$reste} MAD)."
                    ]);
                }
            }

            // Charges récurrentes fixes
            if (in_array($type, ['mensuel','trimestriel'])) {

                $montantPaye = $paiement->montant;
                $statut = 'paye';

            }
            // Service variable
            elseif ($type === 'variable') {

                $paiement->montant = $request->montant;

                $montantPaye = $request->montant;
                $statut = 'paye';

            }
            // Dépense unique
            else {

                $montantPaye = $paiement->montant_paye + $request->montant;

                $statut = ($montantPaye >= $paiement->montant)
                    ? 'paye'
                    : 'en_attente';
            }

            $paiement->update([
                'montant_paye' => $montantPaye,
                'date_paiement' => $request->date_paiement,
                'mode_paiement' => $request->mode_paiement,
                'reference' => $request->reference,
                'commentaire' => $request->commentaire,
                'statut' => $statut,
            ]);
    

            if ($request->hasFile('document')) {

                $file = $request->file('document');

                $path = $file->store(
                    "justificatifs/depenses/{$paiement->depense->residence_id}",
                    'public'
                );

                Document::create([
                    'nom_fichier' => $file->getClientOriginalName(),
                    'chemin_stockage' => $path,
                    'type_document' => 'facture',
                    'residence_id' => $paiement->depense->residence_id,
                    'paiement_depense_id' => $paiement->id,
                    'date_upload' => now(),
                ]);
            }

        });

        return back()->with(
            'success',
            'Paiement enregistré avec succès.'
        );
    }

    // ─────────────────────────────────────────────────────────────
    // EXPORT CSV
    // ─────────────────────────────────────────────────────────────

    public function exportCsv(Request $request)
    {
        $annee = (int) $request->input('annee', now()->year);
        $residenceId = $request->input('residence_id');

        $paiements = PaiementDepense::with('depense')
            ->whereHas('depense', fn($q) => $q->where('residence_id', $residenceId))
            ->whereYear('periode_debut', $annee)
            ->orderBy('periode_debut')
            ->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"depenses_{$annee}.csv\"",
        ];

        $callback = function () use ($paiements) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Titre', 'Fournisseur', 'Catégorie', 'Type', 'Période début', 'Période fin', 'Montant', 'Statut', 'Mode', 'Référence'], ';');

            foreach ($paiements as $p) {
                fputcsv($handle, [
                    $p->depense->titre,
                    $p->depense->fournisseur ?? '—',
                    $p->depense->categorie,
                    $p->depense->type,
                    $p->periode_debut->format('d/m/Y'),
                    $p->periode_fin->format('d/m/Y'),
                    number_format($p->montant, 2, '.', ''),
                    $p->statut,
                    $p->mode_paiement ?? '—',
                    $p->reference ?? '—',
                ], ';');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ─────────────────────────────────────────────────────────────
    // PRIVÉ — KPIs
    // ─────────────────────────────────────────────────────────────

    private function calculerKpis(?int $residenceId, int $annee): array
    {
        $base = PaiementDepense::whereHas('depense', fn($q) =>
                    $q->where('residence_id', $residenceId))
                ->whereYear('periode_debut', $annee);

        $now        = now();
        $debutMois  = $now->copy()->startOfMonth();
        $finMois    = $now->copy()->endOfMonth();

        $enRetard = (clone $base)->where('statut', 'en_retard')->count();

        $bientot = (clone $base)
            ->where('statut', 'en_attente')
            ->whereBetween('periode_fin', [$now, $now->copy()->addDays(15)])
            ->count();

        $partiels = (clone $base)
            ->where('montant', '>', 0)
            ->where('statut', 'en_attente')
            ->whereColumn('montant', '>', DB::raw('0')) // sera affiné
            ->count(); // à affiner si vous gérez le partiel au niveau paiement

        $payesMoisMontant = (clone $base)
            ->where('statut', 'paye')
            ->whereBetween('date_paiement', [$debutMois, $finMois])
            ->sum('montant');

        $payesMoisCount = (clone $base)
            ->where('statut', 'paye')
            ->whereBetween('date_paiement', [$debutMois, $finMois])
            ->count();

        $totalAttendu = (clone $base)->sum('montant');
        $totalPaye    = (clone $base)->where('statut', 'paye')->sum('montant');

        return compact(
            'enRetard', 'bientot', 'partiels',
            'payesMoisMontant', 'payesMoisCount',
            'totalAttendu', 'totalPaye'
        );
    }

    // ─────────────────────────────────────────────────────────────
    // PRIVÉ — Alertes
    // ─────────────────────────────────────────────────────────────

    private function genererAlertes(?int $residenceId): array
    {
        $alertes = [];

        // En retard
        $retards = PaiementDepense::with('depense')
            ->whereHas('depense', fn($q) => $q->where('residence_id', $residenceId)->where('is_active', true))
            ->where('statut', 'en_retard')
            ->orderBy('periode_fin')
            ->take(10)
            ->get();

        foreach ($retards as $p) {
            $alertes[] = [
                'type'    => 'danger',
                'icon'    => 'fa-triangle-exclamation',
                'label'   => $p->depense->titre,
                'message' => 'En retard depuis le ' . $p->periode_fin->format('d/m/Y') . ' — ' . number_format($p->montant, 2) . ' MAD',
                'id'      => $p->id,
                'montant' => $p->montant,
                'periode_debut' => $p->periode_debut->format('d/m/Y'),
                'periode_fin' => $p->periode_fin->format('d/m/Y'),
                'depense_type' => $p->depense->type,
            ];
        }

        // Bientôt dus (≤ 15 jours)
        $bientot = PaiementDepense::with('depense')
            ->whereHas('depense', fn($q) => $q->where('residence_id', $residenceId)->where('is_active', true))
            ->where('statut', 'en_attente')
            ->whereBetween('periode_fin', [now(), now()->addDays(15)])
            ->orderBy('periode_fin')
            ->take(5)
            ->get();

        foreach ($bientot as $p) {
            $alertes[] = [
                'type'    => 'warning',
                'icon'    => 'fa-hourglass-half',
                'label'   => $p->depense->titre,
                'message' => 'Échéance le ' . $p->periode_fin->format('d/m/Y') . ' — ' . number_format($p->montant, 2) . ' MAD',
                'id'      => $p->id,
                'montant' => $p->montant,
                'periode_debut' => $p->periode_debut->format('d/m/Y'),
                'periode_fin' => $p->periode_fin->format('d/m/Y'),
                'depense_type' => $p->depense->type,
            ];
        }

        return $alertes;

    }

    // ─────────────────────────────────────────────────────────────
    // PRIVÉ — Recalcul automatique des retards
    // ─────────────────────────────────────────────────────────────

    private function recalculerStatutsRetard(?int $residenceId, int $annee): void
    {
        PaiementDepense::whereHas('depense', fn($q) =>
                $q->where('residence_id', $residenceId))
            ->whereYear('periode_debut', $annee)
            ->where('statut', 'en_attente')
            ->where('periode_fin', '<', now())
            ->update(['statut' => 'en_retard']);
    }
}
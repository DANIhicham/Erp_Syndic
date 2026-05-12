<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appartement;
use App\Models\PaiementCotisation;
use Carbon\Carbon;

class CoproprietaireController extends Controller
{
    // ════════════════════════════════════════
    // INDEX
    // ════════════════════════════════════════
    public function index(Request $request)
    {
        $residenceId = session('residence_id');

        $annee  = (int) ($request->annee ?? date('Y'));
        $search = $request->search ?? '';
        $status = $request->status ?? '';

        if (!$residenceId) {
            return back()->with('error', 'Résidence non sélectionnée.');
        }

        // ── QUERY APPARTEMENTS ───────────────
        $query = Appartement::with([
            'proprietaire',
            'paiementCotisation' => function ($q) use ($annee) {
                $q->where('annee_concernee', $annee);
            }
        ])
        ->where('residence_id', $residenceId)
        ->whereNotNull('proprietaire_id')
        ->whereNotNull('date_signature_contrat');
        // ── SEARCH ───────────────────────────
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('numero', 'like', "%$search%")
                  ->orWhereHas('proprietaire', function ($q2) use ($search) {
                      $q2->where('nom', 'like', "%$search%")
                         ->orWhere('prenom', 'like', "%$search%");
                  });
            });
        }

        // ── GET DATA ──────────────────────────
        $appartements = $query->get()
            ->map(fn($apt) => $this->buildRow($apt, $annee))
            ->filter(fn($apt) => $apt['montant_attendu'] > 0)
            ->values();
        // ── FILTER STATUS (UI) ───────────────
        if ($status) {
            $appartements = $appartements
                ->where('status', $status)
                ->values();
        }

        return view('syndic.coproprietaires', compact(
            'appartements',
            'annee',
            'search',
            'status'
        ));
    }

    // ════════════════════════════════════════
    // BUILD ROW (LOGIQUE CENTRALE)
    // ════════════════════════════════════════
    private function buildRow($apt, int $annee): array
    {
        $paiement = $apt->paiementCotisation->first();
        $dateSignatureContrat = $apt->date_signature_contrat
        ? Carbon::parse($apt->date_signature_contrat)->format('d/m/Y')
        : null;
        $montantPaye    = (float) ($paiement->montant_paye ?? 0);
        $montantAttendu = (float) ($paiement->montant_attendu ?? 0);

        $reste = max(0, $montantAttendu - $montantPaye);

        // ── STATUS CLEAN ─────────────────────
        $status = $paiement->statut ?? 'en_retard';

        $statusLabel = match ($status) {
            'payé'    => 'Soldé',
            'partiel' => 'Partiel',
            default   => 'Retard',
        };

        // ── DATE SIGNATURE LOGIC ─────────────
        $dateSignature = $apt->date_signature_contrat
            ? Carbon::parse($apt->date_signature_contrat)
            : null;

        $anneeSignature = $dateSignature->year;

        $dateDebut = $annee == $anneeSignature
            ? $dateSignature->copy()
            : Carbon::create($annee, 1, 1);

        $dateFin = Carbon::create($annee, 12, 31);

        $jours = $dateDebut->diffInDays($dateFin) + 1;

        $coutJournalier = $montantAttendu > 0
            ? $montantAttendu / $jours
            : 0;

        $joursCouverts = $coutJournalier > 0
            ? floor($montantPaye / $coutJournalier)
            : 0;

        $dateEcheance = null;
        
        if ($joursCouverts > 0) {

            $dateEcheance = $dateDebut->copy()
                ->addDays($joursCouverts - 1);

            if ($dateEcheance->gt($dateFin)) {
                $dateEcheance = $dateFin->copy();
            }

            $dateEcheance = $dateEcheance->format('d/m/Y');
        }

        // ── AVATAR ────────────────────────────
        $user = $apt->proprietaire;

        $avatar = $user
            ? strtoupper(substr($user->prenom, 0, 1) . substr($user->nom, 0, 1))
            : '??';

        return [
            'id'              => $apt->id,
            'numero'          => $apt->numero,
            'etage'           => $apt->etage,

            'nom'             => $user ? $user->prenom . ' ' . $user->nom : '—',
            'email'           => $user?->email,
            'telephone'       => $user?->telephone,

            'avatar'          => $avatar,

            'montant_paye'    => $montantPaye,
            'montant_attendu' => $montantAttendu,
            'reste'           => $reste,

            'status'          => $status,
            'status_label'    => $statusLabel,

            'date_echeance'   => $dateEcheance,
            'date_signature_contrat' => $dateSignatureContrat,
            'jours_couverts'  => $joursCouverts,
        ];
    }
}
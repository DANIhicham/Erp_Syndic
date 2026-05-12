<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reclamation;
use App\Models\Appartement;
use Carbon\Carbon;

class ReclamationController extends Controller
{
    // ════════════════════════════════════════
    // INDEX
    // ════════════════════════════════════════
    public function index(Request $request)
    {
        $residenceId = session('residence_id');

        if (!$residenceId) {
            return back()->with('error', 'Veuillez sélectionner une résidence.');
        }

        $search   = $request->search ?? '';
        $statut   = $request->statut ?? '';
        $priorite = $request->priorite ?? '';

        // ─────────────────────────────────────
        // APPARTEMENTS (pour modal create)
        // ─────────────────────────────────────
        $appartements = Appartement::where('residence_id', $residenceId)
            ->orderBy('numero')
            ->get();

        // ─────────────────────────────────────
        // QUERY RECLAMATIONS
        // ─────────────────────────────────────
        $query = Reclamation::with([
            'user',
            'appartement'
        ])
        ->whereHas('appartement', function ($q) use ($residenceId) {

            $q->where('residence_id', $residenceId);

        });

        // ─────────────────────────────────────
        // SEARCH
        // ─────────────────────────────────────
        if ($search) {

            $query->where(function ($q) use ($search) {

                $q->where('titre', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")

                  ->orWhereHas('appartement', function ($q2) use ($search) {

                      $q2->where('numero', 'like', "%{$search}%");

                  });

            });

        }

        // ─────────────────────────────────────
        // FILTER STATUT
        // ─────────────────────────────────────
        if ($statut) {

            $query->where('statut', $statut);

        }

        // ─────────────────────────────────────
        // FILTER PRIORITE
        // ─────────────────────────────────────
        if ($priorite) {

            $query->where('priorite', $priorite);

        }

        // ─────────────────────────────────────
        // DATA
        // ─────────────────────────────────────
        $reclamations = $query
            ->latest('date_creation')
            ->get();

        // ─────────────────────────────────────
        // KPIs
        // ─────────────────────────────────────
        $stats = [

            'ouvertes' => Reclamation::whereHas('appartement', function ($q) use ($residenceId) {

                    $q->where('residence_id', $residenceId);

                })
                ->where('statut', 'ouverte')
                ->count(),

            'urgentes' => Reclamation::whereHas('appartement', function ($q) use ($residenceId) {

                    $q->where('residence_id', $residenceId);

                })
                ->where('priorite', 'urgente')
                ->where('statut', 'ouverte')
                ->count(),

            'en_cours' => Reclamation::whereHas('appartement', function ($q) use ($residenceId) {

                    $q->where('residence_id', $residenceId);

                })
                ->where('statut', 'en_cours')
                ->count(),

            'resolues' => Reclamation::whereHas('appartement', function ($q) use ($residenceId) {

                    $q->where('residence_id', $residenceId);

                })
                ->where('statut', 'resolue')
                ->whereMonth('date_creation', now()->month)
                ->whereYear('date_creation', now()->year)
                ->count(),

        ];

        return view('syndic.reclamations', compact(
            'reclamations',
            'appartements',
            'stats',
            'search',
            'statut',
            'priorite'
        ));
    }

    // ════════════════════════════════════════
    // STORE
    // ════════════════════════════════════════
    public function store(Request $request)
    {
        $request->validate([

            'titre'          => 'required|string|max:255',
            'description'    => 'required|string',
            'priorite'       => 'required|in:basse,moyenne,haute,urgente',
            'appartement_id' => 'nullable|exists:appartements,id',

        ]);

        Reclamation::create([

            'user_id' => auth()->id(),

            'appartement_id' => $request->appartement_id,

            'titre' => $request->titre,

            'description' => $request->description,

            'priorite' => $request->priorite,

            'statut' => 'ouverte',

            'date_creation' => Carbon::now(),

        ]);

        return redirect()
            ->route('reclamations.index')
            ->with('success', 'Réclamation créée avec succès.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([

            'statut' => 'required|in:ouverte,en_cours,resolue',

        ]);

        $reclamation = Reclamation::findOrFail($id);

        $reclamation->update([

            'statut' => $request->statut,

        ]);

        return redirect()
            ->route('reclamations.index')
            ->with('success', 'Statut mis à jour avec succès.');
    }
}
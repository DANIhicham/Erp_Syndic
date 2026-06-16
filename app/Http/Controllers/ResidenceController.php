<?php

namespace App\Http\Controllers;

use App\Models\Residence;
use Illuminate\Http\Request;

class ResidenceController extends Controller
{
    /**
     * Afficher toutes les résidences.
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');

        $residences = Residence::query()
            ->when($search, function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('adresse', 'like', "%{$search}%")
                  ->orWhere('code_postal', 'like', "%{$search}%");
            })
            ->orderBy('nom')
            ->get();

        return view('admin.residences', compact('residences', 'search'));
    }

    /**
     * Créer une nouvelle résidence.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'         => 'required|string|max:255',
            'adresse'     => 'required|string|max:255',
            'code_postal' => 'nullable|string|max:20',
        ], [
            'nom.required'     => 'Le nom de la résidence est obligatoire.',
            'adresse.required' => 'L\'adresse est obligatoire.',
        ]);

        Residence::create($validated);

        return redirect()->route('admin.residences.index')
            ->with('success', 'Résidence « ' . $validated['nom'] . ' » créée avec succès.');
    }

    /**
     * Modifier une résidence existante.
     */
    public function update(Request $request, Residence $residence)
    {
        $validated = $request->validate([
            'nom'         => 'required|string|max:255',
            'adresse'     => 'required|string|max:255',
            'code_postal' => 'nullable|string|max:20',
        ], [
            'nom.required'     => 'Le nom de la résidence est obligatoire.',
            'adresse.required' => 'L\'adresse est obligatoire.',
        ]);

        $residence->update($validated);

        return redirect()->route('admin.residences.index')
            ->with('success', 'Résidence « ' . $validated['nom'] . ' » mise à jour avec succès.');
    }

    /**
     * Supprimer une résidence.
     */
    public function destroy(Residence $residence)
    {
        $nom = $residence->nom;
        $residence->delete();

        return redirect()->route('admin.residences.index')
            ->with('success', 'Résidence « ' . $nom . ' » supprimée avec succès.');
    }
}
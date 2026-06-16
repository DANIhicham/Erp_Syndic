<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UtilisateurController extends Controller
{
    // Rôles gérés par ce controller (jamais 'proprietaire')
    private const ROLES_AUTORISES = ['admin', 'syndic', 'locateur'];

    // ══════════════════════════════════════════════════════════════
    // INDEX
    // ══════════════════════════════════════════════════════════════
    public function index()
    {
        $utilisateurs = User::whereIn('role', self::ROLES_AUTORISES)
            ->orderBy('nom')
            ->get();

        return view('admin.utilisateurs', compact('utilisateurs'));
    }

    // ══════════════════════════════════════════════════════════════
    // STORE — Créer un utilisateur
    // ══════════════════════════════════════════════════════════════
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nom'       => 'required|string|max:255',
            'prenom'    => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'telephone' => 'nullable|string|max:20',
            'cin'       => 'nullable|string|max:20',
            'role'      => 'required|in:admin,syndic,locateur',
            'password'  => 'required|string|min:8',
        ], [
            'nom.required'      => 'Le nom est obligatoire.',
            'prenom.required'   => 'Le prénom est obligatoire.',
            'email.required'    => 'L\'email est obligatoire.',
            'email.unique'      => 'Cet email est déjà utilisé.',
            'role.required'     => 'Le rôle est obligatoire.',
            'role.in'           => 'Rôle invalide.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min'      => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        $utilisateur = User::create([
            'nom'       => $request->nom,
            'prenom'    => $request->prenom,
            'email'     => $request->email,
            'telephone' => $request->telephone,
            'cin'       => $request->cin,
            'role'      => $request->role,
            'password'  => Hash::make($request->password),
            'etat'      => 'active',
        ]);

        return response()->json([
            'success'      => true,
            'message'      => 'Utilisateur créé avec succès.',
            'utilisateur'  => $utilisateur,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // UPDATE — Modifier un utilisateur
    // ══════════════════════════════════════════════════════════════
    public function update(Request $request, User $utilisateur): JsonResponse
    {
        // Empêcher la modification d'un propriétaire via ce controller
        if (!in_array($utilisateur->role, self::ROLES_AUTORISES) && !in_array($request->role, self::ROLES_AUTORISES)) {
            return response()->json(['success' => false, 'message' => 'Action non autorisée.'], 403);
        }

        $request->validate([
            'nom'       => 'required|string|max:255',
            'prenom'    => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $utilisateur->id,
            'telephone' => 'nullable|string|max:20',
            'cin'       => 'nullable|string|max:20',
            'role'      => 'required|in:admin,syndic,locateur',
        ], [
            'nom.required'    => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'email.required'  => 'L\'email est obligatoire.',
            'email.unique'    => 'Cet email est déjà utilisé.',
            'role.required'   => 'Le rôle est obligatoire.',
            'role.in'         => 'Rôle invalide.',
        ]);

        $data = $request->only(['nom', 'prenom', 'email', 'telephone', 'cin', 'role']);

        // Mot de passe seulement si fourni
        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8'], [
                'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            ]);
            $data['password'] = Hash::make($request->password);
        }

        $utilisateur->update($data);

        return response()->json([
            'success'     => true,
            'message'     => 'Utilisateur modifié avec succès.',
            'utilisateur' => $utilisateur->fresh(),
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // DÉSACTIVER / RÉACTIVER
    // ══════════════════════════════════════════════════════════════
    public function toggleEtat(User $utilisateur): JsonResponse
    {
        if (!in_array($utilisateur->role, self::ROLES_AUTORISES)) {
            return response()->json(['success' => false, 'message' => 'Action non autorisée.'], 403);
        }

        // Empêcher l'admin connecté de se désactiver lui-même
        if ($utilisateur->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas désactiver votre propre compte.',
            ], 403);
        }

        $nouvelEtat = $utilisateur->etat === 'active' ? 'desactive' : 'active';
        $utilisateur->update(['etat' => $nouvelEtat]);

        return response()->json([
            'success' => true,
            'message' => $nouvelEtat === 'active' ? 'Utilisateur réactivé.' : 'Utilisateur désactivé.',
            'etat'    => $nouvelEtat,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // DESTROY — Supprimer un utilisateur
    // ══════════════════════════════════════════════════════════════
    public function destroy(User $utilisateur): JsonResponse
    {
        if (!in_array($utilisateur->role, self::ROLES_AUTORISES)) {
            return response()->json(['success' => false, 'message' => 'Action non autorisée.'], 403);
        }

        if ($utilisateur->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas supprimer votre propre compte.',
            ], 403);
        }

        $nom = $utilisateur->prenom . ' ' . $utilisateur->nom;
        $utilisateur->delete();

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur ' . $nom . ' supprimé.',
        ]);
    }
}
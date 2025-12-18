<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login de l'utilisateur
     */
    public function login(Request $request)
    {
        // Validation des champs
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Chercher l'utilisateur par email
        $utilisateur = Utilisateur::where('email', $request->email)->first();

        // Vérifier si utilisateur existe et mot de passe correct
        if (!$utilisateur || !Hash::check($request->password, $utilisateur->mot_de_passe)) {
            throw ValidationException::withMessages([
                'email' => ['Les informations de connexion sont invalides.'],
            ]);
        }

        // Optionnel : créer un token API si tu utilises Laravel Sanctum
        // $token = $utilisateur->createToken('API Token')->plainTextToken;

        return response()->json([
            'success' => true,
            'utilisateur' => $utilisateur,
            // 'token' => $token
        ]);
    }

    /**
     * Logout de l'utilisateur (optionnel si tokens)
     */
    public function logout(Request $request)
    {
        // Si tokens : $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => true]);
    }
}

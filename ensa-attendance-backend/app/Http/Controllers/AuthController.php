<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        $utilisateur = Utilisateur::where('email', $request->email)->first();
        if (!$utilisateur || !Hash::check($request->password, $utilisateur->mot_de_passe)) {
            throw ValidationException::withMessages([
                'email' => ['Les informations de connexion sont invalides.'],
            ]);
        }

        $token = $utilisateur->createToken('API Token')->plainTextToken;

        return response()->json([
            'success' => true,
            'utilisateur' => $utilisateur,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['success' => true, 'message' => 'Déconnecté avec succès']);
        }

        return response()->json(['success' => false, 'message' => 'Utilisateur non authentifié'], 401);
    }
}

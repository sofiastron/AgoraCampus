<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{

    public function register(Request $request)
    {
      //par admin only
    }

   
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email ou mot de passe incorrect'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie',
            'user' => $user,
            'token' => $token
        ]);
    }

    
   public function profile(Request $request)
{
    $user = $request->user(); 
    return response()->json([
        'nom' => $user->nom,
        'email' => $user->email,
        'photo' => $user->photo, 
    ]);
}
public function changePassword(Request $request)
{
    $request->validate([
        'current_password' => 'required',
        'new_password' => 'required|min:6|confirmed', 
    ]);

    $user = $request->user();

   
    if (!Hash::check($request->current_password, $user->password)) {
        return response()->json([
            'message' => 'Mot de passe actuel incorrect'
        ], 400);
    }

  
    $user->password = Hash::make($request->new_password);
    $user->save();

    return response()->json([
        'message' => 'Mot de passe mis à jour avec succès'
    ]);
}

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie'
        ]);
    }


    public function sendResetLinkEmail(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
    ]);

    $status = Password::sendResetLink(
        $request->only('email')
    );

    return $status === Password::RESET_LINK_SENT
        ? response()->json(['message' => 'Email de réinitialisation envoyé'])
        : response()->json(['message' => 'Erreur lors de l’envoi'], 500);
}

public function resetPassword(Request $request)
{
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:6|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->password = Hash::make($password);
            $user->save();
        }
    );

    return $status === Password::PASSWORD_RESET
        ? response()->json(['message' => 'Mot de passe réinitialisé avec succès'])
        : response()->json(['message' => 'Token invalide ou expiré'], 400);
}
public function updatePhoto(Request $request)
{
    $request->validate([
        'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // max 2 Mo
    ]);

    $user = auth()->user();

    if ($request->hasFile('photo')) {
        $file = $request->file('photo');
        $filename = time().'_'.$file->getClientOriginalName();
        $file->storeAs('public/photos', $filename);
        $user->photo = asset('storage/photos/'.$filename);
        $user->save();

        return response()->json(['photo' => $user->photo]);
    }

    return response()->json(['message' => 'Aucune photo sélectionnée'], 400);
}


}

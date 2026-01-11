<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Filiere;
use App\Models\Groupe;
class EtudiantController extends Controller
{
     public function getByFiliere($filiere_id)
{
    $groupes = \App\Models\Groupe::where('filiere_id', $filiere_id)
        ->select('id', 'nom', 'niveau', 'filiere_id')
        ->get();
    
    return response()->json([
        'success' => true,
        'data' => $groupes
    ]);
}
    // ✅ LISTER LES FILIERES
    public function getFilieres()
    {
        try {
            if (class_exists('App\Models\Filiere')) {
                $filieres = Filiere::select('id', 'nom', 'code')->get();
            } else {
                $filieres = [
                    (object)['id' => 1, 'nom' => 'Génie Informatique', 'code' => 'GI'],
                    (object)['id' => 2, 'nom' => 'Génie Industriel', 'code' => 'GIND'],
                    (object)['id' => 3, 'nom' => 'Génie Civil', 'code' => 'GC']
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => $filieres
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in getFilieres: ' . $e->getMessage());
            
            return response()->json([
                'success' => true,
                'data' => [
                    ['id' => 1, 'nom' => 'Génie Informatique', 'code' => 'GI'],
                    ['id' => 2, 'nom' => 'Génie Industriel', 'code' => 'GIND'],
                    ['id' => 3, 'nom' => 'Gestion', 'code' => 'GEST']
                ]
            ]);
        }
    }

    // ✅ LISTER LES ETUDIANTS
    public function index()
    {
        return DB::table('etudiants')
            ->join('users', 'users.id', '=', 'etudiants.user_id')
            ->leftJoin('groupes', 'groupes.id', '=', 'etudiants.groupe_id')
            ->leftJoin('filieres', 'filieres.id', '=', 'groupes.filiere_id')
           ->select(
    'etudiants.id',
    'users.id as user_id',
    'users.nom',
    'users.email',
    'etudiants.cne',
    'etudiants.niveau',
    'etudiants.groupe_id',
    'groupes.nom as groupe_nom',        // IMPORTANT
    'groupes.niveau as groupe_niveau',  // IMPORTANT
    'filieres.id as filiere_id',
    'filieres.nom as filiere_nom',
    'filieres.code as filiere_code'
)
            ->get();
    }

    // ✅ AJOUTER UN ETUDIANT
    public function store(Request $request)
    {
        // Validation des données
        $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'cne' => 'required|string|unique:etudiants,cne',
            'groupe_id' => 'required|exists:groupes,id'
        ]);

        try {
            // Créer l'utilisateur
            $user = User::create([
                'nom' => $request->nom,
                'email' => $request->email,
                'password' => bcrypt('123456'),
                'role' => 'etudiant'
            ]);

            // Récupérer le niveau depuis le groupe
            $groupe = \App\Models\Groupe::find($request->groupe_id);
            $niveau = $groupe ? $groupe->niveau : 'L1';

            // Créer l'étudiant
            Etudiant::create([
                'cne' => $request->cne,
                'niveau' => $niveau,
                'groupe_id' => $request->groupe_id,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Étudiant ajouté avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Error in store method: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout de l\'étudiant: ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ MODIFIER UN ETUDIANT
    public function update(Request $request, $id)
    {
        // Récupérer l'étudiant pour obtenir le user_id
        $etudiant = Etudiant::find($id);
        
        if (!$etudiant) {
            return response()->json([
                'success' => false,
                'message' => 'Étudiant non trouvé'
            ], 404);
        }
        
        $user_id = $etudiant->user_id;

        // CORRECTION IMPORTANTE: Règles de validation avec les bons IDs
        $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user_id, // Utiliser user_id pour la table users
            'cne' => 'required|string|unique:etudiants,cne,' . $id,    // Utiliser id pour la table etudiants
            'groupe_id' => 'required|exists:groupes,id'
        ]);

        try {
            // Mettre à jour l'utilisateur
            User::where('id', $user_id)->update([
                'nom' => $request->nom,
                'email' => $request->email,
            ]);

            // Récupérer le niveau depuis le groupe
            $groupe = \App\Models\Groupe::find($request->groupe_id);
            $niveau = $groupe ? $groupe->niveau : 'L1';

            // Mettre à jour l'étudiant
            $etudiant->update([
                'cne' => $request->cne,
                'niveau' => $niveau,
                'groupe_id' => $request->groupe_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Étudiant modifié avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Error in update method: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification de l\'étudiant: ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ SUPPRIMER UN ETUDIANT
    public function destroy($id)
    {
        try {
            // Récupérer l'étudiant pour obtenir le user_id
            $etudiant = Etudiant::find($id);
            
            if (!$etudiant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Étudiant non trouvé'
                ], 404);
            }
            
            $user_id = $etudiant->user_id;
            
            // Supprimer l'étudiant
            $etudiant->delete();
            
            // Supprimer l'utilisateur
            User::where('id', $user_id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Étudiant supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Error in destroy method: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'étudiant: ' . $e->getMessage()
            ], 500);
        }
    }
}
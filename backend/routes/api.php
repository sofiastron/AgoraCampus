<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\EnseignantEmploiController;
use App\Http\Controllers\EmploiProfController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EtudiantController;
use App\Http\Controllers\EmploiController;
use App\Http\Controllers\EnseignantController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Ici sont définies toutes les routes API pour l'application.
| Les routes sont organisées par sections logiques.
|
*/

/** -------------------- USERS -------------------- **/
Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
});

/** -------------------- ETUDIANTS -------------------- **/

/** -------------------- ENSEIGNANTS -------------------- **/
Route::prefix('enseignants')->group(function () {
    Route::get('/', [EnseignantController::class, 'index']);
    Route::post('/', [EnseignantController::class, 'store']);
    Route::put('/{id}', [EnseignantController::class, 'update']);
    Route::delete('/{id}', [EnseignantController::class, 'destroy']);
});

/** -------------------- EMPLOIS -------------------- **/
// CRUD emplois
/** -------------------- VALIDATION EMPLOI -------------------- **/


/** -------------------- VALIDATION EMPLOI -------------------- **/
Route::prefix('emploi-temps')->group(function () {
    
    // Vérifier combinaison exacte
    Route::get('/verifier-valide', function(Request $request) {
        try {
            $emploiValide = \App\Models\EmploiTemps::where('filiere_id', $request->filiere_id)
                ->where('niveau', $request->niveau)
                ->where('semestre', $request->semestre)
                ->where('statut', 'valide')
                ->first();
                
            return response()->json([
                'success' => true,
                'existe' => !is_null($emploiValide),
                'data' => $emploiValide
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    });
    
    // Vérifier par filière seulement (tous niveaux)
    Route::get('/verifier-valide-filiere', function(Request $request) {
        try {
            $emploisValides = \App\Models\EmploiTemps::where('filiere_id', $request->filiere_id)
                ->where('semestre', $request->semestre)
                ->where('statut', 'valide')
                ->get();
                
            return response()->json([
                'success' => true,
                'existe' => $emploisValides->count() > 0,
                'count' => $emploisValides->count(),
                'data' => $emploisValides
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    });
    
    // Lister tous les emplois validés d'une filière
    Route::get('/filiere/{filiere_id}/valides', function($filiere_id) {
        try {
            $emplois = \App\Models\EmploiTemps::where('filiere_id', $filiere_id)
                ->where('statut', 'valide')
                ->with('filiere')
                ->orderBy('semestre')
                ->orderBy('niveau')
                ->get();
                
            return response()->json([
                'success' => true,
                'data' => $emplois
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    });
});
Route::prefix('schedule')->group(function () {
    Route::get('/filieres', [ScheduleController::class, 'getFilieres']);
    Route::get('/niveaux/{filiere_id}', [ScheduleController::class, 'getNiveaux']);
    Route::post('/generate-from-db', [ScheduleController::class, 'generateFromDb']);
});
/** -------------------- PLANNING -------------------- **/
Route::prefix('planning')->group(function () {
    // Routes existantes
    Route::get('/filieres', [PlanningController::class, 'getFilieres']);
    Route::get('/niveaux/{filiere_id}', [PlanningController::class, 'getNiveaux']);
    Route::post('/generate', [PlanningController::class, 'generateFromDb']);
    Route::post('/save', [PlanningController::class, 'saveEmploi']);
    Route::post('/check-duplicate', [PlanningController::class, 'checkDuplicate']);});


    
    Route::prefix('planning')->group(function () {
    // Routes pour la gestion
    Route::get('/saved', [PlanningController::class, 'getSavedEmplois']);
    Route::get('/saved/filiere/{filiere_id}', [PlanningController::class, 'getSavedEmplois']);
    Route::get('/saved/{id}', [PlanningController::class, 'getEmploiById']);
    
    // NOUVELLE ROUTE : Suppression
    Route::delete('/saved/{id}', [PlanningController::class, 'deleteEmploi']);
    // AJOUTEZ CETTE ROUTE POUR UPDATE
  
});
// Ajoutez cette route spécifique pour la validation
Route::post('/planning/{id}/validate', [PlanningController::class, 'validateEmploi']);
// Si votre méthode s'appelle différemment

/** -------------------- SCHEDULE -------------------- **/


/** -------------------- ENSEIGNANT EMPLOIS -------------------- **/
Route::prefix('enseignant-emploi')->group(function () {
    Route::get('/enseignants', [EnseignantEmploiController::class, 'getAllEnseignants']);
    Route::get('/emploi/{enseignant_id}', [EnseignantEmploiController::class, 'getEmploiEnseignant']);
    Route::get('/correction/{module}', [EnseignantEmploiController::class, 'getEnseignantsPourCorrection']);
    Route::get('/verifier/{emploi_id}', [EnseignantEmploiController::class, 'verifierIncoherencesEmploi']);
    Route::post('/corriger/{emploi_id}', [EnseignantEmploiController::class, 'appliquerCorrectionsEmploi']);
    Route::get('/simple/{id}', [EnseignantEmploiController::class, 'getEmploiSimple']);
    Route::get('/commun/{id}', [EnseignantEmploiController::class, 'getEmploiAvecModulesCommuns']);
    Route::get('/all/with-emplois', [EnseignantEmploiController::class, 'getAllEnseignantsWithEmplois']);
    Route::get('/enseignant/{enseignant_id}', [EnseignantEmploiController::class, 'getEmploiForEnseignant']);
    Route::get('/filiere/{filiere_id}', [EnseignantEmploiController::class, 'getEmploiByFiliere']);
    Route::get('/export/{enseignant_id}/{format?}', [EnseignantEmploiController::class, 'exportEmploiEnseignant']);
    Route::get('/reel/{enseignant_id}', [EnseignantEmploiController::class, 'getEmploiReelEnseignant']);
});

/** -------------------- ENSEIGNANT (avec emploi) -------------------- **/
Route::get('/enseignants', function() {
    try {
        $enseignants = \App\Models\Enseignant::with('user')->orderBy('nom')->get();
        return response()->json([
            'success' => true,
            'data' => $enseignants
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});
/** -------------------- ETUDIANTS -------------------- **/
Route::prefix('etudiants')->group(function () {
    Route::get('/', [EtudiantController::class, 'index']);
    Route::post('/', [EtudiantController::class, 'store']);
    Route::delete('/{id}', [EtudiantController::class, 'destroy']);
    Route::put('/{id}', [EtudiantController::class, 'update']);
    // Ajoutez cette ligne pour récupérer les filières
    Route::get('/filieres', [EtudiantController::class, 'getFilieres']);
});
// Dans routes/api.php
Route::get('/groupes/filiere/{filiere_id}', [EtudiantController::class, 'getByFiliere']);



/** -------------------- EMPLOI PROF -------------------- **/
Route::prefix('emploi-prof')->group(function () {
    Route::get('/complet/{enseignant_id}', [EmploiProfController::class, 'getEmploiCompletProf']);
    Route::get('/filiere/{filiere_id}/niveau/{niveau}', [EmploiProfController::class, 'getEmploiParFiliereNiveau']);
});

/** -------------------- DEBUG / RAW -------------------- **/
Route::get('/emploi-raw/{id}', function ($id) {
    $emploi = \App\Models\EmploiTemps::find($id);
    return response()->json([
        'id' => $emploi->id,
        'horaires_raw' => $emploi->horaires,
        'affectations_raw' => $emploi->affectations,
        'semaines_raw' => $emploi->semaines,
        'horaires_type' => gettype($emploi->horaires),
        'horaires_decoded' => is_string($emploi->horaires) ? json_decode($emploi->horaires, true) : $emploi->horaires
    ]);
});
/** -------------------- FILIERES -------------------- **/
Route::get('/filieres', function() {
    try {
        $filieres = \App\Models\Filiere::orderBy('nom')->get();
        return response()->json([
            'success' => true,
            'data' => $filieres
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\EnseignantEmploiController;
use App\Http\Controllers\UserController;

// Supprimez le middleware 'api' ou utilisez-le correctement
Route::group([], function () {
    // Récupérer tous les utilisateurs
    Route::get('/users', [UserController::class, 'index']);
    
    // Ajouter un utilisateur
    Route::post('/addusers', [UserController::class, 'store']);
    
    // Voir un utilisateur
    Route::get('/users/{id}', [UserController::class, 'show']);
    
    // Mettre à jour un utilisateur
    Route::put('/users/{id}', [UserController::class, 'update']);
    
    // Supprimer un utilisateur
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});

// Routes pour l'emploi du temps
Route::get('/schedule/filieres', [ScheduleController::class, 'getFilieres']);
Route::get('/schedule/niveaux/{filiere_id}', [ScheduleController::class, 'getNiveaux']);
Route::post('/schedule/generate-from-db', [ScheduleController::class, 'generateFromDb']);

// Routes pour l'emploi du temps
// Routes pour l'emploi du temps
// Routes pour le planning
// routes/api.php
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
// Endpoint de débogage pour voir les données brutes
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
// Routes pour les emplois des enseignants
// NOUVELLES ROUTES POUR LES ENSEIGNANTS
Route::prefix('enseignant')->group(function () {
    // Route pour récupérer tous les enseignants
    Route::get('/', function() {
        try {
            $enseignants = \App\Models\Enseignant::orderBy('nom')->get();
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
    
    // Route pour récupérer les emplois d'un enseignant spécifique
    Route::get('/{id}/emplois', [EnseignantEmploiController::class, 'getEmploiForEnseignant']);
    
    // Route pour récupérer tous les enseignants avec leurs emplois
    Route::get('/all/with-emplois', [EnseignantEmploiController::class, 'getAllEnseignantsWithEmplois']);
});

// Routes spécifiques pour EnseignantEmploiController
Route::prefix('enseignant-emploi')->group(function () {
    Route::get('/enseignant/{enseignant_id}', [EnseignantEmploiController::class, 'getEmploiForEnseignant']);
    Route::get('/all-enseignants', [EnseignantEmploiController::class, 'getAllEnseignantsWithEmplois']);
    Route::get('/filiere/{filiere_id}', [EnseignantEmploiController::class, 'getEmploiByFiliere']);
    Route::get('/export/{enseignant_id}/{format?}', [EnseignantEmploiController::class, 'exportEmploiEnseignant']);
});
// Dans routes/api.php
Route::get('/enseignant-emploi/reel/{enseignant_id}', [EnseignantEmploiController::class, 'getEmploiReelEnseignant']);
Route::prefix('emploi-prof')->group(function () {
    // Emploi complet d'un prof
    Route::get('/complet/{enseignant_id}', [EmploiProfController::class, 'getEmploiCompletProf']);
    
    // Emploi par filière et niveau
    Route::get('/filiere/{filiere_id}/niveau/{niveau}', [EmploiProfController::class, 'getEmploiParFiliereNiveau']);
});
// Route pour l'emploi RÉEL de l'enseignant
Route::get('/enseignant-emploi/reel/{enseignant_id}', [EnseignantEmploiController::class, 'getEmploiReelEnseignant']);
// Routes simples
Route::get('/enseignant-emploi/simple/{id}', [EnseignantEmploiController::class, 'getEmploiSimple']);
Route::get('/enseignant-emploi/commun/{id}', [EnseignantEmploiController::class, 'getEmploiAvecModulesCommuns']);


// Routes pour l'emploi du temps des enseignants
Route::prefix('enseignant-emploi')->group(function () {
    Route::get('/enseignants', [EnseignantEmploiController::class, 'getAllEnseignants']);
    Route::get('/emploi/{enseignant_id}', [EnseignantEmploiController::class, 'getEmploiEnseignant']);
    Route::get('/correction/{module}', [EnseignantEmploiController::class, 'getEnseignantsPourCorrection']);
    
    // Vérification d'incohérences (optionnel)
    Route::get('/verifier/{emploi_id}', [EnseignantEmploiController::class, 'verifierIncoherencesEmploi']);
    Route::post('/corriger/{emploi_id}', [EnseignantEmploiController::class, 'appliquerCorrectionsEmploi']);
});

// Route pour appliquer une correction spécifique
Route::post('/emplois/{emploi_id}/correction', function(Request $request, $emploi_id) {
    // Logique pour appliquer la correction
    return response()->json(['success' => true, 'message' => 'Correction appliquée']);
});


Route::prefix('enseignants')->group(function () {
    // Liste des enseignants
    Route::get('/', [EnseignantEmploiController::class, 'getAllEnseignants']);
    
    // Emploi du temps complet d'un enseignant
    Route::get('/{id}/emploi', [EnseignantEmploiController::class, 'getEmploiCompletEnseignant']);
    
    // Statistiques de l'enseignant
    Route::get('/{id}/stats', [EnseignantEmploiController::class, 'getStatsEnseignant']);
    
    // Route de debug
    Route::get('/{id}/debug', [EnseignantEmploiController::class, 'debugEmploi']);
});
Route::get('/emploi', [PlanningController::class, 'getSavedEmplois']);
// ou
Route::get('/emploi/{id}', [PlanningController::class, 'getEmploiById']);
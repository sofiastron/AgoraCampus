<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\SeanceController;
use App\Http\Controllers\AnnonceController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EnseignantController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Routes publiques
|--------------------------------------------------------------------------
*/

// Test API
Route::get('/test', function () {
    return response()->json(['status' => 'ok']);
});

// Auth
Route::post('/login', [AuthController::class, 'login']);

// Annonces (lecture publique)
Route::get('/annonces', [AnnonceController::class, 'index']);

// Documents (lecture publique)
Route::get('/documents/module/{moduleId}', [DocumentController::class, 'documentsModule']);
Route::get('/documents/{id}/download', [DocumentController::class, 'download']);

// Présences publiques
Route::get('/presences/seance/{seanceId}', [PresenceController::class, 'presencesSeance']);

// Profil enseignant
Route::get('/enseignant/{id}', [EnseignantController::class, 'profil']);

/*
|--------------------------------------------------------------------------
| Routes protégées (auth:sanctum)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // Dashboard
    Route::get('/teacher/dashboard-stats', [DashboardController::class, 'stats']);
    Route::get('/teacher/presence-stats-by-module', [DashboardController::class, 'presenceStatsByModule']);

    // Modules
    Route::get('/teacher/modules', [ModuleController::class, 'mesModules']);
    Route::post('/modules', [ModuleController::class, 'store']);

    // Séances
    Route::post('/seances', [SeanceController::class, 'store']);
    Route::get('/seances/{id}/qrcode', [SeanceController::class, 'genererQRCode']);

    // Annonces (enseignants uniquement)
    Route::post('/annonces', [AnnonceController::class, 'store']);
    Route::put('/annonces/{id}', [AnnonceController::class, 'update']);
    Route::delete('/annonces/{id}', [AnnonceController::class, 'destroy']);

    // Documents (enseignants uniquement)
    Route::post('/documents', [DocumentController::class, 'store']);
    Route::delete('/documents/{id}', [DocumentController::class, 'destroy']);

    // Présences
    Route::post('/presences', [PresenceController::class, 'enregistrerPresence']);
    Route::post('/presences/face-recognition', [PresenceController::class, 'faceRecognition']);
    Route::get('/teacher/etudiants-presence', [PresenceController::class, 'getEtudiantsPresence']);
});

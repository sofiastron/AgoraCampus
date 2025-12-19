<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\SeanceController;
use App\Http\Controllers\AnnonceController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EnseignantController;

// Route test simple
Route::get('/test', function() {
    return response()->json(['status' => 'ok']);
});

// Auth
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);


// Modules
Route::get('/teacher/modules/{enseignantId}', [ModuleController::class, 'mesModules']);
Route::post('/modules', [ModuleController::class, 'store']);



// Séances
Route::get('/seances/module/{moduleId}', [SeanceController::class, 'seancesParModule']);
Route::post('/seances', [SeanceController::class, 'store']);
Route::get('/seances/{seanceId}/qrcode', [SeanceController::class, 'genererQRCode']);
Route::get('/seances/{id}', [SeanceController::class, 'show']);

// Annonces
Route::get('/annonces/module/{moduleId}', [AnnonceController::class, 'annoncesModule']);
Route::post('/annonces', [AnnonceController::class, 'store']);

// Présences
Route::get('presences/seance/{seanceId}', [PresenceController::class, 'presencesSeance']);
Route::post('presences', [PresenceController::class, 'enregistrerPresence']);
Route::post('presences/face-recognition', [PresenceController::class, 'faceRecognition']);
// Documents
Route::get('/documents/module/{moduleId}', [DocumentController::class, 'documentsModule']);
Route::post('/documents', [DocumentController::class, 'store']);

// Enseignant profil
Route::get('/enseignant/{id}', [EnseignantController::class, 'profil']);

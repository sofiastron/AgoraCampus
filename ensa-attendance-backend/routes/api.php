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

Route::get('/test', function () {
    return response()->json(['status' => 'ok']);
});

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {


    Route::post('/logout', [AuthController::class, 'logout']);


    Route::get('/teacher/dashboard-stats', [DashboardController::class, 'stats']);
    Route::get('/teacher/presence-stats-by-module', [DashboardController::class, 'presenceStatsByModule']);


    Route::get('/teacher/modules', [ModuleController::class, 'mesModules']);
    Route::post('/modules', [ModuleController::class, 'store']);

   Route::post('/seances', [SeanceController::class, 'store']);

    Route::get('/seances/{id}/qrcode', [SeanceController::class, 'genererQRCode']);

    Route::post('/annonces', [AnnonceController::class, 'store']);


    Route::post('/presences', [PresenceController::class, 'enregistrerPresence']);
    Route::post('/presences/face-recognition', [PresenceController::class, 'faceRecognition']);


    Route::post('/documents', [DocumentController::class, 'store']);
});


Route::get('/annonces/module/{moduleId}', [AnnonceController::class, 'annoncesModule']);
Route::get('/presences/seance/{seanceId}', [PresenceController::class, 'presencesSeance']);
Route::get('/documents/module/{moduleId}', [DocumentController::class, 'documentsModule']);


Route::get('/enseignant/{id}', [EnseignantController::class, 'profil']);
Route::middleware('auth:sanctum')->group(function () {


    Route::get('/teacher/etudiants-presence', [PresenceController::class, 'getEtudiantsPresence']);
});

<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Etudiant\EtudiantDashboardController;
use App\Http\Controllers\Etudiant\ModuleController;
use App\Http\Controllers\Etudiant\SeanceController;
use App\Http\Controllers\Etudiant\PresenceController;
use App\Http\Controllers\Etudiant\DocumentController;
use App\Http\Controllers\Etudiant\AnnonceController;
use App\Http\Controllers\Etudiant\NotificationController;
Route::middleware('auth:sanctum')->prefix('etudiant')->group(function () {

    Route::get('/dashboard', [EtudiantDashboardController::class, 'index']);

    Route::get('/modules', [ModuleController::class, 'index']);
    Route::get('/modules/{id}', [ModuleController::class, 'show']);

    Route::get('/modules/{id}/seances', [SeanceController::class, 'index']);

    Route::get('/presences', [PresenceController::class, 'index']);
    Route::post('/presence/{seanceId}', [PresenceController::class, 'store']);

    Route::get('/modules/{id}/documents', [DocumentController::class, 'index']);

    Route::get('/modules/{id}/annonces', [AnnonceController::class, 'index']);
    Route::get('/notifications', [NotificationController::class, 'index']);
});
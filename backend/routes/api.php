<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Etudiant\EtudiantDashboardController;
use App\Http\Controllers\Etudiant\ModuleController;
use App\Http\Controllers\Etudiant\SeanceController;
use App\Http\Controllers\Etudiant\PresenceController;
use App\Http\Controllers\Etudiant\DocumentController;
use App\Http\Controllers\Etudiant\AnnonceController;
use App\Http\Controllers\Etudiant\NotificationController;
use App\Http\Controllers\Auth\AuthController;



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);


Route::middleware('auth:sanctum')->prefix('etudiant')->group(function () {

    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile/change-password', [AuthController::class, 'changePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/dashboard', [EtudiantDashboardController::class, 'index']);
   


    Route::get('/modules', [ModuleController::class, 'index']);
    Route::get('/modules/{id}', [ModuleController::class, 'show']);

    Route::get('/modules/{id}/seances', [SeanceController::class, 'index']);

    Route::get('/presences', [PresenceController::class, 'index']);
    Route::post('/presence/{seanceId}', [PresenceController::class, 'store']);

    Route::get('/modules/{id}/documents', [DocumentController::class, 'index']);

    Route::get('/modules/{id}/annonces', [AnnonceController::class, 'index']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/profile/photo', [AuthController::class, 'updatePhoto']);

});
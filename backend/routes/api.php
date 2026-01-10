<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
use App\Http\Controllers\EtudiantController;
use App\Http\Controllers\EmploiController;
use App\Http\Controllers\EnseignantController;

Route::get('/etudiants', [EtudiantController::class, 'index']);
Route::post('/etudiants', [EtudiantController::class, 'store']);
Route::delete('/etudiants/{id}', [EtudiantController::class, 'destroy']);

Route::get('/emplois', [EmploiController::class, 'index']);
Route::post('/emplois', [EmploiController::class, 'store']);
Route::post('/emplois/{id}/valider', [EmploiController::class, 'valider']);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/enseignants', [EnseignantController::class, 'index']);
Route::post('/enseignants', [EnseignantController::class, 'store']);
Route::put('/enseignants/{id}', [EnseignantController::class, 'update']);
Route::delete('/enseignants/{id}', [EnseignantController::class, 'destroy']);

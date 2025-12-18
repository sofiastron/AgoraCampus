<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
// Route de test
Route::get('/test', function() {
    return response()->json(['status' => 'ok']);
});
// Route login
Route::post('/login', [AuthController::class, 'login']);

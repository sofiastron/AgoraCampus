<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Seance;

class SeanceController extends Controller
{
    public function index($moduleId)
    {
        return response()->json(
            Seance::where('module_id', $moduleId)->get()
        );
    }

    public function show($id)
    {
        return response()->json(
            Seance::with('module')->findOrFail($id)
        );
    }
}
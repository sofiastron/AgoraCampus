<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Document;

class DocumentController extends Controller
{
    public function index($moduleId)
    {
        return response()->json(
            Document::where('idModule', $moduleId)->get()
        );
    }

    public function show($id)
    {
        return response()->json(
            Document::findOrFail($id)
        );
    }
}
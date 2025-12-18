<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function documentsModule($moduleId)
    {
        return response()->json([
            'success' => true,
            'documents' => Document::where('module_id', $moduleId)->get()
        ]);
    }

    public function store(Request $request)
    {
        return response()->json([
            'success' => true,
            'document' => Document::create($request->all())
        ]);
    }
}

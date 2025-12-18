<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function mesModules($enseignantId)
    {
        return response()->json([
            'success' => true,
            'modules' => Module::where('enseignant_id', $enseignantId)->get()
        ]);
    }

    public function store(Request $request)
    {
        return response()->json([
            'success' => true,
            'module' => Module::create($request->all())
        ]);
    }
}

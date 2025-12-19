<?php

namespace App\Http\Controllers;

use App\Models\Seance;
use App\Models\GenerateurQRCode;
use Illuminate\Http\Request;

class SeanceController extends Controller
{
    public function seancesParModule($moduleId)
    {
        return response()->json([
            'success' => true,
            'seances' => Seance::where('module_id', $moduleId)->get()
        ]);
    }

    public function store(Request $request)
    {
       
        $validatedData = $request->validate([
            'date' => 'required|date',
            'module_id' => 'required|integer|exists:modules,id',
            'heure_debut' => 'required|date_format:H:i:s',
            'heure_fin' => 'required|date_format:H:i:s',
            'qr_code' => 'nullable|string',
        ]);
        $seance = Seance::create($validatedData);

        return response()->json([
            'success' => true,
            'seance' => $seance
        ]);
    }

    public function genererQRCode($seanceId)
    {
        $qr = GenerateurQRCode::create([
            'contenu' => uniqid('QR_'),
            'dateExpiration' => now()->addMinutes(15),
            'seance_id' => $seanceId
        ]);

        return response()->json([
            'success' => true,
            'qrcode' => $qr
        ]);
    }
    public function show($id)
{
    $seance = Seance::find($id);

    if (!$seance) {
        return response()->json([
            'success' => false,
            'message' => 'Séance non trouvée'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'seance' => $seance
    ]);
}

}

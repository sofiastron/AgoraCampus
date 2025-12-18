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
        return response()->json([
            'success' => true,
            'seance' => Seance::create($request->all())
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
}

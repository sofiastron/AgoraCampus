<?php

namespace App\Http\Controllers;

use App\Models\Seance;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SeanceController extends Controller
{

    public function store(Request $request)
    {
        $validated = $request->validate([
            'module_id' => 'required|exists:modules,id',
            'date' => 'required|date',
            'heure_debut' => 'required|date_format:H:i',
            'heure_fin' => 'required|date_format:H:i|after:heure_debut',
        ]);

        $seance = Seance::create($validated);

        return response()->json([
            'success' => true,
            'seance' => $seance,
        ]);
    }

   
    public function qrcode(Seance $seance)
    {
        $contenu = "seance_id:" . $seance->id;

        $qr = QrCode::format('png')->size(300)->generate($contenu);

        return response($qr)->header('Content-Type', 'image/png');
    }
}

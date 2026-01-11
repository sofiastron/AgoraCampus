<?php

namespace App\Http\Controllers;

use App\Models\Emploi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmploiValideMail;

class EmploiController extends Controller
{
    public function index() {
        return Emploi::all();
    }

    public function store(Request $request) {
        return Emploi::create($request->all());
    }

    public function valider($id) {
        $emploi = Emploi::find($id);
        $emploi->statut = 'valide';
        $emploi->save();

        Mail::to('admin@mail.com')->send(new EmploiValideMail($emploi));

        return response()->json(['message' => 'Emploi validé']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    // Liste des documents par module
    public function documentsModule($moduleId)
    {
        return response()->json([
            'success' => true,
            'documents' => Document::where('module_id', $moduleId)
                ->with('enseignant.utilisateur')
                ->orderBy('date_upload', 'desc')
                ->get()
        ]);
    }

    // Upload d'un document
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:100',
            'fichier' => 'required|file|mimes:pdf,ppt,pptx,doc,docx|max:10240', // max 10MB
            'module_id' => 'required|exists:modules,id',
        ]);

        // Upload du fichier
        $file = $request->file('fichier');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('documents', $filename, 'public');

        $document = Document::create([
            'titre' => $validated['titre'],
            'chemin_fichier' => $path,
            'type_document' => $file->getClientOriginalExtension(),
            'date_upload' => now(),
            'module_id' => $validated['module_id'],
            'enseignant_id' => auth()->user()->enseignant->id,
        ]);

        return response()->json([
            'success' => true,
            'document' => $document->load('enseignant.utilisateur', 'module')
        ], 201);
    }

    // Télécharger un document
    public function download($id)
    {
        $document = Document::findOrFail($id);
        return Storage::disk('public')->download($document->chemin_fichier, $document->titre . '.' . $document->type_document);
    }

    // Supprimer un document
    public function destroy($id)
    {
        $document = Document::findOrFail($id);

        if ($document->enseignant_id !== auth()->user()->enseignant->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        // Supprimer le fichier physique
        Storage::disk('public')->delete($document->chemin_fichier);
        
        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Document supprimé'
        ]);
    }
}
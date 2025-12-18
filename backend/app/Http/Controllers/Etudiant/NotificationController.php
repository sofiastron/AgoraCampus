<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Annonce;
use App\Models\Document;
use App\Models\Seance;
use App\Models\Presence;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function index()
    {
        $etudiant = auth()->user()->etudiant;

        if (!$etudiant) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $notifications = [];

        /* notif nouveaux annonces*/
        $annonces = Annonce::whereHas('module.seances.presences', function ($q) use ($etudiant) {
            $q->where('idEtudiant', $etudiant->id);
        })->latest('dateCreation')->take(5)->get();

        foreach ($annonces as $annonce) {
            $notifications[] = [
                'type' => 'annonce',
                'titre' => 'Nouvelle annonce',
                'message' => $annonce->titre,
                'date' => $annonce->dateCreation,
                'module' => $annonce->module->titre ?? null,
                'reference_id' => $annonce->id
            ];
        }

        /*  notif nouveaux document*/
        $documents = Document::whereHas('module.seances.presences', function ($q) use ($etudiant) {
            $q->where('idEtudiant', $etudiant->id);
        })->latest('date_upload')->take(5)->get();

        foreach ($documents as $doc) {
            $notifications[] = [
                'type' => 'document',
                'titre' => 'Nouveau document',
                'message' => $doc->titre,
                'date' => $doc->date_upload,
                'module' => $doc->module->titre ?? null,
                'reference_id' => $doc->id
            ];
        }

        /* notif nouveaux seances*/
        $seances = Seance::whereHas('module.seances.presences', function ($q) use ($etudiant) {
            $q->where('idEtudiant', $etudiant->id);
        })->latest('date')->take(5)->get();

        foreach ($seances as $seance) {
            $notifications[] = [
                'type' => 'seance',
                'titre' => 'Nouvelle séance programmée',
                'message' => 'Séance du ' . $seance->date,
                'date' => $seance->date,
                'module' => $seance->module->titre ?? null,
                'reference_id' => $seance->id
            ];
        }

        /* notif seance aujourdhui*/
        $today = Carbon::today();

        $seancesToday = Seance::whereDate('date', $today)
            ->whereHas('module.seances.presences', function ($q) use ($etudiant) {
                $q->where('idEtudiant', $etudiant->id);
            })->get();

        foreach ($seancesToday as $seance) {
            $notifications[] = [
                'type' => 'alerte',
                'titre' => 'Séance aujourd’hui',
                'message' => 'Séance à ' . $seance->heureDebut,
                'date' => $seance->date,
                'module' => $seance->module->titre ?? null,
                'reference_id' => $seance->id
            ];
        }

        /* notif vous etes abscentes*/
        $absences = Presence::where('idEtudiant', $etudiant->id)
            ->where('statut', 'absent')
            ->latest('horodatage')
            ->take(5)
            ->get();

        foreach ($absences as $absence) {
            $notifications[] = [
                'type' => 'absence',
                'titre' => 'Absence enregistrée',
                'message' => 'Vous étiez absent',
                'date' => $absence->horodatage,
                'module' => $absence->seance->module->titre ?? null,
                'reference_id' => $absence->id
            ];
        }

        /* ordre par la date de chaque notif*/
        usort($notifications, function ($a, $b) {
            return strtotime($b['date']) <=> strtotime($a['date']);
        });

        return response()->json(array_slice($notifications, 0, 20));
    }
}
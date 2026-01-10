<?php

namespace App\Services;

use App\Models\Seance;
use App\Models\Module;
use App\Models\Enseignant;

class EmploiDuTempsService
{
    public function generer()
    {
        // Nettoyer seulement les séances (garder modules et enseignants)
        Seance::query()->delete();
        
        // Récupérer les données existantes
        $modules = Module::all();
        $enseignants = Enseignant::all();
        
        if ($modules->isEmpty()) {
            return [
                'success' => false,
                'message' => '❌ Aucun module trouvé dans la base de données'
            ];
        }
        
        // Configuration de base
        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
        $creneaux = [
            ['debut' => '08:00', 'fin' => '10:00'],
            ['debut' => '10:00', 'fin' => '12:00'],
            ['debut' => '14:00', 'fin' => '16:00'],
            ['debut' => '16:00', 'fin' => '18:00'],
        ];
        
        $planning = [];
        $creneauxOccupes = []; // Pour éviter les conflits
        
        // Pour chaque module
        foreach ($modules as $module) {
            // Déterminer les heures par semaine basé sur la description
            $heuresParSemaine = $this->determinerHeuresParSemaine($module->description);
            
            // Vérifier si le module a un enseignant
            if (!$module->enseignant_id) {
                continue;
            }
            
            $enseignant = $enseignants->firstWhere('id', $module->enseignant_id);
            if (!$enseignant) {
                continue;
            }
            
            $heuresPlacees = 0;
            $tentatives = 0;
            
            // Essayer de placer les heures
            while ($heuresPlacees < $heuresParSemaine && $tentatives < 20) {
                $tentatives++;
                $placee = false;
                
                // Essayer différents jours et créneaux
                foreach ($jours as $jour) {
                    foreach ($creneaux as $creneau) {
                        $cleCreneau = $jour . '_' . $creneau['debut'];
                        
                        // Vérifier si le créneau est libre
                        if (!isset($creneauxOccupes[$cleCreneau])) {
                            // Vérifier si l'enseignant n'est pas déjà occupé à ce créneau
                            $enseignantOccupe = $this->enseignantOccupeALaMemeHeure(
                                $enseignant->id, 
                                $jour, 
                                $creneau['debut'],
                                $creneauxOccupes
                            );
                            
                            if (!$enseignantOccupe) {
                                // Créer la séance
                                $date = $this->calculerProchaineDate($jour);
                                
                                $seance = Seance::create([
                                    'date' => $date,
                                    'heure_debut' => $creneau['debut'],
                                    'heure_fin' => $creneau['fin'],
                                    'qr_code' => $this->genererQRCode(),
                                    'module_id' => $module->id,
                                    'enseignant_id' => $enseignant->id,
                                    'groupe' => $this->determinerGroupe($module)
                                ]);
                                
                                // Ajouter au planning de retour
                                $planning[] = [
                                    'id' => $seance->id,
                                    'date' => $seance->date,
                                    'heure_debut' => $seance->heure_debut,
                                    'heure_fin' => $seance->heure_fin,
                                    'qr_code' => $seance->qr_code,
                                    'groupe' => $seance->groupe,
                                    'jour' => $jour, // Ajouté pour l'affichage
                                    'module_id' => $module->id,
                                    'module_nom' => $module->titre,
                                    'enseignant_id' => $enseignant->id,
                                    'enseignant_nom' => $enseignant->nom
                                ];
                                
                                // Marquer le créneau comme occupé
                                $creneauxOccupes[$cleCreneau] = [
                                    'enseignant_id' => $enseignant->id,
                                    'module_id' => $module->id
                                ];
                                
                                $heuresPlacees += 2; // 2 heures par créneau
                                $placee = true;
                                break 2; // Sortir des deux boucles
                            }
                        }
                    }
                }
                
                if (!$placee) {
                    // Aucun créneau libre trouvé pour ce module
                    break;
                }
            }
        }
        
        return [
            'success' => true,
            'message' => '✅ Emploi du temps généré avec succès !',
            'planning' => $planning,
            'statistiques' => [
                'total_seances' => count($planning),
                'heures_totales' => count($planning) * 2,
                'modules_utilises' => count(array_unique(array_column($planning, 'module_id'))),
                'enseignants_impliques' => count(array_unique(array_column($planning, 'enseignant_id')))
            ]
        ];
    }
    
    private function determinerHeuresParSemaine($description)
    {
        if (empty($description)) {
            return 4; // Valeur par défaut
        }
        
        // Essayer d'extraire les heures depuis la description
        if (preg_match('/(\d+)\s*h/i', $description, $matches)) {
            $heures = (int) $matches[1];
            return min($heures, 12); // Maximum 12 heures
        }
        
        // Heures par défaut basées sur le contenu
        $descLower = strtolower($description);
        
        if (str_contains($descLower, 'intensif') || str_contains($descLower, 'avancé')) {
            return 8;
        }
        
        if (str_contains($descLower, 'normal') || str_contains($descLower, 'standard')) {
            return 6;
        }
        
        return 4; // Valeur par défaut
    }
    
    private function enseignantOccupeALaMemeHeure($enseignantId, $jour, $heure, $creneauxOccupes)
    {
        foreach ($creneauxOccupes as $cle => $info) {
            if (str_starts_with($cle, $jour . '_') && 
                $info['enseignant_id'] == $enseignantId) {
                return true;
            }
        }
        return false;
    }
    
    private function determinerGroupe($module)
    {
        // Basé sur le titre ou description
        $desc = strtolower($module->description ?? '');
        $titre = strtolower($module->titre ?? '');
        
        if (str_contains($desc, 'master') || str_contains($titre, 'master')) {
            return 'Master';
        }
        
        if (str_contains($desc, 'licence') || str_contains($titre, 'licence')) {
            // Essayer d'extraire l'année
            if (preg_match('/licence\s*(\d)/i', $desc . ' ' . $titre, $matches)) {
                return 'L' . $matches[1];
            }
            return 'Licence';
        }
        
        return 'G1'; // Groupe par défaut
    }
    
    private function genererQRCode()
    {
        return 'QR' . strtoupper(substr(md5(uniqid()), 0, 8));
    }
    
    private function calculerProchaineDate($jour)
    {
        $joursMap = [
            'Lundi' => 1,
            'Mardi' => 2,
            'Mercredi' => 3,
            'Jeudi' => 4,
            'Vendredi' => 5,
            'Samedi' => 6
        ];
        
        $jourNumero = $joursMap[$jour] ?? 1;
        $aujourdhui = date('N'); // 1 (lundi) à 7 (dimanche)
        
        $joursAAjouter = $jourNumero - $aujourdhui;
        if ($joursAAjouter < 0) {
            $joursAAjouter += 7;
        } elseif ($joursAAjouter == 0) {
            $joursAAjouter = 7; // Semaine prochaine
        }
        
        return date('Y-m-d', strtotime("+$joursAAjouter days"));
    }
    
    public function viderPlanning()
    {
        Seance::query()->delete();
        return [
            'success' => true,
            'message' => '🗑️ Planning vidé avec succès'
        ];
    }
}
<?php

namespace App\Services;

use App\Models\Filiere;
use App\Models\Semestre;
use App\Models\Affectation;
use App\Models\DisponibiliteEnseignant;
use App\Models\Salle;
use App\Models\Seance;
use App\Models\EmploiDuTemps;
use App\Models\Enseignant;
use Illuminate\Support\Facades\Log;

class GenerateurEmploiService
{
    public function generer($filiereId, $semestreId, $adminId = null)
    {
        try {
            Log::info("Début génération emploi - Filiere: {$filiereId}, Semestre: {$semestreId}");
            
            // 1. Créer l'emploi du temps
            $emploi = EmploiDuTemps::create([
                'filiere_id' => $filiereId,
                'semestre_id' => $semestreId,
                'statut' => 'genere',
                'date_generation' => now(),
                'admin_id' => $adminId
            ]);
            
            Log::info("Emploi créé - ID: {$emploi->id}");
            
            // 2. Récupérer les affectations
            $affectations = Affectation::with([
                'enseignant.disponibilites',
                'module'
            ])
            ->where('filiere_id', $filiereId)
            ->where('semestre_id', $semestreId)
            ->get();
            
            Log::info("Affectations trouvées: " . $affectations->count());
            
            // 3. Récupérer les salles disponibles
            $salles = Salle::where('disponible', true)->get();
            Log::info("Salles disponibles: " . $salles->count());
            
            // 4. Configuration
            $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
            $creneaux = [
                ['debut' => '08:00', 'fin' => '10:00'],
                ['debut' => '10:00', 'fin' => '12:00'],
                ['debut' => '14:00', 'fin' => '16:00'],
                ['debut' => '16:00', 'fin' => '18:00'],
            ];
            
            $seancesCreees = [];
            $occupations = []; // Format: 'Lundi_08:00' => ['enseignant_id' => X, 'salle_id' => Y]
            $enseignantsHeures = []; // Suivi des heures par enseignant
            
            // 5. Traiter chaque affectation
            foreach ($affectations as $affectation) {
                $enseignantId = $affectation->enseignant_id;
                $moduleId = $affectation->module_id;
                $heuresARepartir = $affectation->volume_horaire;
                
                Log::info("Traitement - Enseignant: {$enseignantId}, Module: {$moduleId}, Heures: {$heuresARepartir}");
                
                // Initialiser compteur d'heures pour cet enseignant
                if (!isset($enseignantsHeures[$enseignantId])) {
                    $enseignantsHeures[$enseignantId] = 0;
                }
                
                // Récupérer les disponibilités de cet enseignant
                $dispos = DisponibiliteEnseignant::where('enseignant_id', $enseignantId)
                    ->where('disponible', true)
                    ->get()
                    ->keyBy(function($item) {
                        return $item->jour . '_' . $item->creneau;
                    });
                
                $heuresPlacees = 0;
                $tentatives = 0;
                $maxTentatives = 20;
                
                // Essayer de placer les heures
                while ($heuresPlacees < $heuresARepartir && $tentatives < $maxTentatives) {
                    $tentatives++;
                    $placee = false;
                    
                    // Essayer chaque jour et créneau
                    foreach ($jours as $jour) {
                        foreach ($creneaux as $creneau) {
                            $creneauStr = $creneau['debut'] . '-' . $creneau['fin'];
                            $cle = $jour . '_' . $creneau['debut'];
                            
                            // Vérifier si l'enseignant est disponible
                            $dispoKey = $jour . '_' . $creneauStr;
                            if (!$dispos->has($dispoKey)) {
                                continue; // Pas disponible
                            }
                            
                            // Vérifier si l'enseignant n'est pas déjà occupé
                            if ($this->enseignantOccupe($enseignantId, $jour, $creneau['debut'], $occupations)) {
                                continue;
                            }
                            
                            // Vérifier la limite d'heures par enseignant
                            $enseignant = Enseignant::find($enseignantId);
                            $limiteHeures = $enseignant->heures_max_semaine ?? 18;
                            
                            if ($enseignantsHeures[$enseignantId] >= $limiteHeures) {
                                Log::warning("Enseignant {$enseignantId} a atteint sa limite d'heures");
                                continue;
                            }
                            
                            // Trouver une salle disponible
                            $salle = $this->trouverSalleDisponible(
                                $jour,
                                $creneau['debut'],
                                $salles,
                                $occupations
                            );
                            
                            if ($salle) {
                                // Calculer la date
                                $date = $this->calculerDateProchaine($jour);
                                
                                // Créer la séance
                                $seance = Seance::create([
                                    'emploi_temps_id' => $emploi->id,
                                    'module_id' => $moduleId,
                                    'enseignant_id' => $enseignantId,
                                    'salle_id' => $salle->id,
                                    'groupe' => 'G1', // À améliorer avec groupes réels
                                    'date' => $date,
                                    'jour' => $jour,
                                    'heure_debut' => $creneau['debut'],
                                    'heure_fin' => $creneau['fin'],
                                    'qr_code' => $this->genererQRCode(),
                                    'statut' => 'planifiee'
                                ]);
                                
                                // Mettre à jour les occupations
                                $occupations[$cle] = [
                                    'enseignant_id' => $enseignantId,
                                    'salle_id' => $salle->id,
                                    'module_id' => $moduleId,
                                    'seance_id' => $seance->id
                                ];
                                
                                // Mettre à jour les compteurs
                                $enseignantsHeures[$enseignantId] += 2;
                                $heuresPlacees += 2;
                                $seancesCreees[] = $seance;
                                
                                Log::info("Séance créée - {$jour} {$creneau['debut']} - Module: {$affectation->module->titre}");
                                
                                $placee = true;
                                break 2; // Sortir des boucles jour/créneau
                            }
                        }
                    }
                    
                    if (!$placee) {
                        Log::warning("Aucun créneau trouvé pour l'affectation {$affectation->id}");
                        break;
                    }
                }
                
                if ($heuresPlacees < $heuresARepartir) {
                    Log::warning("Module {$moduleId}: seulement {$heuresPlacees}/{$heuresARepartir} heures placées");
                }
            }
            
            return [
                'success' => true,
                'message' => 'Emploi du temps généré avec succès',
                'emploi_id' => $emploi->id,
                'seances_crees' => count($seancesCreees),
                'statistiques' => [
                    'enseignants_impliques' => count(array_unique(array_column($occupations, 'enseignant_id'))),
                    'salles_utilisees' => count(array_unique(array_column($occupations, 'salle_id'))),
                    'heures_totales' => count($seancesCreees) * 2
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error('Erreur génération emploi: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return [
                'success' => false,
                'message' => 'Erreur lors de la génération: ' . $e->getMessage()
            ];
        }
    }
    
    private function enseignantOccupe($enseignantId, $jour, $heure, $occupations)
    {
        foreach ($occupations as $cle => $info) {
            if (str_starts_with($cle, $jour . '_') && 
                $info['enseignant_id'] == $enseignantId) {
                return true;
            }
        }
        return false;
    }
    
    private function trouverSalleDisponible($jour, $heure, $salles, $occupations)
    {
        foreach ($salles as $salle) {
            $salleOccupee = false;
            
            foreach ($occupations as $cle => $info) {
                if (str_starts_with($cle, $jour . '_') && 
                    $info['salle_id'] == $salle->id) {
                    $salleOccupee = true;
                    break;
                }
            }
            
            if (!$salleOccupee) {
                return $salle;
            }
        }
        return null;
    }
    
    private function calculerDateProchaine($jour)
    {
        $joursMap = [
            'Lundi' => 1,
            'Mardi' => 2,
            'Mercredi' => 3,
            'Jeudi' => 4,
            'Vendredi' => 5
        ];
        
        $jourNumero = $joursMap[$jour] ?? 1;
        $aujourdhui = date('N'); // 1 (lundi) à 7 (dimanche)
        
        $joursAAjouter = $jourNumero - $aujourdhui;
        if ($joursAAjouter < 0) {
            $joursAAjouter += 7;
        } elseif ($joursAAjouter == 0) {
            $joursAAjouter = 7;
        }
        
        return date('Y-m-d', strtotime("+$joursAAjouter days"));
    }
    
    private function genererQRCode()
    {
        return 'QR' . strtoupper(substr(md5(uniqid()), 0, 8));
    }
}
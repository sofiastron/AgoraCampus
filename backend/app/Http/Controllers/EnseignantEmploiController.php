<?php

namespace App\Http\Controllers;

use App\Models\EmploiTemps;
use App\Models\Enseignant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnseignantEmploiController extends Controller
{
    /**
     * Récupère tous les enseignants
     */
    public function getAllEnseignants()
    {
        try {
            $enseignants = Enseignant::select('id', 'nom', 'specialite', 'email', 'statut')
                ->orderBy('nom')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $enseignants
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur getAllEnseignants: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Récupère l'emploi du temps COMPLET d'un enseignant
     */
    public function getEmploiCompletEnseignant($enseignant_id)
    {
        try {
            $enseignant = Enseignant::find($enseignant_id);
            
            if (!$enseignant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Enseignant non trouvé'
                ], 404);
            }
            
            // Récupérer TOUS les emplois du temps où l'enseignant est affecté
            $emplois = EmploiTemps::with('filiere')
                ->whereIn('statut', ['valide', 'en_attente'])
                ->get();
            
            // Structure pour l'emploi de la semaine
            $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
            $creneaux = ['08h-10h', '10h-12h', '12h-14h', '14h-16h', '16h-18h'];
            
            $emploiSemaine = $this->initEmploiVide($jours, $creneaux);
            $tousCours = [];
            $incoherences = [];
            
            $totalHeures = 0;
            $totalEmplois = 0;
            $groupesUniques = [];
            
            // Parcourir tous les emplois
            foreach ($emplois as $emploi) {
                // CORRECTION ICI : Ne pas utiliser json_decode() !
                // Laravel décode automatiquement les champs JSON en array
                $horaires = $emploi->horaires ?? [];
                $affectations = $emploi->affectations ?? [];
                
                // Si c'est encore une string, alors décoder
                if (is_string($horaires)) {
                    $horaires = json_decode($horaires, true) ?? [];
                }
                if (is_string($affectations)) {
                    $affectations = json_decode($affectations, true) ?? [];
                }
                
                if (empty($horaires) || empty($affectations)) {
                    continue;
                }
                
                $emploiACours = false;
                
                // Parcourir tous les groupes de cet emploi
                foreach ($horaires as $groupe => $joursHoraires) {
                    if (!is_array($joursHoraires)) continue;
                    
                    foreach ($joursHoraires as $jour => $creneauxHoraires) {
                        if (!is_array($creneauxHoraires)) continue;
                        
                        foreach ($creneauxHoraires as $creneau => $module) {
                            if (empty($module)) continue;
                            
                            // Vérifier si ce créneau est affecté à notre enseignant
                            $enseignantAffecte = null;
                            
                            // Accès sécurisé aux données
                            if (isset($affectations[$groupe]) &&
                                isset($affectations[$groupe][$jour]) &&
                                isset($affectations[$groupe][$jour][$creneau]) &&
                                isset($affectations[$groupe][$jour][$creneau]['enseignant']) &&
                                isset($affectations[$groupe][$jour][$creneau]['enseignant']['id'])) {
                                
                                $enseignantAffecte = $affectations[$groupe][$jour][$creneau]['enseignant']['id'];
                            }
                            
                            if ($enseignantAffecte == $enseignant_id) {
                                $emploiACours = true;
                                
                                // Enregistrer le groupe unique
                                if (!in_array($groupe, $groupesUniques)) {
                                    $groupesUniques[] = $groupe;
                                }
                                
                                // Récupérer les informations du cours
                                $salleNom = 'Non spécifiée';
                                $salleBatiment = '';
                                
                                if (isset($affectations[$groupe][$jour][$creneau]['salle']['nom'])) {
                                    $salleNom = $affectations[$groupe][$jour][$creneau]['salle']['nom'];
                                }
                                if (isset($affectations[$groupe][$jour][$creneau]['salle']['batiment'])) {
                                    $salleBatiment = $affectations[$groupe][$jour][$creneau]['salle']['batiment'];
                                }
                                
                                $cours = [
                                    'emploi_id' => $emploi->id,
                                    'emploi_titre' => $emploi->titre,
                                    'filiere' => $emploi->filiere->nom ?? 'Inconnue',
                                    'semestre' => $emploi->semestre,
                                    'niveau' => $emploi->niveau,
                                    'groupe' => $groupe,
                                    'jour' => $jour,
                                    'creneau' => $creneau,
                                    'module' => $module,
                                    'salle' => $salleNom,
                                    'batiment' => $salleBatiment,
                                    'incoherent' => false,
                                    'enseignant_id' => $enseignantAffecte
                                ];
                                
                                // Vérifier l'incohérence (prof de langue vs module technique)
                                if ($this->estIncoherent($module, $enseignant)) {
                                    $cours['incoherent'] = true;
                                    $cours['probleme'] = $this->getMessageIncoherence($module, $enseignant);
                                    $incoherences[] = $cours;
                                }
                                
                                $tousCours[] = $cours;
                                $totalHeures += 2; // Chaque créneau = 2 heures
                                
                                // Ajouter à l'emploi de la semaine
                                $this->ajouterAuEmploiSemaine($emploiSemaine, $jour, $creneau, $cours);
                            }
                        }
                    }
                }
                
                if ($emploiACours) {
                    $totalEmplois++;
                }
            }
            
            // Trier les cours
            $tousCours = $this->trierCours($tousCours);
            
            return response()->json([
                'success' => true,
                'enseignant' => [
                    'id' => $enseignant->id,
                    'nom' => $enseignant->nom,
                    'specialite' => $enseignant->specialite,
                    'email' => $enseignant->email,
                    'statut' => $enseignant->statut,
                    'total_heures' => $totalHeures
                ],
                'emploi_semaine' => $emploiSemaine,
                'cours_tous' => $tousCours,
                'incoherences' => $incoherences,
                'stats' => [
                    'total_cours' => count($tousCours),
                    'total_heures' => $totalHeures,
                    'total_emplois' => $totalEmplois,
                    'total_groupes' => count($groupesUniques),
                    'total_incoherences' => count($incoherences)
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur getEmploiCompletEnseignant: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false, 
                'message' => 'Erreur serveur: ' . $e->getMessage(),
                'trace' => env('APP_DEBUG') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Récupère les statistiques d'un enseignant
     */
    public function getStatsEnseignant($enseignant_id)
    {
        try {
            $enseignant = Enseignant::findOrFail($enseignant_id);
            
            // Compter les modules attribués dans la table modules
            $modulesCount = DB::table('modules')
                ->where('enseignant_id', $enseignant_id)
                ->count();
            
            // Calculer les heures dans les emplois
            $statsEmplois = $this->calculerStatsEmplois($enseignant_id);
            
            return response()->json([
                'success' => true,
                'stats' => [
                    'modules_attribues' => $modulesCount,
                    'heures_semaine' => $statsEmplois['total_heures'],
                    'emplois_participation' => $statsEmplois['total_emplois'],
                    'groupes_enseignes' => $statsEmplois['total_groupes'],
                    'cours_semaine' => $statsEmplois['total_cours']
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur getStatsEnseignant: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Calcul des statistiques des emplois
     */
    private function calculerStatsEmplois($enseignant_id)
    {
        $totalHeures = 0;
        $totalEmplois = 0;
        $totalCours = 0;
        $groupes = [];
        
        $emplois = EmploiTemps::whereIn('statut', ['valide', 'en_attente'])->get();
        
        foreach ($emplois as $emploi) {
            $affectations = $emploi->affectations;
            
            // Convertir si nécessaire
            if (is_string($affectations)) {
                $affectations = json_decode($affectations, true) ?? [];
            }
            
            $emploiHeures = 0;
            $emploiCours = 0;
            
            if (is_array($affectations)) {
                foreach ($affectations as $groupe => $jours) {
                    if (is_array($jours)) {
                        foreach ($jours as $jour => $creneaux) {
                            if (is_array($creneaux)) {
                                foreach ($creneaux as $creneau => $data) {
                                    if (is_array($data) && 
                                        isset($data['enseignant']['id']) && 
                                        $data['enseignant']['id'] == $enseignant_id) {
                                        
                                        $emploiHeures += 2;
                                        $emploiCours += 1;
                                        $totalHeures += 2;
                                        $totalCours += 1;
                                        
                                        if (!in_array($groupe, $groupes)) {
                                            $groupes[] = $groupe;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
            if ($emploiHeures > 0) {
                $totalEmplois++;
            }
        }
        
        return [
            'total_heures' => $totalHeures,
            'total_emplois' => $totalEmplois,
            'total_cours' => $totalCours,
            'total_groupes' => count($groupes)
        ];
    }

    /**
     * Version DEBUG pour voir la structure des données
     */
    public function debugEmploi($enseignant_id)
    {
        try {
            $enseignant = Enseignant::find($enseignant_id);
            
            if (!$enseignant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Enseignant non trouvé'
                ], 404);
            }
            
            $emplois = EmploiTemps::with('filiere')
                ->whereIn('statut', ['valide', 'en_attente'])
                ->get();
            
            $debugInfo = [];
            
            foreach ($emplois as $emploi) {
                $debugInfo[] = [
                    'emploi_id' => $emploi->id,
                    'titre' => $emploi->titre,
                    'horaires_type' => gettype($emploi->horaires),
                    'affectations_type' => gettype($emploi->affectations),
                    'horaires_preview' => is_string($emploi->horaires) ? 
                        substr($emploi->horaires, 0, 100) . '...' : 
                        'Non-string',
                    'affectations_preview' => is_string($emploi->affectations) ? 
                        substr($emploi->affectations, 0, 100) . '...' : 
                        'Non-string'
                ];
                
                // Vérifier si cet emploi contient notre enseignant
                $affectations = $emploi->affectations;
                if (is_string($affectations)) {
                    $affectations = json_decode($affectations, true);
                }
                
                if (is_array($affectations)) {
                    foreach ($affectations as $groupe => $jours) {
                        if (is_array($jours)) {
                            foreach ($jours as $jour => $creneaux) {
                                if (is_array($creneaux)) {
                                    foreach ($creneaux as $creneau => $data) {
                                        if (isset($data['enseignant']['id']) && 
                                            $data['enseignant']['id'] == $enseignant_id) {
                                            $debugInfo[count($debugInfo)-1]['found'] = true;
                                            $debugInfo[count($debugInfo)-1]['cours'][] = [
                                                'groupe' => $groupe,
                                                'jour' => $jour,
                                                'creneau' => $creneau,
                                                'module' => $data['module'] ?? 'Inconnu'
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'enseignant' => $enseignant,
                'debug_info' => $debugInfo,
                'total_emplois' => count($emplois)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Méthodes utilitaires
     */
    private function initEmploiVide($jours, $creneaux)
    {
        $emploi = [];
        foreach ($jours as $jour) {
            $emploi[$jour] = [];
            foreach ($creneaux as $creneau) {
                $emploi[$jour][$creneau] = [
                    'cours' => [],
                    'occupe' => false,
                    'multiple' => false
                ];
            }
        }
        return $emploi;
    }

    private function ajouterAuEmploiSemaine(&$emploiSemaine, $jour, $creneau, $cours)
    {
        if (!isset($emploiSemaine[$jour][$creneau])) {
            return;
        }
        
        $emploiSemaine[$jour][$creneau]['cours'][] = $cours;
        $emploiSemaine[$jour][$creneau]['occupe'] = true;
        
        if (count($emploiSemaine[$jour][$creneau]['cours']) > 1) {
            $emploiSemaine[$jour][$creneau]['multiple'] = true;
        }
    }

    private function trierCours($cours)
    {
        $ordreJours = ['Lundi' => 1, 'Mardi' => 2, 'Mercredi' => 3, 'Jeudi' => 4, 'Vendredi' => 5];
        $ordreCreneaux = ['08h-10h' => 1, '10h-12h' => 2, '12h-14h' => 3, '14h-16h' => 4, '16h-18h' => 5];
        
        usort($cours, function($a, $b) use ($ordreJours, $ordreCreneaux) {
            if ($ordreJours[$a['jour']] != $ordreJours[$b['jour']]) {
                return $ordreJours[$a['jour']] - $ordreJours[$b['jour']];
            }
            return $ordreCreneaux[$a['creneau']] - $ordreCreneaux[$b['creneau']];
        });
        
        return $cours;
    }

    private function estIncoherent($module, $enseignant)
    {
        $moduleLower = strtolower($module);
        $specialite = strtolower($enseignant->specialite);
        
        // Si prof de langue
        if (strpos($specialite, 'langues') !== false) {
            // Vérifier si c'est un module de langue
            $modulesLangue = ['anglais', 'français', 'francais', 'communication'];
            foreach ($modulesLangue as $langue) {
                if (strpos($moduleLower, $langue) !== false) {
                    return false; // C'est cohérent
                }
            }
            return true; // Prof de langue enseigne autre chose -> INCOHERENT
        }
        
        // Si module de langue
        if (strpos($moduleLower, 'anglais') !== false || 
            strpos($moduleLower, 'français') !== false ||
            strpos($moduleLower, 'francais') !== false) {
            // Vérifier si c'est un prof de langue
            return strpos($specialite, 'langues') === false;
        }
        
        return false;
    }

    private function getMessageIncoherence($module, $enseignant)
    {
        $specialite = $enseignant->specialite;
        $moduleLower = strtolower($module);
        
        if (strpos($specialite, 'Langues') !== false) {
            if (strpos($moduleLower, 'math') !== false) {
                return "Professeur de langues enseigne des mathématiques";
            }
            if (strpos($moduleLower, 'programmation') !== false) {
                return "Professeur de langues enseigne de la programmation";
            }
            if (strpos($moduleLower, 'informatique') !== false) {
                return "Professeur de langues enseigne de l'informatique";
            }
            return "Professeur de langues enseigne un module technique";
        }
        
        if (strpos($moduleLower, 'anglais') !== false) {
            return "Module d'anglais enseigné par un non-spécialiste";
        }
        
        if (strpos($moduleLower, 'français') !== false || strpos($moduleLower, 'francais') !== false) {
            return "Module de français enseigné par un non-spécialiste";
        }
        
        return "Affectation incohérente";
    }
}
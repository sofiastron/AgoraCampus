<?php

namespace App\Http\Controllers;

use App\Models\Filiere;
use App\Models\Groupe;
use App\Models\Module;
use App\Models\Enseignant;
use App\Models\Salle;
use App\Models\EmploiTemps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema; 
use Carbon\Carbon;

class PlanningController extends Controller
{
    /**
     * API: Liste des filières
     */
    public function getFilieres()
    {
        try {
            if (class_exists('App\Models\Filiere')) {
                $filieres = Filiere::select('id', 'nom', 'code')->get();
            } else {
                $filieres = [
                    (object)['id' => 1, 'nom' => 'Génie Informatique', 'code' => 'GI'],
                    (object)['id' => 2, 'nom' => 'Génie Industriel', 'code' => 'GIND'],
                    (object)['id' => 3, 'nom' => 'Génie Civil', 'code' => 'GC']
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => $filieres
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in getFilieres: ' . $e->getMessage());
            
            return response()->json([
                'success' => true,
                'data' => [
                    ['id' => 1, 'nom' => 'Génie Informatique', 'code' => 'GI'],
                    ['id' => 2, 'nom' => 'Génie Industriel', 'code' => 'GIND'],
                    ['id' => 3, 'nom' => 'Gestion', 'code' => 'GEST']
                ]
            ]);
        }
    }

    /**
     * API: Liste des niveaux par filière
     */
    public function getNiveaux($filiere_id)
    {
        try {
            if (class_exists('App\Models\Groupe')) {
                $niveaux = Groupe::where('filiere_id', $filiere_id)
                    ->select('niveau')
                    ->distinct()
                    ->orderByRaw("
                        CASE 
                            WHEN niveau LIKE '%1%' THEN 1
                            WHEN niveau LIKE '%2%' THEN 2
                            WHEN niveau LIKE '%3%' THEN 3
                            ELSE 4
                        END
                    ")
                    ->get()
                    ->pluck('niveau');
            } else {
                $niveaux = ['1ère année cycle', '2ème année cycle', '3ème année cycle'];
            }
            
            return response()->json([
                'success' => true,
                'data' => $niveaux
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in getNiveaux: ' . $e->getMessage());
            
            return response()->json([
                'success' => true,
                'data' => ['1ère année cycle', '2ème année cycle', '3ème année cycle']
            ]);
        }
    }
    
    /**
     * Génération avec modules consécutifs SANS créneau 12h-14h
     * AVEC GARANTIE: Même prof ET même salle pour module de 4h
     */
    public function generateFromDb(Request $request)
    {
        try {
            Log::info('=== DÉBUT GÉNÉRATION EMPLOI DU TEMPS AVEC MÊME PROF ET MÊME SALLE POUR MÊME MODULE ===');
            
            $request->validate([
                'filiere_id' => 'required|exists:filieres,id',
                'niveau' => 'nullable|string'
            ]);

            $filiere = Filiere::findOrFail($request->filiere_id);
            
            $groupesQuery = Groupe::where('filiere_id', $filiere->id);
            
            if ($request->filled('niveau')) {
                $groupesQuery->where('niveau', $request->niveau);
            }
            
            $groupes = $groupesQuery->get();

            if ($groupes->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun groupe pour cette filière' . ($request->niveau ? " et ce niveau ($request->niveau)" : '')
                ], 400);
            }

            $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
            $creneauxComplets = ['08h-10h', '10h-12h', '14h-16h', '16h-18h'];

            $enseignants = Enseignant::all();
            Log::info("Total enseignants disponibles: " . $enseignants->count());

            $salles = [];
            if (class_exists('App\Models\Salle')) {
                $salles = Salle::where('disponible', 1)->get();
            }

            $modulesCommuns = Module::where('is_common', 1)->get();
            $moduleAnglais = $modulesCommuns->first(fn($m) => stripos($m->titre, 'anglais') !== false);
            $moduleFrancais = $modulesCommuns->first(fn($m) => stripos($m->titre, 'français') !== false);
            
            $anglais = $moduleAnglais ? [
                'titre' => $moduleAnglais->titre,
                'semaine_debut' => $moduleAnglais->semaine_debut,
                'semaine_fin' => $moduleAnglais->semaine_fin
            ] : [
                'titre' => 'Anglais',
                'semaine_debut' => 1,
                'semaine_fin' => 8
            ];
            
            $francais = $moduleFrancais ? [
                'titre' => $moduleFrancais->titre,
                'semaine_debut' => $moduleFrancais->semaine_debut,
                'semaine_fin' => $moduleFrancais->semaine_fin
            ] : [
                'titre' => 'Français',
                'semaine_debut' => 1,
                'semaine_fin' => 8
            ];

            $scheduleByGroup = [];
            $affectationsByGroup = [];
            $semainesByGroup = [];
            $statistics = [
                'filiere' => $filiere->nom,
                'niveau_selectionne' => $request->niveau ?? 'Tous niveaux',
                'total_groupes' => $groupes->count(),
                'specialite_filiere' => $this->determinerSpecialiteFiliere($filiere),
                'regles' => 'Modules consécutifs de 4h | Même prof ET même salle pour même module | Pas de cours 12h-14h'
            ];

            $emploisExistants = $this->getEmploisExistantsPourConflits($filiere->id);
            Log::info("Emplois existants récupérés pour vérifier conflits: " . count($emploisExistants));

            foreach ($groupes as $groupe) {
                Log::info("Génération pour groupe: {$groupe->nom} (Filière: {$filiere->nom}, Niveau: {$groupe->niveau})");
                
                $modulesTechniques = Module::where('filiere_id', $filiere->id)
                    ->where('is_common', 0)
                    ->where('niveau', $groupe->niveau)
                    ->select('id', 'titre', 'semaine_debut', 'semaine_fin')
                    ->get();
                
                if ($modulesTechniques->isEmpty()) {
                    $modulesTechniques = Module::where('filiere_id', $filiere->id)
                        ->where('is_common', 0)
                        ->select('id', 'titre', 'semaine_debut', 'semaine_fin')
                        ->get();
                }
                
                $techModules = $modulesTechniques->take(7)->map(function($module) {
                    return [
                        'id' => $module->id,
                        'titre' => $module->titre,
                        'semaine_debut' => $module->semaine_debut ?? 1,
                        'semaine_fin' => $module->semaine_fin ?? 13
                    ];
                })->toArray();
                
                $resultatGeneration = $this->generateScheduleSansPauseDejeuner(
                    $techModules,
                    $anglais,
                    $francais,
                    $jours,
                    $creneauxComplets,
                    $groupe->nom,
                    $groupe->niveau
                );
                
                $schedule = $resultatGeneration['schedule'];
                $semainesInfo = $resultatGeneration['semaines_info'];
                
                $affectations = $this->affecterEnseignantsAvecMemeProfEtSalle(
                    $schedule,
                    $enseignants,
                    $salles,
                    $jours,
                    $creneauxComplets,
                    $groupe->nom,
                    $techModules,
                    $anglais,
                    $francais,
                    $filiere,
                    $emploisExistants
                );
                
                $scheduleByGroup[$groupe->nom] = $schedule;
                $affectationsByGroup[$groupe->nom] = $affectations;
                $semainesByGroup[$groupe->nom] = $semainesInfo;
                
                $stats = $this->calculerStatistiquesAvecConsecutifs($schedule, $affectations, $jours, $creneauxComplets);
                $stats['niveau'] = $groupe->niveau;
                $stats['modules_utilises'] = array_column($techModules, 'titre');
                $stats['semaines_info'] = $semainesInfo;
                
                $stats['enseignants_affectes'] = $this->getEnseignantsAffectes($affectations);
                $stats['modules_meme_prof_et_salle'] = $this->verifierModulesMemeProfEtSalle($affectations, $schedule, $jours, $creneauxComplets);
                
                $statistics['groupes'][$groupe->nom] = $stats;
            }

            Log::info('=== GÉNÉRATION TERMINÉE AVEC SUCCÈS (MÊME PROF ET MÊME SALLE POUR MÊME MODULE) ===');
            
            return response()->json([
                'success' => true,
                'schedule' => $scheduleByGroup,
                'affectations' => $affectationsByGroup,
                'semaines' => $semainesByGroup,
                'statistics' => $statistics,
                'jours' => $jours,
                'creneaux' => $creneauxComplets,
                'message' => 'Emploi du temps généré avec même prof et même salle pour même module'
            ]);

        } catch (\Exception $e) {
            Log::error('=== ERREUR CRITIQUE ===');
            Log::error('Message: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les emplois existants pour vérifier les conflits de profs
     */
    private function getEmploisExistantsPourConflits($filiere_id)
    {
        try {
            if (!class_exists('App\Models\EmploiTemps') || !Schema::hasTable('emplotemps')) {
                return [];
            }
            
            $emplois = EmploiTemps::where('filiere_id', $filiere_id)
                ->whereNotNull('affectations')
                ->get();
            
            $conflits = [];
            
            foreach ($emplois as $emploi) {
                $affectations = is_string($emploi->affectations) 
                    ? json_decode($emploi->affectations, true) 
                    : $emploi->affectations;
                
                if (is_array($affectations)) {
                    $conflits[$emploi->semestre] = [
                        'semestre' => $emploi->semestre,
                        'niveau' => $emploi->niveau,
                        'affectations' => $affectations
                    ];
                }
            }
            
            Log::info("Récupéré " . count($conflits) . " emplois existants pour vérification conflits");
            return $conflits;
            
        } catch (\Exception $e) {
            Log::error("Erreur récupération emplois existants: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Affectation GARANTIE: Même prof ET même salle pour toutes les sessions d'un même module
     */
    private function affecterEnseignantsAvecMemeProfEtSalle($schedule, $enseignants, $salles, $jours, $creneaux, $groupeName, $techModules, $anglais, $francais, $filiere, $emploisExistants)
    {
        $affectations = [];
        $enseignantsDisponibles = $enseignants->toArray();
        $sallesDisponibles = !empty($salles) ? $salles->toArray() : [];
        
        $specialiteCible = $this->determinerSpecialiteFiliere($filiere);
        
        Log::info("Filtre spécialité pour filière {$filiere->nom}: $specialiteCible");
        
        $heuresEnseignants = [];
        foreach ($enseignants as $enseignant) {
            $heuresEnseignants[$enseignant->id] = 0;
        }
        
        $modulesAnalyse = $this->analyserModulesDansSchedule($schedule, $jours, $creneaux);
        Log::info("Analyse modules pour $groupeName: " . json_encode(array_keys($modulesAnalyse)));
        
        $affectationsParModule = $this->affecterProfEtSalleUniquesParModule(
            $modulesAnalyse,
            $enseignantsDisponibles,
            $sallesDisponibles,
            $specialiteCible,
            $heuresEnseignants,
            $groupeName,
            $emploisExistants
        );
        
        foreach ($jours as $jour) {
            $affectations[$jour] = [];
            
            foreach ($creneaux as $creneau) {
                $module = $schedule[$jour][$creneau];
                
                if ($module) {
                    $affectation = [
                        'module' => $module,
                        'enseignant' => null,
                        'salle' => null,
                        'module_id' => null
                    ];
                    
                    if (isset($affectationsParModule[$module])) {
                        if (isset($affectationsParModule[$module]['enseignant'])) {
                            $affectation['enseignant'] = $affectationsParModule[$module]['enseignant'];
                        } else {
                            Log::warning("Clé 'enseignant' manquante pour module: $module");
                            $affectation['enseignant'] = $this->trouverEnseignantFallback(
                                $module,
                                $enseignantsDisponibles,
                                $specialiteCible,
                                $heuresEnseignants
                            );
                        }
                        
                        if (isset($affectationsParModule[$module]['salle'])) {
                            $affectation['salle'] = $affectationsParModule[$module]['salle'];
                        } else {
                            Log::warning("Clé 'salle' manquante pour module: $module");
                            $affectation['salle'] = $this->trouverSallePourModule(
                                $module,
                                $sallesDisponibles
                            );
                        }
                        
                        if (isset($affectationsParModule[$module]['module_id'])) {
                            $affectation['module_id'] = $affectationsParModule[$module]['module_id'];
                        } else {
                            $affectation['module_id'] = $this->genererModuleId($module);
                        }
                        
                        if (isset($affectationsParModule[$module]['commentaire'])) {
                            $affectation['commentaire'] = $affectationsParModule[$module]['commentaire'];
                        }
                    } else {
                        Log::warning("Module '$module' non trouvé dans affectationsParModule, fallback complet");
                        
                        $affectation['enseignant'] = $this->trouverEnseignantFallback(
                            $module,
                            $enseignantsDisponibles,
                            $specialiteCible,
                            $heuresEnseignants
                        );
                        
                        $affectation['salle'] = $this->trouverSallePourModule(
                            $module,
                            $sallesDisponibles
                        );
                        
                        $affectation['module_id'] = $this->genererModuleId($module);
                        $affectation['commentaire'] = "Affectation fallback";
                    }
                    
                    $affectations[$jour][$creneau] = $affectation;
                }
            }
        }
        
        return $affectations;
    }

    /**
     * Analyse tous les modules dans le schedule
     */
    private function analyserModulesDansSchedule($schedule, $jours, $creneaux)
    {
        $modules = [];
        
        foreach ($jours as $jour) {
            foreach ($creneaux as $creneau) {
                $module = $schedule[$jour][$creneau] ?? null;
                
                if ($module) {
                    if (!isset($modules[$module])) {
                        $modules[$module] = [
                            'nom' => $module,
                            'total_heures' => 0,
                            'sessions' => [],
                            'est_langue' => $this->estModuleLangue($module)
                        ];
                    }
                    
                    $modules[$module]['total_heures'] += 2;
                    $modules[$module]['sessions'][] = [
                        'jour' => $jour,
                        'creneau' => $creneau
                    ];
                }
            }
        }
        
        Log::info("Modules analysés: " . count($modules) . " modules trouvés");
        
        return $modules;
    }

    /**
     * Génère un ID unique et stable pour chaque module
     */
    private function genererModuleId($nomModule)
    {
        $hash = substr(md5($nomModule), 0, 8);
        
        $modulesIds = [
            'DevWeb' => 101,
            'Programmation' => 102,
            'Base de données' => 103,
            'Réseaux' => 104,
            'Mathématiques' => 105,
            'Anglais' => 201,
            'Français' => 202,
            'Langue' => 203,
            'Informatique' => 301,
            'Génie Industriel' => 302,
            'Physique' => 303
        ];
        
        foreach ($modulesIds as $pattern => $id) {
            if (stripos($nomModule, $pattern) !== false) {
                return $id;
            }
        }
        
        return hexdec($hash) % 1000 + 300;
    }

    /**
     * Affecte un professeur unique ET une salle unique pour chaque module
     */
    private function affecterProfEtSalleUniquesParModule($modulesAnalyse, $enseignantsDisponibles, $sallesDisponibles, $specialiteCible, &$heuresEnseignants, $groupeName, $emploisExistants)
    {
        $affectations = [];
        $sallesAffectees = [];
        
        if (!is_array($modulesAnalyse) || empty($modulesAnalyse)) {
            Log::error("modulesAnalyse n'est pas un tableau ou est vide");
            return $affectations;
        }
        
        foreach ($modulesAnalyse as $moduleNom => $info) {
            if (!is_array($info)) {
                Log::warning("Structure invalide pour module: $moduleNom");
                continue;
            }
            
            $heuresModule = $info['total_heures'] ?? 4;
            $estLangue = $info['est_langue'] ?? $this->estModuleLangue($moduleNom);
            
            $moduleId = $this->genererModuleId($moduleNom);
            
            Log::info("Affectation module '$moduleNom' (ID: $moduleId, $heuresModule heures)");
            
            $enseignantsFiltres = $this->filtrerEnseignantsPourModule(
                $moduleNom,
                $enseignantsDisponibles,
                $specialiteCible,
                $estLangue
            );
            
            if (empty($enseignantsFiltres)) {
                Log::warning("Aucun enseignant filtré pour '$moduleNom', utilisation de tous");
                $enseignantsFiltres = $enseignantsDisponibles;
            }
            
            usort($enseignantsFiltres, function($a, $b) use ($heuresEnseignants) {
                $heuresA = $heuresEnseignants[$a['id']] ?? 0;
                $heuresB = $heuresEnseignants[$b['id']] ?? 0;
                return $heuresA - $heuresB;
            });
            
            $enseignantTrouve = null;
            foreach ($enseignantsFiltres as $enseignant) {
                $enseignantId = $enseignant['id'] ?? null;
                if (!$enseignantId) continue;
                
                $heuresMax = $enseignant['heures_max_semaine'] ?? 20;
                $heuresActuelles = $heuresEnseignants[$enseignantId] ?? 0;
                
                if ($heuresActuelles + $heuresModule <= $heuresMax) {
                    $enseignantTrouve = $enseignant;
                    break;
                }
            }
            
            $salleAttribuee = $this->trouverSalleUniquePourModule($moduleNom, $moduleId, $sallesDisponibles, $sallesAffectees);
            
            if ($enseignantTrouve) {
                $affectations[$moduleNom] = [
                    'enseignant' => [
                        'id' => $enseignantTrouve['id'] ?? 0,
                        'nom' => $enseignantTrouve['nom'] ?? 'Inconnu',
                        'specialite' => $enseignantTrouve['specialite'] ?? 'Général',
                        'est_specialiste_langue' => $estLangue ? 'Oui' : 'Non',
                        'heures_module' => $heuresModule
                    ],
                    'salle' => $salleAttribuee,
                    'module_id' => $moduleId,
                    'commentaire' => "Même prof et même salle pour toutes les sessions"
                ];
                
                if (isset($enseignantTrouve['id'])) {
                    $heuresEnseignants[$enseignantTrouve['id']] += $heuresModule;
                }
                
                $sallesAffectees[$moduleId] = $salleAttribuee['id'];
                
                Log::info("✅ MODULE '$moduleNom' (ID: $moduleId):");
                Log::info("   Prof: {$enseignantTrouve['nom']} ($heuresModule heures)");
                Log::info("   Salle: {$salleAttribuee['nom']}");
                
            } else {
                $enseignantFallback = $enseignantsFiltres[0] ?? ['id' => 0, 'nom' => 'Aucun', 'specialite' => 'Général'];
                
                $affectations[$moduleNom] = [
                    'enseignant' => [
                        'id' => $enseignantFallback['id'] ?? 0,
                        'nom' => $enseignantFallback['nom'] ?? 'Aucun disponible',
                        'specialite' => $enseignantFallback['specialite'] ?? 'Général',
                        'est_specialiste_langue' => $estLangue ? 'Fallback' : 'Non',
                        'warning' => 'Heures limites'
                    ],
                    'salle' => $salleAttribuee,
                    'module_id' => $moduleId,
                    'commentaire' => "Affectation avec contraintes"
                ];
                
                Log::warning("⚠️ Fallback pour module: $moduleNom");
            }
        }
        
        return $affectations;
    }

    /**
     * Trouve une salle unique pour toutes les sessions d'un même module
     */
    private function trouverSalleUniquePourModule($moduleNom, $moduleId, $sallesDisponibles, &$sallesAffectees)
    {
        if (isset($sallesAffectees[$moduleId])) {
            $salleId = $sallesAffectees[$moduleId];
            foreach ($sallesDisponibles as $salle) {
                if (isset($salle['id']) && $salle['id'] == $salleId) {
                    return [
                        'id' => $salle['id'],
                        'nom' => $salle['nom'] ?? 'Salle Inconnue',
                        'batiment' => $salle['batiment'] ?? 'Bâtiment Principal',
                        'capacite' => $salle['capacite'] ?? 30,
                        'reutilisee' => true,
                        'pour_module' => $moduleNom
                    ];
                }
            }
        }
        
        if (empty($sallesDisponibles)) {
            $numeroSalle = ($moduleId % 20) + 1;
            return [
                'id' => $moduleId + 1000,
                'nom' => 'Salle ' . $numeroSalle,
                'batiment' => $this->determinerBatimentModule($moduleNom),
                'capacite' => 30,
                'affectee_au_module' => $moduleNom
            ];
        }
        
        $salle = $sallesDisponibles[array_rand($sallesDisponibles)];
        
        return [
            'id' => $salle['id'] ?? $moduleId + 1000,
            'nom' => $salle['nom'] ?? 'Salle ' . (($moduleId % 20) + 1),
            'batiment' => $salle['batiment'] ?? $this->determinerBatimentModule($moduleNom),
            'capacite' => $salle['capacite'] ?? 30,
            'affectee_au_module' => $moduleNom
        ];
    }

    /**
     * Détermine le bâtiment approprié selon le type de module
     */
    private function determinerBatimentModule($moduleNom)
    {
        $moduleLower = strtolower($moduleNom);
        
        if (strpos($moduleLower, 'informatique') !== false || 
            strpos($moduleLower, 'programmation') !== false ||
            strpos($moduleLower, 'réseau') !== false ||
            strpos($moduleLower, 'base de données') !== false ||
            strpos($moduleLower, 'web') !== false) {
            return 'Bâtiment Informatique';
        }
        
        if (strpos($moduleLower, 'math') !== false || 
            strpos($moduleLower, 'statistique') !== false ||
            strpos($moduleLower, 'algèbre') !== false) {
            return 'Bâtiment Sciences';
        }
        
        if (strpos($moduleLower, 'anglais') !== false || 
            strpos($moduleLower, 'français') !== false ||
            strpos($moduleLower, 'langue') !== false) {
            return 'Bâtiment Langues';
        }
        
        if (strpos($moduleLower, 'physique') !== false || 
            strpos($moduleLower, 'mécanique') !== false ||
            strpos($moduleLower, 'thermodynamique') !== false) {
            return 'Bâtiment Sciences Appliquées';
        }
        
        if (strpos($moduleLower, 'génie industriel') !== false) {
            return 'Bâtiment Génie Industriel';
        }
        
        return 'Bâtiment Principal';
    }

    /**
     * Filtre les enseignants par spécialité du module
     */
    private function filtrerEnseignantsPourModule($moduleNom, $enseignants, $specialiteCible, $estLangue)
    {
        if ($estLangue) {
            return array_filter($enseignants, function($e) use ($moduleNom) {
                $specialite = strtolower($e['specialite'] ?? '');
                $moduleLower = strtolower($moduleNom);
                
                if (strpos($moduleLower, 'anglais') !== false) {
                    return strpos($specialite, 'anglais') !== false || 
                           strpos($specialite, 'langue') !== false;
                }
                
                if (strpos($moduleLower, 'français') !== false) {
                    return strpos($specialite, 'français') !== false || 
                           strpos($specialite, 'francais') !== false ||
                           strpos($specialite, 'langue') !== false;
                }
                
                return strpos($specialite, 'langue') !== false;
            });
        }
        
        $specialiteModule = $this->determinerSpecialiteModule($moduleNom);
        
        return array_filter($enseignants, function($e) use ($specialiteModule, $specialiteCible) {
            if (stripos($e['specialite'] ?? '', $specialiteModule) !== false) {
                return true;
            }
            
            if (stripos($e['specialite'] ?? '', $specialiteCible) !== false) {
                return true;
            }
            
            if (($e['specialite'] ?? '') === 'Général') {
                return true;
            }
            
            return false;
        });
    }

    /**
     * Trouve un enseignant fallback
     */
    private function trouverEnseignantFallback($module, $enseignants, $specialiteCible, &$heuresEnseignants)
    {
        $estLangue = $this->estModuleLangue($module);
        $enseignantsFiltres = $this->filtrerEnseignantsPourModule($module, $enseignants, $specialiteCible, $estLangue);
        
        if (empty($enseignantsFiltres)) {
            $enseignantsFiltres = $enseignants;
        }
        
        usort($enseignantsFiltres, function($a, $b) use ($heuresEnseignants) {
            return ($heuresEnseignants[$a['id']] ?? 0) - ($heuresEnseignants[$b['id']] ?? 0);
        });
        
        $enseignant = $enseignantsFiltres[0] ?? $enseignants[0];
        $heuresEnseignants[$enseignant['id']] += 2;
        
        return [
            'id' => $enseignant['id'],
            'nom' => $enseignant['nom'],
            'specialite' => $enseignant['specialite'] ?? 'Général',
            'est_specialiste_langue' => $estLangue ? 'Fallback' : 'Non',
            'warning' => 'Affectation fallback'
        ];
    }

    /**
     * Trouve une salle pour un module (fallback)
     */
    private function trouverSallePourModule($module, $sallesDisponibles)
    {
        if (empty($sallesDisponibles)) {
            return [
                'id' => rand(100, 200),
                'nom' => 'Salle ' . rand(1, 30),
                'batiment' => $this->determinerBatimentModule($module),
                'capacite' => 30
            ];
        }
        
        $salle = $sallesDisponibles[array_rand($sallesDisponibles)];
        
        return [
            'id' => $salle['id'] ?? rand(100, 200),
            'nom' => $salle['nom'] ?? 'Salle Inconnue',
            'batiment' => $salle['batiment'] ?? $this->determinerBatimentModule($module),
            'capacite' => $salle['capacite'] ?? 30
        ];
    }

    /**
     * Vérifie quels modules ont le même prof ET la même salle
     */
    private function verifierModulesMemeProfEtSalle($affectations, $schedule, $jours, $creneaux)
    {
        $modulesParProf = [];
        $modulesParSalle = [];
        $resultats = [];
        
        foreach ($jours as $jour) {
            foreach ($creneaux as $creneau) {
                $module = $schedule[$jour][$creneau] ?? null;
                $affectation = $affectations[$jour][$creneau] ?? null;
                
                if ($module && $affectation && $affectation['enseignant'] && $affectation['salle']) {
                    $profId = $affectation['enseignant']['id'];
                    $profNom = $affectation['enseignant']['nom'];
                    $salleId = $affectation['salle']['id'];
                    $salleNom = $affectation['salle']['nom'];
                    $moduleId = $affectation['module_id'] ?? 'N/A';
                    
                    if (!isset($modulesParProf[$profId])) {
                        $modulesParProf[$profId] = [
                            'professeur' => $profNom,
                            'modules' => [],
                            'total_heures' => 0
                        ];
                    }
                    
                    if (!in_array(['nom' => $module, 'id' => $moduleId], $modulesParProf[$profId]['modules'])) {
                        $modulesParProf[$profId]['modules'][] = ['nom' => $module, 'id' => $moduleId];
                    }
                    $modulesParProf[$profId]['total_heures'] += 2;
                    
                    if (!isset($modulesParSalle[$salleId])) {
                        $modulesParSalle[$salleId] = [
                            'salle' => $salleNom,
                            'modules' => [],
                            'sessions' => 0
                        ];
                    }
                    
                    if (!in_array(['nom' => $module, 'id' => $moduleId], $modulesParSalle[$salleId]['modules'])) {
                        $modulesParSalle[$salleId]['modules'][] = ['nom' => $module, 'id' => $moduleId];
                    }
                    $modulesParSalle[$salleId]['sessions']++;
                }
            }
        }
        
        foreach ($modulesParProf as $profId => $info) {
            if (count($info['modules']) === 1) {
                $resultats['professeurs'][] = [
                    'professeur' => $info['professeur'],
                    'module' => $info['modules'][0]['nom'],
                    'module_id' => $info['modules'][0]['id'],
                    'statut' => '✅ Même prof pour toutes les sessions',
                    'heures' => $info['total_heures']
                ];
            } else {
                $resultats['professeurs'][] = [
                    'professeur' => $info['professeur'],
                    'modules' => array_column($info['modules'], 'nom'),
                    'statut' => '⚠️ Plusieurs modules',
                    'heures' => $info['total_heures']
                ];
            }
        }
        
        foreach ($modulesParSalle as $salleId => $info) {
            if (count($info['modules']) === 1) {
                $resultats['salles'][] = [
                    'salle' => $info['salle'],
                    'module' => $info['modules'][0]['nom'],
                    'module_id' => $info['modules'][0]['id'],
                    'statut' => '✅ Même salle pour toutes les sessions',
                    'sessions' => $info['sessions']
                ];
            } else {
                $resultats['salles'][] = [
                    'salle' => $info['salle'],
                    'modules' => array_column($info['modules'], 'nom'),
                    'statut' => '⚠️ Plusieurs modules dans cette salle',
                    'sessions' => $info['sessions']
                ];
            }
        }
        
        return $resultats;
    }
/**
 * Valider un emploi du temps
 */
/**
 * Route spécifique pour valider un emploi
 */
public function validateEmploi(Request $request, $id)
{
    try {
        \Log::info("=== VALIDATION SIMPLE EMPLOI ID: $id ===");
        
        // Validation simple
        $request->validate([
            'statut' => 'required|in:valide,en_attente,rejeté'
        ]);
        
        // Trouver et mettre à jour l'emploi
        $emploi = EmploiTemps::findOrFail($id);
        
        $emploi->update([
            'statut' => $request->statut,
            'updated_at' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour avec succès',
            'emploi' => $emploi
        ]);
        
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Emploi non trouvé'
        ], 404);
    } catch (\Exception $e) {
        \Log::error('Erreur validation simple: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Erreur: ' . $e->getMessage()
        ], 500);
    }
}
    /**
     * Sauvegarde un emploi du temps
     */
    public function saveEmploi(Request $request)
    {
        try {
            \Log::info('=== DÉBUT SAUVEGARDE EMPLOI ===');
            
            $validated = $request->validate([
                'filiere_id' => 'required|exists:filieres,id',
                'semestre' => 'required|string',
                'niveau' => 'nullable|string',
                'schedule' => 'required|array',
                'affectations' => 'nullable|array',
                'semaines' => 'nullable|array',
                'statistics' => 'nullable|array',
                'details' => 'nullable|array',
                'emploi_id' => 'nullable|exists:emplotemps,id'
            ]);
            
            $modelClass = 'App\\Models\\EmploiTemps';
            
            if (!class_exists($modelClass)) {
                \Log::error('Classe non trouvée: ' . $modelClass);
                return response()->json([
                    'success' => false,
                    'message' => 'Modèle EmploiTemps non trouvé.'
                ], 500);
            }
            
            if ($request->has('emploi_id') && $request->emploi_id) {
                \Log::info('Mise à jour emploi ID: ' . $request->emploi_id);
                
                $emploi = $modelClass::find($request->emploi_id);
                
                if (!$emploi) {
                    \Log::error('Emploi non trouvé pour ID: ' . $request->emploi_id);
                    return response()->json([
                        'success' => false,
                        'message' => 'Emploi non trouvé'
                    ], 404);
                }
                
                $emploi->update([
                    'schedule' => $request->schedule,
                    'affectations' => $request->affectations,
                    'semaines' => $request->semaines,
                    'statistics' => $request->statistics,
                    'details' => $request->details,
                    'titre' => $request->input('details.titre', $emploi->titre),
                    'description' => $request->input('details.description', $emploi->description),
                    'updated_at' => now()
                ]);
                
                \Log::info('Emploi mis à jour avec succès');
                
                return response()->json([
                    'success' => true,
                    'message' => 'Emploi mis à jour avec succès',
                    'emploi_id' => $emploi->id,
                    'existe_deja' => true
                ]);
                
            } else {
                \Log::info('Création nouvel emploi');
                
                $emploi = $modelClass::create([
                    'filiere_id' => $request->filiere_id,
                    'semestre' => $request->semestre,
                    'niveau' => $request->niveau,
                    'schedule' => $request->schedule,
                    'affectations' => $request->affectations,
                    'semaines' => $request->semaines,
                    'statistics' => $request->statistics,
                    'details' => $request->details,
                    'titre' => $request->input('details.titre', 'Sans titre'),
                    'description' => $request->input('details.description', ''),
                    'statut' => 'en_attente',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                \Log::info('Emploi créé avec ID: ' . $emploi->id);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Emploi sauvegardé avec succès',
                    'emploi_id' => $emploi->id,
                    'existe_deja' => false
                ]);
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Erreur validation: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error('Erreur sauvegarde emploi: ' . $e->getMessage());
            \Log::error('Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprime un emploi du temps
     */
    public function deleteEmploi($id)
    {
        try {
            Log::info("=== SUPPRESSION EMPLOI ID: $id ===");
            
            if (!Schema::hasTable('emplotemps')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Table emploi_temps non disponible'
                ], 404);
            }
            
            $emploi = DB::table('emplotemps')->find($id);
            
            if (!$emploi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Emploi du temps non trouvé'
                ], 404);
            }
            
            $deleted = DB::table('emplotemps')->where('id', $id)->delete();
            
            Log::info("Emploi $id supprimé avec succès");
            
            return response()->json([
                'success' => true,
                'message' => 'Emploi du temps supprimé avec succès',
                'deleted' => $deleted
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in deleteEmploi: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifie si un emploi existe déjà
     */
    public function checkDuplicate(Request $request)
    {
        try {
            $request->validate([
                'filiere_id' => 'required|exists:filieres,id',
                'semestre' => 'required|string',
                'niveau' => 'nullable|string'
            ]);
            
            $exists = false;
            $emploiId = null;
            $createdAt = null;
            
            if (class_exists('App\Models\EmploiTemps') && Schema::hasTable('emplotemps')) {
                $query = EmploiTemps::where('filiere_id', $request->filiere_id)
                    ->where('semestre', $request->semestre);
                
                if ($request->filled('niveau')) {
                    $query->where('niveau', $request->niveau);
                } else {
                    $query->whereNull('niveau');
                }
                
                $emploi = $query->first();
                
                if ($emploi) {
                    $exists = true;
                    $emploiId = $emploi->id;
                    $createdAt = $emploi->created_at;
                }
            }
            
            return response()->json([
                'success' => true,
                'exists' => $exists,
                'emploi_id' => $emploiId,
                'created_at' => $createdAt ? $createdAt->format('d/m/Y H:i') : null
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in checkDuplicate: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'exists' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
/**
 * Récupère un emploi du temps spécifique par ID
 */
public function getEmploiById($id)
{
    try {
        \Log::info("=== RÉCUPÉRATION EMPLOI ID: $id ===");
        
        // Vérifier si la table existe
        if (!class_exists('App\\Models\\EmploiTemps') || !Schema::hasTable('emplotemps')) {
            \Log::error('Table emplotemps non disponible');
            
            return response()->json([
                'success' => false,
                'message' => 'Table emploi du temps non disponible',
                'data' => null
            ]);
        }
        
        $emploi = EmploiTemps::find($id);
        
        if (!$emploi) {
            \Log::warning("Emploi ID $id non trouvé");
            
            return response()->json([
                'success' => false,
                'message' => 'Emploi du temps non trouvé',
                'data' => null
            ], 404);
        }
        
        // Charger les données associées
        $emploi->load('filiere');
        
        // Décoder les champs JSON si nécessaire
        if ($emploi->horaires && is_string($emploi->horaires)) {
            $emploi->horaires = json_decode($emploi->horaires, true);
        }
        
        if ($emploi->affectations && is_string($emploi->affectations)) {
            $emploi->affectations = json_decode($emploi->affectations, true);
        }
        
        if ($emploi->semaines && is_string($emploi->semaines)) {
            $emploi->semaines = json_decode($emploi->semaines, true);
        }
        
        if ($emploi->statistics && is_string($emploi->statistics)) {
            $emploi->statistics = json_decode($emploi->statistics, true);
        }
        
        if ($emploi->details && is_string($emploi->details)) {
            $emploi->details = json_decode($emploi->details, true);
        }
        
        // Alias pour la compatibilité avec le frontend
        $emploi->schedule = $emploi->horaires ?? [];
        
        \Log::info("Emploi ID $id récupéré avec succès");
        
        return response()->json([
            'success' => true,
            'data' => $emploi
        ]);
        
    } catch (\Exception $e) {
        \Log::error("Erreur récupération emploi ID $id: " . $e->getMessage());
        \Log::error('Trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération de l\'emploi',
            'error' => $e->getMessage()
        ], 500);
    }
}
    /**
     * Récupère la liste des emplois du temps sauvegardés
     */
    public function getSavedEmplois(Request $request)
    {
        try {
            $filiereId = $request->input('filiere_id');
            $semestre = $request->input('semestre');
            $statut = $request->input('statut');
            
            $query = EmploiTemps::with('filiere');
            
            if ($filiereId) {
                $query->where('filiere_id', $filiereId);
            }
            
            if ($semestre) {
                $query->where('semestre', $semestre);
            }
            
            if ($statut) {
                $query->where('statut', $statut);
            }
            
            $emplois = $query->orderBy('created_at', 'desc')->get();
            
            $emplois->transform(function ($emploi) {
                $horaires = $emploi->horaires;
                if (is_string($horaires)) {
                    $horaires = json_decode($horaires, true);
                }
                $emploi->horaires = $horaires ?? [];
                
                $affectations = $emploi->affectations;
                if (is_string($affectations)) {
                    $affectations = json_decode($affectations, true);
                }
                $emploi->affectations = $affectations ?? [];
                
                $semaines = $emploi->semaines;
                if (is_string($semaines)) {
                    $semaines = json_decode($semaines, true);
                }
                $emploi->semaines = $semaines ?? [];
                
                $emploi->schedule = $emploi->horaires;
                
                return $emploi;
            });
            
            return response()->json([
                'success' => true,
                'data' => $emplois
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erreur récupération emplois: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des emplois',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère un emploi spécifique
     */
    public function getSavedEmploi($id)
    {
        try {
            $emploi = EmploiTemps::with('filiere')->findOrFail($id);
            
            $emploi->horaires = is_string($emploi->horaires) 
                ? json_decode($emploi->horaires, true) 
                : $emploi->horaires;
            
            $emploi->affectations = is_string($emploi->affectations) 
                ? json_decode($emploi->affectations, true) 
                : $emploi->affectations;
            
            $emploi->semaines = is_string($emploi->semaines) 
                ? json_decode($emploi->semaines, true) 
                : $emploi->semaines;
            
            $emploi->schedule = $emploi->horaires;
            
            return response()->json([
                'success' => true,
                'data' => $emploi
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Emploi non trouvé'
            ], 404);
            
        } catch (\Exception $e) {
            \Log::error('Erreur récupération emploi: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'emploi'
            ], 500);
        }
    }

    /**
     * Génère un emploi du temps SANS créneau 12h-14h
     */
    private function generateScheduleSansPauseDejeuner($techModules, $anglais, $francais, $jours, $creneaux, $groupeName, $niveau)
    {
        $schedule = [];
        $semainesInfo = [];
        
        foreach ($jours as $jour) {
            foreach ($creneaux as $creneau) {
                $schedule[$jour][$creneau] = null;
            }
        }
        
        $distribution = $this->getDistributionSansPauseDejeuner($niveau);
        
        $nombreModulesConsecutifs = rand(4, 5);
        Log::info("$groupeName aura $nombreModulesConsecutifs modules en 4h consécutives");
        
        $modulesPourConsecutifs = array_slice($techModules, 0, $nombreModulesConsecutifs);
        $modulesPourSepares = array_slice($techModules, $nombreModulesConsecutifs);
        
        $this->placerModulesConsecutifsSansPause(
            $schedule, 
            $modulesPourConsecutifs, 
            $jours, 
            $creneaux, 
            $distribution,
            $semainesInfo
        );
        
        $this->placerModulesSeparesSansPause(
            $schedule,
            $modulesPourSepares,
            $jours,
            $creneaux,
            $distribution,
            $semainesInfo
        );
        
        $this->placerLanguesSansPause($schedule, $anglais, $francais, $jours, $creneaux, $distribution, $semainesInfo);
        
        $this->remplirCreneauxVidesSansPause($schedule, $techModules, $jours, $creneaux, $distribution, $semainesInfo);
        
        $this->ajusterHeuresModulesSansPause($schedule, $techModules, $anglais, $francais, $jours, $creneaux, $semainesInfo);
        
        return [
            'schedule' => $schedule,
            'semaines_info' => $semainesInfo
        ];
    }

    /**
     * Distribution horaire SANS créneau 12h-14h
     */
  /**
 * Distribution horaire SANS créneau 12h-14h
 * Optimisée pour permettre des 4h consécutives l'après-midi
 */
private function getDistributionSansPauseDejeuner($niveau)
{
    $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
    
    $niveauNum = 1;
    if (strpos($niveau, '1') !== false) $niveauNum = 1;
    elseif (strpos($niveau, '2') !== false) $niveauNum = 2;
    elseif (strpos($niveau, '3') !== false) $niveauNum = 3;
    
    // Distributions avec possibilité de 4h l'après-midi
    $distributions = [
        1 => [
            [6, 8, 6, 8, 4],  // 1ère année - standard
            [8, 6, 8, 6, 4],  // 1ère année - alternative
            [8, 8, 6, 4, 6],  // 1ère année - plus de matin
        ],
        2 => [
            [8, 6, 8, 6, 4],  // 2ème année - standard
            [6, 8, 8, 4, 6],  // 2ème année - plus d'après-midi
            [8, 8, 4, 6, 6],  // 2ème année - variante
        ],
        3 => [
            [8, 8, 6, 6, 4],  // 3ème année - standard
            [8, 6, 8, 4, 6],  // 3ème année - plus équilibré
            [6, 8, 8, 6, 4],  // 3ème année - plus d'après-midi
        ],
    ];
    
    $selectedSet = $distributions[$niveauNum] ?? [[8, 6, 8, 6, 4]];
    $selected = $selectedSet[array_rand($selectedSet)];
    shuffle($selected);
    
    $result = [];
    foreach ($jours as $index => $jour) {
        $result[$jour] = $selected[$index];
    }
    
    // Assurer qu'au moins 2 jours ont 8h pour permettre 4h consécutives
    $joursAvec8h = array_filter($result, function($heures) {
        return $heures >= 8;
    });
    
    if (count($joursAvec8h) < 2) {
        // Ajuster pour avoir au moins 2 jours avec 8h
        $joursFaibles = array_filter($result, function($heures) {
            return $heures < 8;
        });
        
        if (count($joursFaibles) > 0) {
            $jourAAjuster = array_keys($joursFaibles)[0];
            $result[$jourAAjuster] = 8;
            
            // Réduire un autre jour pour compenser
            $joursFort = array_filter($result, function($heures) {
                return $heures > 8;
            });
            
            if (count($joursFort) > 0) {
                $jourARevoir = array_keys($joursFort)[0];
                $result[$jourARevoir] = max(6, $result[$jourARevoir] - 2);
            }
        }
    }
    
    Log::info("Distribution horaire: " . json_encode($result));
    
    return $result;
}

    /**
     * Place les modules en 4h consécutives SANS 12h-14h
     */
    private function placerModulesConsecutifsSansPause(&$schedule, $modules, $jours, $creneaux, $distribution, &$semainesInfo)
    {
        $places = 0;
        $pairesCreneaux = [
            ['08h-10h', '10h-12h'],
             ['14h-16h', '16h-18h'],
        ];
        
        shuffle($jours);
        
        foreach ($modules as $module) {
            $place = false;
            
            foreach ($jours as $jour) {
                $heuresUtilisees = $this->calculerHeuresJour($schedule, $jour, $creneaux);
                if ($heuresUtilisees + 4 > $distribution[$jour]) {
                    continue;
                }
                
                foreach ($pairesCreneaux as $paire) {
                    $creneau1 = $paire[0];
                    $creneau2 = $paire[1];
                    
                    if ($schedule[$jour][$creneau1] === null && $schedule[$jour][$creneau2] === null) {
                        $schedule[$jour][$creneau1] = $module['titre'];
                        $schedule[$jour][$creneau2] = $module['titre'];
                        
                        $semainesInfo[$jour][$creneau1] = [
                            'module' => $module['titre'],
                            'semaine_debut' => $module['semaine_debut'],
                            'semaine_fin' => $module['semaine_fin']
                        ];
                        
                        $semainesInfo[$jour][$creneau2] = [
                            'module' => $module['titre'],
                            'semaine_debut' => $module['semaine_debut'],
                            'semaine_fin' => $module['semaine_fin']
                        ];
                        
                        $places++;
                        $place = true;
                        Log::info("Module '{$module['titre']}' placé en 4h consécutives le $jour: $creneau1-$creneau2");
                        break 2;
                    }
                }
            }
            
            if (!$place) {
                Log::warning("Impossible de placer '{$module['titre']}' en consécutif, placement en séparé");
                $this->placerModuleSepareSansPause($schedule, $module, $jours, $creneaux, $distribution, $semainesInfo);
            }
        }
        
        return $places;
    }

    /**
     * Place un module en 2 créneaux séparés SANS 12h-14h
     */
    private function placerModuleSepareSansPause(&$schedule, $module, $jours, $creneaux, $distribution, &$semainesInfo)
    {
        $creneauxPlaces = 0;
        $maxEssais = 30;
        $essais = 0;
        
        shuffle($jours);
        
        while ($creneauxPlaces < 2 && $essais < $maxEssais) {
            $jour = $jours[array_rand($jours)];
            $creneau = $creneaux[array_rand($creneaux)];
            
            $heuresUtilisees = $this->calculerHeuresJour($schedule, $jour, $creneaux);
            if ($heuresUtilisees + 2 > $distribution[$jour]) {
                $essais++;
                continue;
            }
            
            if ($schedule[$jour][$creneau] === null) {
                $indexCreneau = array_search($creneau, $creneaux);
                $estConsecutif = false;
                
                if ($indexCreneau > 0) {
                    $creneauPrecedent = $creneaux[$indexCreneau - 1];
                    if ($schedule[$jour][$creneauPrecedent] === $module['titre']) {
                        $estConsecutif = true;
                    }
                }
                
                if ($indexCreneau < count($creneaux) - 1) {
                    $creneauSuivant = $creneaux[$indexCreneau + 1];
                    if ($schedule[$jour][$creneauSuivant] === $module['titre']) {
                        $estConsecutif = true;
                    }
                }
                
                if (!$estConsecutif) {
                    $schedule[$jour][$creneau] = $module['titre'];
                    
                    $semainesInfo[$jour][$creneau] = [
                        'module' => $module['titre'],
                        'semaine_debut' => $module['semaine_debut'],
                        'semaine_fin' => $module['semaine_fin']
                    ];
                    
                    $creneauxPlaces++;
                    Log::info("Module '{$module['titre']}' placé en 2h séparées le $jour: $creneau ($creneauxPlaces/2)");
                }
            }
            
            $essais++;
        }
        
        if ($creneauxPlaces < 2) {
            $this->placerModuleFallbackSansPause($schedule, $module, $jours, $creneaux, 2 - $creneauxPlaces, $semainesInfo);
        }
    }

    /**
     * Place plusieurs modules séparés SANS 12h-14h
     */
    private function placerModulesSeparesSansPause(&$schedule, $modules, $jours, $creneaux, $distribution, &$semainesInfo)
    {
        $totalPlaces = 0;
        
        foreach ($modules as $module) {
            $this->placerModuleSepareSansPause($schedule, $module, $jours, $creneaux, $distribution, $semainesInfo);
            $totalPlaces++;
        }
        
        return $totalPlaces;
    }

    /**
     * Place les langues SANS 12h-14h
     */
    private function placerLanguesSansPause(&$schedule, $anglais, $francais, $jours, $creneaux, $distribution, &$semainesInfo)
    {
        $this->placerLangueSansPause($schedule, $anglais, $jours, $creneaux, $distribution, $semainesInfo);
        $this->placerLangueSansPause($schedule, $francais, $jours, $creneaux, $distribution, $semainesInfo);
    }

    /**
     * Place une langue SANS 12h-14h
     */
    private function placerLangueSansPause(&$schedule, $langue, $jours, $creneaux, $distribution, &$semainesInfo)
    {
        $place = false;
        $maxEssais = 20;
        $essais = 0;
        
        shuffle($jours);
        
        while (!$place && $essais < $maxEssais) {
            $jour = $jours[array_rand($jours)];
            $creneau = $creneaux[array_rand($creneaux)];
            
            $heuresUtilisees = $this->calculerHeuresJour($schedule, $jour, $creneaux);
            if ($heuresUtilisees + 2 > $distribution[$jour]) {
                $essais++;
                continue;
            }
            
            if ($schedule[$jour][$creneau] === null) {
                $schedule[$jour][$creneau] = $langue['titre'];
                
                $semainesInfo[$jour][$creneau] = [
                    'module' => $langue['titre'],
                    'semaine_debut' => $langue['semaine_debut'],
                    'semaine_fin' => $langue['semaine_fin']
                ];
                
                $place = true;
                Log::info("Langue '{$langue['titre']}' placée le $jour: $creneau");
            }
            
            $essais++;
        }
        
        if (!$place) {
            $this->placerModuleFallbackSansPause($schedule, $langue, $jours, $creneaux, 1, $semainesInfo);
        }
    }

    /**
     * Fallback pour placer un module SANS 12h-14h
     */
    private function placerModuleFallbackSansPause(&$schedule, $module, $jours, $creneaux, $nombreCreneaux, &$semainesInfo)
    {
        $places = 0;
        
        foreach ($jours as $jour) {
            foreach ($creneaux as $creneau) {
                if ($schedule[$jour][$creneau] === null && $places < $nombreCreneaux) {
                    $schedule[$jour][$creneau] = $module['titre'];
                    
                    $semainesInfo[$jour][$creneau] = [
                        'module' => $module['titre'],
                        'semaine_debut' => $module['semaine_debut'],
                        'semaine_fin' => $module['semaine_fin']
                    ];
                    
                    $places++;
                    Log::warning("Module '{$module['titre']}' placé en fallback le $jour: $creneau");
                    
                    if ($places >= $nombreCreneaux) {
                        return;
                    }
                }
            }
        }
    }

    /**
     * Remplit les créneaux vides SANS 12h-14h
     */
    private function remplirCreneauxVidesSansPause(&$schedule, $techModules, $jours, $creneaux, $distribution, &$semainesInfo)
    {
        foreach ($jours as $jour) {
            $heuresUtilisees = $this->calculerHeuresJour($schedule, $jour, $creneaux);
            $cibleHeures = $distribution[$jour];
            
            while ($heuresUtilisees < $cibleHeures) {
                $creneauTrouve = false;
                
                foreach ($creneaux as $creneau) {
                    if ($schedule[$jour][$creneau] === null) {
                        $module = $techModules[array_rand($techModules)];
                        $schedule[$jour][$creneau] = $module['titre'];
                        
                        $semainesInfo[$jour][$creneau] = [
                            'module' => $module['titre'],
                            'semaine_debut' => $module['semaine_debut'],
                            'semaine_fin' => $module['semaine_fin']
                        ];
                        
                        $heuresUtilisees += 2;
                        $creneauTrouve = true;
                        Log::debug("Remplissage $jour $creneau avec {$module['titre']}");
                        break;
                    }
                }
                
                if (!$creneauTrouve) {
                    break;
                }
            }
        }
    }

    /**
     * Ajuste les heures des modules SANS 12h-14h
     */
    private function ajusterHeuresModulesSansPause(&$schedule, $techModules, $anglais, $francais, $jours, $creneaux, &$semainesInfo)
    {
        $heuresModules = [];
        foreach ($jours as $jour) {
            foreach ($creneaux as $creneau) {
                $module = $schedule[$jour][$creneau];
                if ($module) {
                    $heuresModules[$module] = ($heuresModules[$module] ?? 0) + 2;
                }
            }
        }
        
        foreach ($techModules as $moduleData) {
            $module = $moduleData['titre'];
            $heuresActuelles = $heuresModules[$module] ?? 0;
            
            if ($heuresActuelles < 4) {
                $this->ajouterHeuresModuleSansPause($schedule, $moduleData, 4 - $heuresActuelles, $jours, $creneaux, $semainesInfo);
            } elseif ($heuresActuelles > 4) {
                $this->retirerHeuresModuleSansPause($schedule, $module, $heuresActuelles - 4, $jours, $creneaux, $semainesInfo);
            }
        }
        
        $langues = [$anglais, $francais];
        foreach ($langues as $langueData) {
            $langue = $langueData['titre'];
            $heuresActuelles = $heuresModules[$langue] ?? 0;
            
            if ($heuresActuelles < 2) {
                $this->ajouterHeuresModuleSansPause($schedule, $langueData, 2 - $heuresActuelles, $jours, $creneaux, $semainesInfo);
            } elseif ($heuresActuelles > 2) {
                $this->retirerHeuresModuleSansPause($schedule, $langue, $heuresActuelles - 2, $jours, $creneaux, $semainesInfo);
            }
        }
    }

    /**
     * Ajoute des heures SANS 12h-14h
     */
    private function ajouterHeuresModuleSansPause(&$schedule, $moduleData, $heuresAAjouter, $jours, $creneaux, &$semainesInfo)
    {
        $creneauxAAjouter = $heuresAAjouter / 2;
        $module = $moduleData['titre'];
        
        for ($i = 0; $i < $creneauxAAjouter; $i++) {
            foreach ($jours as $jour) {
                foreach ($creneaux as $creneau) {
                    if ($schedule[$jour][$creneau] === null) {
                        $schedule[$jour][$creneau] = $module;
                        
                        $semainesInfo[$jour][$creneau] = [
                            'module' => $module,
                            'semaine_debut' => $moduleData['semaine_debut'],
                            'semaine_fin' => $moduleData['semaine_fin']
                        ];
                        
                        continue 3;
                    }
                }
            }
        }
    }

    /**
     * Retire des heures SANS 12h-14h
     */
    private function retirerHeuresModuleSansPause(&$schedule, $module, $heuresARetirer, $jours, $creneaux, &$semainesInfo)
    {
        $creneauxARetirer = $heuresARetirer / 2;
        $retires = 0;
        
        foreach ($jours as $jour) {
            foreach ($creneaux as $creneau) {
                if ($schedule[$jour][$creneau] === $module && $retires < $creneauxARetirer) {
                    $schedule[$jour][$creneau] = null;
                    unset($semainesInfo[$jour][$creneau]);
                    $retires++;
                    
                    if ($retires >= $creneauxARetirer) {
                        return;
                    }
                }
            }
        }
    }

    /**
     * Calcule les heures utilisées un jour
     */
    private function calculerHeuresJour($schedule, $jour, $creneaux)
    {
        $heures = 0;
        foreach ($creneaux as $creneau) {
            if ($schedule[$jour][$creneau] !== null) {
                $heures += 2;
            }
        }
        return $heures;
    }

    /**
     * Détermine la spécialité principale d'une filière
     */
    private function determinerSpecialiteFiliere($filiere)
    {
        $nom = strtolower($filiere->nom);
        
        if (strpos($nom, 'informatique') !== false) {
            return 'Informatique';
        }
        
        if (strpos($nom, 'industriel') !== false) {
            return 'Génie Industriel';
        }
        
        if (strpos($nom, 'civil') !== false) {
            return 'Génie Civil';
        }
        
        if (strpos($nom, 'mathématique') !== false || strpos($nom, 'math') !== false) {
            return 'Mathématiques';
        }
        
        if (strpos($nom, 'physique') !== false) {
            return 'Physique';
        }
        
        return 'Général';
    }
    
    /**
     * Détermine la spécialité d'un module
     */
    private function determinerSpecialiteModule($module)
    {
        $moduleLower = strtolower($module);
        
        if (strpos($moduleLower, 'anglais') !== false || strpos($moduleLower, 'français') !== false) {
            return 'Langues';
        }
        
        if (strpos($moduleLower, 'programmation') !== false || 
            strpos($moduleLower, 'python') !== false || 
            strpos($moduleLower, 'java') !== false || 
            strpos($moduleLower, 'c++') !== false ||
            strpos($moduleLower, 'base de données') !== false || 
            strpos($moduleLower, 'réseau') !== false || 
            strpos($moduleLower, 'web') !== false || 
            strpos($moduleLower, 'algorithm') !== false || 
            strpos($moduleLower, 'informatique') !== false ||
            strpos($moduleLower, 'intelligence artificielle') !== false ||
            strpos($moduleLower, 'cloud') !== false ||
            strpos($moduleLower, 'mobile') !== false ||
            strpos($moduleLower, 'sécurité') !== false) {
            return 'Informatique';
        }
        
        if (strpos($moduleLower, 'mathématique') !== false || 
            strpos($moduleLower, 'algèbre') !== false || 
            strpos($moduleLower, 'analyse') !== false || 
            strpos($moduleLower, 'statistique') !== false || 
            strpos($moduleLower, 'probabilité') !== false || 
            strpos($moduleLower, 'logique') !== false ||
            strpos($moduleLower, 'numérique') !== false ||
            strpos($moduleLower, 'graphe') !== false ||
            strpos($moduleLower, 'recherche opérationnelle') !== false) {
            return 'Mathématiques';
        }
        
        if (strpos($moduleLower, 'génie industriel') !== false || 
            strpos($moduleLower, 'production') !== false || 
            strpos($moduleLower, 'maintenance') !== false || 
            strpos($moduleLower, 'qualité') !== false || 
            strpos($moduleLower, 'logistique') !== false || 
            strpos($moduleLower, 'automatisme') !== false || 
            strpos($moduleLower, 'robotique') !== false ||
            strpos($moduleLower, 'résistance des matériaux') !== false ||
            strpos($moduleLower, 'thermodynamique') !== false ||
            strpos($moduleLower, 'mécanique des fluides') !== false ||
            strpos($moduleLower, 'science des matériaux') !== false) {
            return 'Génie Industriel';
        }
        
        if (strpos($moduleLower, 'physique') !== false || 
            strpos($moduleLower, 'thermodynamique') !== false || 
            strpos($moduleLower, 'mécanique') !== false || 
            strpos($moduleLower, 'résistance') !== false || 
            strpos($moduleLower, 'matériaux') !== false ||
            strpos($moduleLower, 'énergétique') !== false) {
            return 'Physique';
        }
        
        return 'Général';
    }
    
    /**
     * Vérifie si un module est une langue
     */
    private function estModuleLangue($module)
    {
        $moduleLower = strtolower($module);
        return strpos($moduleLower, 'anglais') !== false || 
               strpos($moduleLower, 'français') !== false ||
               strpos($moduleLower, 'francais') !== false ||
               strpos($moduleLower, 'langue') !== false;
    }
    
    /**
     * Récupère la liste des enseignants affectés
     */
    private function getEnseignantsAffectes($affectations)
    {
        $enseignants = [];
        
        foreach ($affectations as $jour => $creneaux) {
            foreach ($creneaux as $creneau => $affectation) {
                if ($affectation['enseignant']) {
                    $enseignant = $affectation['enseignant'];
                    if (!isset($enseignants[$enseignant['id']])) {
                        $enseignants[$enseignant['id']] = [
                            'id' => $enseignant['id'],
                            'nom' => $enseignant['nom'],
                            'specialite' => $enseignant['specialite'],
                            'heures' => 0
                        ];
                    }
                    $enseignants[$enseignant['id']]['heures'] += 2;
                }
            }
        }
        
        return array_values($enseignants);
    }

    /**
     * Calcule les statistiques avec vérification des modules consécutifs
     */
    private function calculerStatistiquesAvecConsecutifs($schedule, $affectations, $jours, $creneaux)
    {
        $heuresParJour = [];
        $heuresParModule = [];
        $modulesConsecutifs = 0;
        $modulesSepares = 0;
        $modulesAvecMemeProf = 0;
        $modulesAvecProfDifferent = 0;
        $modulesAvecMemeSalle = 0;
        $modulesAvecSalleDifferent = 0;
        
        foreach ($jours as $jour) {
            $heuresJour = 0;
            
            foreach ($creneaux as $creneau) {
                $module = $schedule[$jour][$creneau];
                if ($module) {
                    $heuresJour += 2;
                    $heuresParModule[$module] = ($heuresParModule[$module] ?? 0) + 2;
                }
            }
            
            $heuresParJour[$jour] = $heuresJour;
        }
        
        foreach ($heuresParModule as $module => $heures) {
            $enseignants = [];
            $salles = [];
            
            foreach ($jours as $jour) {
                foreach ($creneaux as $creneau) {
                    if ($schedule[$jour][$creneau] === $module) {
                        $enseignantId = $affectations[$jour][$creneau]['enseignant']['id'] ?? null;
                        if ($enseignantId) {
                            $enseignants[$enseignantId] = $affectations[$jour][$creneau]['enseignant']['nom'] ?? 'Inconnu';
                        }
                        
                        $salleId = $affectations[$jour][$creneau]['salle']['id'] ?? null;
                        if ($salleId) {
                            $salles[$salleId] = $affectations[$jour][$creneau]['salle']['nom'] ?? 'Inconnue';
                        }
                    }
                }
            }
            
            $estConsecutif = false;
            foreach ($jours as $jour) {
                for ($i = 0; $i < count($creneaux) - 1; $i++) {
                    if ($schedule[$jour][$creneaux[$i]] === $module && 
                        $schedule[$jour][$creneaux[$i + 1]] === $module) {
                        $estConsecutif = true;
                        break 2;
                    }
                }
            }
            
            if ($estConsecutif) {
                $modulesConsecutifs++;
            } else {
                $modulesSepares++;
            }
            
            if (count($enseignants) === 1) {
                $modulesAvecMemeProf++;
                Log::info("✅ Module '$module': Même prof (" . reset($enseignants) . ") pour toutes les sessions");
            } elseif (count($enseignants) > 1) {
                $modulesAvecProfDifferent++;
                Log::warning("❌ Module '$module': " . count($enseignants) . " profs différents");
            }
            
            if (count($salles) === 1) {
                $modulesAvecMemeSalle++;
                Log::info("✅ Module '$module': Même salle (" . reset($salles) . ") pour toutes les sessions");
            } elseif (count($salles) > 1) {
                $modulesAvecSalleDifferent++;
                Log::warning("❌ Module '$module': " . count($salles) . " salles différentes");
            }
        }
        
        $enseignants = [];
        $salles = [];
        
        if ($affectations) {
            foreach ($jours as $jour) {
                foreach ($creneaux as $creneau) {
                    $affectation = $affectations[$jour][$creneau] ?? null;
                    if ($affectation) {
                        if ($affectation['enseignant']) {
                            $enseignants[$affectation['enseignant']['id']] = $affectation['enseignant'];
                        }
                        if ($affectation['salle']) {
                            $salles[$affectation['salle']['id']] = $affectation['salle'];
                        }
                    }
                }
            }
        }
        
        return [
            'total_heures' => array_sum($heuresParJour),
            'heures_par_jour' => $heuresParJour,
            'modules_consecutifs' => $modulesConsecutifs,
            'modules_separes' => $modulesSepares,
            'modules_meme_prof' => $modulesAvecMemeProf,
            'modules_prof_different' => $modulesAvecProfDifferent,
            'modules_meme_salle' => $modulesAvecMemeSalle,
            'modules_salle_different' => $modulesAvecSalleDifferent,
            'nombre_enseignants' => count($enseignants),
            'nombre_salles' => count($salles),
            'taux_success_meme_prof' => ($modulesAvecMemeProf / max(1, ($modulesAvecMemeProf + $modulesAvecProfDifferent))) * 100,
            'taux_success_meme_salle' => ($modulesAvecMemeSalle / max(1, ($modulesAvecMemeSalle + $modulesAvecSalleDifferent))) * 100
        ];
    }
}
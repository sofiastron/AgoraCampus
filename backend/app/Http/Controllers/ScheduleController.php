<?php

namespace App\Http\Controllers;

use App\Models\Filiere;
use App\Models\Groupe;
use App\Models\Module;
use App\Models\Enseignant;
use App\Models\Salle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ScheduleController extends Controller
{
    // ... Les méthodes getFilieres() et getNiveaux() restent inchangées ...
    public function getFilieres()
    {
        try {
            // Vérifier si le modèle Filiere existe
            if (class_exists('App\Models\Filiere')) {
                $filieres = Filiere::select('id', 'nom', 'code')->get();
            } else {
                // Données par défaut si le modèle n'existe pas
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
            
            // Données de secours en cas d'erreur
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
            // Vérifier si le modèle Groupe existe
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
                // Données par défaut
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
     */
    public function generateFromDb(Request $request)
    {
        try {
            Log::info('=== DÉBUT GÉNÉRATION EMPLOI DU TEMPS SANS 12h-14h ===');
            
            $request->validate([
                'filiere_id' => 'required|exists:filieres,id',
                'niveau' => 'nullable|string'
            ]);

            $filiere = Filiere::findOrFail($request->filiere_id);
            
            // Filtrer les groupes par niveau si spécifié
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
            // REMOVED '12h-14h' from creneaux
            $creneauxComplets = ['08h-10h', '10h-12h', '14h-16h', '16h-18h'];

            // Récupérer TOUS les enseignants
            $enseignants = Enseignant::all();
            Log::info("Total enseignants disponibles: " . $enseignants->count());

            // Récupérer les salles
            $salles = [];
            if (class_exists('App\Models\Salle')) {
                $salles = Salle::where('disponible', 1)->get();
            }

            // Modules communs (langues)
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
                'regles' => 'Modules consécutifs de 4h | Langues = 2h | Pas de cours 12h-14h'
            ];

            foreach ($groupes as $groupe) {
                Log::info("Génération pour groupe: {$groupe->nom} (Filière: {$filiere->nom}, Niveau: {$groupe->niveau})");
                
                // Récupérer les modules SPÉCIFIQUES au niveau AVEC les semaines
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
                
                // Prendre maximum 7 modules
                $techModules = $modulesTechniques->take(7)->map(function($module) {
                    return [
                        'id' => $module->id,
                        'titre' => $module->titre,
                        'semaine_debut' => $module->semaine_debut ?? 1,
                        'semaine_fin' => $module->semaine_fin ?? 13
                    ];
                })->toArray();
                
                // Générer l'emploi du temps SANS créneau 12h-14h
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
                
                // Affecter les enseignants et salles
                $affectations = $this->affecterEnseignantsEtSalles(
                    $schedule,
                    $enseignants,
                    $salles,
                    $jours,
                    $creneauxComplets,
                    $groupe->nom,
                    $techModules,
                    $anglais,
                    $francais,
                    $filiere
                );
                
                $scheduleByGroup[$groupe->nom] = $schedule;
                $affectationsByGroup[$groupe->nom] = $affectations;
                $semainesByGroup[$groupe->nom] = $semainesInfo;
                
                // Calculer les statistiques
                $stats = $this->calculerStatistiquesAvecConsecutifs($schedule, $affectations, $jours, $creneauxComplets);
                $stats['niveau'] = $groupe->niveau;
                $stats['modules_utilises'] = array_column($techModules, 'titre');
                $stats['semaines_info'] = $semainesInfo;
                
                // Ajouter info sur les enseignants affectés
                $stats['enseignants_affectes'] = $this->getEnseignantsAffectes($affectations);
                
                $statistics['groupes'][$groupe->nom] = $stats;
            }

            Log::info('=== GÉNÉRATION TERMINÉE AVEC SUCCÈS (SANS 12h-14h) ===');
            
            return response()->json([
                'success' => true,
                'schedule' => $scheduleByGroup,
                'affectations' => $affectationsByGroup,
                'semaines' => $semainesByGroup,
                'statistics' => $statistics,
                'jours' => $jours,
                'creneaux' => $creneauxComplets,
                'message' => 'Emploi du temps généré sans créneau 12h-14h'
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
     * Génère un emploi du temps SANS créneau 12h-14h
     */
    private function generateScheduleSansPauseDejeuner($techModules, $anglais, $francais, $jours, $creneaux, $groupeName, $niveau)
    {
        $schedule = [];
        $semainesInfo = [];
        
        // Initialiser la grille
        foreach ($jours as $jour) {
            foreach ($creneaux as $creneau) {
                $schedule[$jour][$creneau] = null;
            }
        }
        
        // Distribution horaire réduite (pas de 12h-14h)
        $distribution = $this->getDistributionSansPauseDejeuner($niveau);
        
        // Déterminer combien de modules seront consécutifs
        $nombreModulesConsecutifs = rand(3, 4);
        Log::info("$groupeName aura $nombreModulesConsecutifs modules en 4h consécutives (sans 12h-14h)");
        
        // Sélectionner les modules qui seront consécutifs
        $modulesPourConsecutifs = array_slice($techModules, 0, $nombreModulesConsecutifs);
        $modulesPourSepares = array_slice($techModules, $nombreModulesConsecutifs);
        
        // Étape 1: Placer les modules en 4h consécutives
        $modulesConsecutifsPlaces = $this->placerModulesConsecutifsSansPause(
            $schedule, 
            $modulesPourConsecutifs, 
            $jours, 
            $creneaux, 
            $distribution,
            $semainesInfo
        );
        
        // Étape 2: Placer les modules séparés
        $modulesSeparesPlaces = $this->placerModulesSeparesSansPause(
            $schedule,
            $modulesPourSepares,
            $jours,
            $creneaux,
            $distribution,
            $semainesInfo
        );
        
        // Étape 3: Placer les langues (2h chacune)
        $this->placerLanguesSansPause($schedule, $anglais, $francais, $jours, $creneaux, $distribution, $semainesInfo);
        
        // Étape 4: Remplir les créneaux vides si nécessaire
        $this->remplirCreneauxVidesSansPause($schedule, $techModules, $jours, $creneaux, $distribution, $semainesInfo);
        
        // Étape 5: Ajuster pour s'assurer que chaque module a exactement 4h
        $this->ajusterHeuresModulesSansPause($schedule, $techModules, $anglais, $francais, $jours, $creneaux, $semainesInfo);
        
        return [
            'schedule' => $schedule,
            'semaines_info' => $semainesInfo
        ];
    }

    /**
     * Distribution horaire SANS créneau 12h-14h
     */
    private function getDistributionSansPauseDejeuner($niveau)
    {
        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
        
        // Extraire le numéro du niveau
        $niveauNum = 1;
        if (strpos($niveau, '1') !== false) $niveauNum = 1;
        elseif (strpos($niveau, '2') !== false) $niveauNum = 2;
        elseif (strpos($niveau, '3') !== false) $niveauNum = 3;
        
        // Distributions réduites (pas de 12h-14h, donc 8h max par jour)
        $distributions = [
            1 => [6, 8, 6, 8, 4],  // 1ère année
            2 => [8, 6, 8, 6, 4],  // 2ème année  
            3 => [8, 8, 6, 6, 4],  // 3ème année
        ];
        
        $selected = $distributions[$niveauNum] ?? [8, 6, 8, 6, 4];
        shuffle($selected);
        
        $result = [];
        foreach ($jours as $index => $jour) {
            $result[$jour] = $selected[$index];
        }
        
        return $result;
    }

    /**
     * Place les modules en 4h consécutives SANS 12h-14h
     */
    private function placerModulesConsecutifsSansPause(&$schedule, $modules, $jours, $creneaux, $distribution, &$semainesInfo)
    {
        $places = 0;
        $pairesCreneaux = [
            ['08h-10h', '10h-12h'],   // Matin seulement
        ];
        
        shuffle($jours);
        
        foreach ($modules as $module) {
            $place = false;
            
            // Essayer de placer le module en 4h consécutives
            foreach ($jours as $jour) {
                // Vérifier les heures disponibles ce jour
                $heuresUtilisees = $this->calculerHeuresJour($schedule, $jour, $creneaux);
                if ($heuresUtilisees + 4 > $distribution[$jour]) {
                    continue; // Pas assez d'heures ce jour
                }
                
                // Essayer chaque paire de créneaux
                foreach ($pairesCreneaux as $paire) {
                    $creneau1 = $paire[0];
                    $creneau2 = $paire[1];
                    
                    // Vérifier si les deux créneaux sont libres
                    if ($schedule[$jour][$creneau1] === null && $schedule[$jour][$creneau2] === null) {
                        // Placer le module
                        $schedule[$jour][$creneau1] = $module['titre'];
                        $schedule[$jour][$creneau2] = $module['titre'];
                        
                        // Enregistrer les semaines pour chaque créneau
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
                        Log::info("Module '{$module['titre']}' placé en 4h consécutives le $jour: $creneau1-$creneau2 (semaines {$module['semaine_debut']}-{$module['semaine_fin']})");
                        break 2;
                    }
                }
            }
            
            // Si on n'a pas pu placer en consécutif, on place en séparé
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
            
            // Vérifier les heures disponibles
            $heuresUtilisees = $this->calculerHeuresJour($schedule, $jour, $creneaux);
            if ($heuresUtilisees + 2 > $distribution[$jour]) {
                $essais++;
                continue;
            }
            
            // Vérifier si le créneau est libre
            if ($schedule[$jour][$creneau] === null) {
                // Vérifier que ce n'est pas à côté d'un autre créneau du même module
                $indexCreneau = array_search($creneau, $creneaux);
                $estConsecutif = false;
                
                // Vérifier le créneau précédent
                if ($indexCreneau > 0) {
                    $creneauPrecedent = $creneaux[$indexCreneau - 1];
                    if ($schedule[$jour][$creneauPrecedent] === $module['titre']) {
                        $estConsecutif = true;
                    }
                }
                
                // Vérifier le créneau suivant
                if ($indexCreneau < count($creneaux) - 1) {
                    $creneauSuivant = $creneaux[$indexCreneau + 1];
                    if ($schedule[$jour][$creneauSuivant] === $module['titre']) {
                        $estConsecutif = true;
                    }
                }
                
                if (!$estConsecutif) {
                    $schedule[$jour][$creneau] = $module['titre'];
                    
                    // Enregistrer les semaines
                    $semainesInfo[$jour][$creneau] = [
                        'module' => $module['titre'],
                        'semaine_debut' => $module['semaine_debut'],
                        'semaine_fin' => $module['semaine_fin']
                    ];
                    
                    $creneauxPlaces++;
                    Log::info("Module '{$module['titre']}' placé en 2h séparées le $jour: $creneau ($creneauxPlaces/2) (semaines {$module['semaine_debut']}-{$module['semaine_fin']})");
                }
            }
            
            $essais++;
        }
        
        // Fallback: placer n'importe où
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
            
            // Vérifier les heures disponibles
            $heuresUtilisees = $this->calculerHeuresJour($schedule, $jour, $creneaux);
            if ($heuresUtilisees + 2 > $distribution[$jour]) {
                $essais++;
                continue;
            }
            
            // Vérifier si le créneau est libre
            if ($schedule[$jour][$creneau] === null) {
                $schedule[$jour][$creneau] = $langue['titre'];
                
                // Enregistrer les semaines
                $semainesInfo[$jour][$creneau] = [
                    'module' => $langue['titre'],
                    'semaine_debut' => $langue['semaine_debut'],
                    'semaine_fin' => $langue['semaine_fin']
                ];
                
                $place = true;
                Log::info("Langue '{$langue['titre']}' placée le $jour: $creneau (semaines {$langue['semaine_debut']}-{$langue['semaine_fin']})");
            }
            
            $essais++;
        }
        
        // Fallback
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
                    
                    // Enregistrer les semaines
                    $semainesInfo[$jour][$creneau] = [
                        'module' => $module['titre'],
                        'semaine_debut' => $module['semaine_debut'],
                        'semaine_fin' => $module['semaine_fin']
                    ];
                    
                    $places++;
                    Log::warning("Module '{$module['titre']}' placé en fallback le $jour: $creneau (semaines {$module['semaine_debut']}-{$module['semaine_fin']})");
                    
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
            
            // Ajouter des heures si nécessaire
            while ($heuresUtilisees < $cibleHeures) {
                $creneauTrouve = false;
                
                foreach ($creneaux as $creneau) {
                    if ($schedule[$jour][$creneau] === null) {
                        // Choisir un module aléatoire
                        $module = $techModules[array_rand($techModules)];
                        $schedule[$jour][$creneau] = $module['titre'];
                        
                        // Enregistrer les semaines
                        $semainesInfo[$jour][$creneau] = [
                            'module' => $module['titre'],
                            'semaine_debut' => $module['semaine_debut'],
                            'semaine_fin' => $module['semaine_fin']
                        ];
                        
                        $heuresUtilisees += 2;
                        $creneauTrouve = true;
                        Log::debug("Remplissage $jour $creneau avec {$module['titre']} (semaines {$module['semaine_debut']}-{$module['semaine_fin']})");
                        break;
                    }
                }
                
                if (!$creneauTrouve) {
                    break; // Tous les créneaux sont remplis
                }
            }
        }
    }

    /**
     * Ajuste les heures des modules SANS 12h-14h
     */
    private function ajusterHeuresModulesSansPause(&$schedule, $techModules, $anglais, $francais, $jours, $creneaux, &$semainesInfo)
    {
        // Compter les heures actuelles
        $heuresModules = [];
        foreach ($jours as $jour) {
            foreach ($creneaux as $creneau) {
                $module = $schedule[$jour][$creneau];
                if ($module) {
                    $heuresModules[$module] = ($heuresModules[$module] ?? 0) + 2;
                }
            }
        }
        
        // Ajuster modules techniques à 4h
        foreach ($techModules as $moduleData) {
            $module = $moduleData['titre'];
            $heuresActuelles = $heuresModules[$module] ?? 0;
            
            if ($heuresActuelles < 4) {
                $this->ajouterHeuresModuleSansPause($schedule, $moduleData, 4 - $heuresActuelles, $jours, $creneaux, $semainesInfo);
            } elseif ($heuresActuelles > 4) {
                $this->retirerHeuresModuleSansPause($schedule, $module, $heuresActuelles - 4, $jours, $creneaux, $semainesInfo);
            }
        }
        
        // Ajuster langues à 2h
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
                        
                        // Enregistrer les semaines
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
     * Affectation des enseignants et salles
     */
    private function affecterEnseignantsEtSalles($schedule, $enseignants, $salles, $jours, $creneaux, $groupeName, $techModules, $anglais, $francais, $filiere)
    {
        $affectations = [];
        $enseignantsDisponibles = $enseignants->toArray();
        $sallesDisponibles = !empty($salles) ? $salles->toArray() : [];
        
        // Déterminer la spécialité cible selon la filière
        $specialiteCible = $this->determinerSpecialiteFiliere($filiere);
        
        Log::info("Filtre spécialité pour filière {$filiere->nom}: $specialiteCible");
        
        // Suivi des heures par enseignant
        $heuresEnseignants = [];
        foreach ($enseignants as $enseignant) {
            $heuresEnseignants[$enseignant->id] = 0;
        }
        
        foreach ($jours as $jour) {
            $affectations[$jour] = [];
            
            foreach ($creneaux as $creneau) {
                $module = $schedule[$jour][$creneau];
                
                if ($module) {
                    $affectation = [
                        'module' => $module,
                        'enseignant' => null,
                        'salle' => null
                    ];
                    
                    // Déterminer la spécialité du module
                    $specialiteModule = $this->determinerSpecialiteModule($module);
                    
                    // Trouver un enseignant AVEC FILTRE
                    $enseignantsFiltres = array_filter($enseignantsDisponibles, function($e) use ($specialiteModule, $specialiteCible) {
                        // Priorité 1: Spécialité exacte du module
                        if (stripos($e['specialite'] ?? '', $specialiteModule) !== false) {
                            return true;
                        }
                        
                        // Priorité 2: Spécialité de la filière
                        if (stripos($e['specialite'] ?? '', $specialiteCible) !== false) {
                            return true;
                        }
                        
                        // Priorité 3: Enseignants "Général"
                        if ($specialiteCible === 'Général' || ($e['specialite'] ?? '') === 'Général') {
                            return true;
                        }
                        
                        return false;
                    });
                    
                    if (!empty($enseignantsFiltres)) {
                        // Trier par heures déjà affectées
                        usort($enseignantsFiltres, function($a, $b) use ($heuresEnseignants) {
                            return ($heuresEnseignants[$a['id']] ?? 0) - ($heuresEnseignants[$b['id']] ?? 0);
                        });
                        
                        $enseignantChoisi = $enseignantsFiltres[0];
                        $heuresMax = $enseignantChoisi['heures_max_semaine'] ?? 20;
                        
                        if (($heuresEnseignants[$enseignantChoisi['id']] ?? 0) + 2 <= $heuresMax) {
                            $affectation['enseignant'] = [
                                'id' => $enseignantChoisi['id'],
                                'nom' => $enseignantChoisi['nom'],
                                'specialite' => $enseignantChoisi['specialite'] ?? 'Général'
                            ];
                            $heuresEnseignants[$enseignantChoisi['id']] += 2;
                        }
                    }
                    
                    // Trouver une salle
                    if (!empty($sallesDisponibles)) {
                        $salleChoisie = $sallesDisponibles[array_rand($sallesDisponibles)];
                        $affectation['salle'] = [
                            'id' => $salleChoisie['id'] ?? 1,
                            'nom' => $salleChoisie['nom'] ?? 'Salle ' . rand(1, 20),
                            'batiment' => $salleChoisie['batiment'] ?? 'Bâtiment Principal',
                            'capacite' => $salleChoisie['capacite'] ?? 30
                        ];
                    } else {
                        $affectation['salle'] = [
                            'id' => 0,
                            'nom' => 'Salle ' . rand(1, 20),
                            'batiment' => 'Bâtiment Principal',
                            'capacite' => 30
                        ];
                    }
                    
                    $affectations[$jour][$creneau] = $affectation;
                }
            }
        }
        
        return $affectations;
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
        
        // Langues
        if (strpos($moduleLower, 'anglais') !== false || strpos($moduleLower, 'français') !== false) {
            return 'Langues';
        }
        
        // Informatique
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
        
        // Mathématiques
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
        
        // Génie Industriel
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
        
        // Physique
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
     * Calcule les statistiques
     */
    private function calculerStatistiquesAvecConsecutifs($schedule, $affectations, $jours, $creneaux)
    {
        $heuresParJour = [];
        $heuresParModule = [];
        $modulesConsecutifs = 0;
        $modulesSepares = 0;
        
        // Compter les heures
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
        
        // Identifier les modules consécutifs vs séparés
        foreach ($heuresParModule as $module => $heures) {
            if ($heures === 4) { // Module technique
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
            }
        }
        
        // Compter enseignants et salles
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
            'nombre_enseignants' => count($enseignants),
            'nombre_salles' => count($salles)
        ];
    }
}
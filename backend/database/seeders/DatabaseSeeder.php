<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Module;
use App\Models\Enseignant;
use App\Models\Etudiant;
use App\Models\Annonce;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // =====================
        // ENSEIGNANTS
        // =====================
        $user1 = User::create([
            'nom' => 'Ali',
            'email' => 'ali1455@test.com',
            'password' => Hash::make('123456'),
            'role' => 'enseignant'
        ]);

        $ens1 = Enseignant::create([
            'user_id' => $user1->id,
            'grade' => 'Professeur',
            'departement' => 'Maths'
        ]);

        $user2 = User::create([
            'nom' => 'Sara',
            'email' => 'sara6551@test.com',
            'password' => Hash::make('123456'),
            'role' => 'enseignant'
        ]);

        $ens2 = Enseignant::create([
            'user_id' => $user2->id,
            'grade' => 'Professeur',
            'departement' => 'Informatique'
        ]);

        // =====================
        // MODULES
        // =====================
        $math = Module::create([
            'titre' => 'Maths',
            'enseignant_id' => $ens1->id
        ]);

        $phys = Module::create([
            'titre' => 'Physique',
            'enseignant_id' => $ens1->id
        ]);

        $info = Module::create([
            'titre' => 'Informatique',
            'enseignant_id' => $ens2->id
        ]);

        // =====================
        // ANNONCES
        // =====================
        Annonce::create([
            'titre' => 'TP Maths',
            'contenu' => 'TP équations',
            'date_creation' => now(),
            'module_id' => $math->id,
            'enseignant_id' => $ens1->id
        ]);

        Annonce::create([
            'titre' => 'TP Info',
            'contenu' => 'TP algorithmes',
            'date_creation' => now(),
            'module_id' => $info->id,
            'enseignant_id' => $ens2->id
        ]);

        // =====================
        // ÉTUDIANT
        // =====================
        $userEtudiant = User::create([
            'nom' => 'Amine',
            'email' => 'amine123@test.com',
            'password' => Hash::make('123456'),
            'role' => 'etudiant'
        ]);

        Etudiant::create([
            'user_id' => $userEtudiant->id,
            'cne' => 'CNE12345678',
            'niveau' => 'Licence 1',
            'groupe_id' => 1
        ]);
    }
}

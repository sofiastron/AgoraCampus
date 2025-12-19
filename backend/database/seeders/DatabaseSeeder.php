<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Module;
use App\Models\Enseignant;
use App\Models\Annonce;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
       
        $user1 = User::firstOrCreate(
            ['email' => 'ali14@test.com'],
            [
                'nom' => 'Ali',
                'password' => Hash::make('123456'),
                'role' => 'enseignant'
            ]
        );

        $enseignant1 = Enseignant::firstOrCreate(
            ['id' => $user1->id]
        );

        $user2 = User::firstOrCreate(
            ['email' => 'sara61@test.com'],
            [
                'nom' => 'Sara',
                'password' => Hash::make('123456'),
                'role' => 'enseignant'
            ]
        );

        $enseignant2 = Enseignant::firstOrCreate(
            ['id' => $user2->id]
        );

      
        $math = Module::firstOrCreate(['titre' => 'Maths']);
        $phys = Module::firstOrCreate(['titre' => 'Physique']);
        $info = Module::firstOrCreate(['titre' => 'Informatique']);

        
        Annonce::firstOrCreate([
            'titre' => 'TP n°1',
            'module_id' => $math->id,
            'enseignant_id' => $enseignant1->id
        ], [
            'contenu' => 'TP sur les équations',
            'date_creation' => now()
        ]);

        Annonce::firstOrCreate([
            'titre' => 'TP n°2',
            'module_id' => $math->id,
            'enseignant_id' => $enseignant2->id
        ], [
            'contenu' => 'TP sur les dérivées',
            'date_creation' => now()
        ]);

        Annonce::firstOrCreate([
            'titre' => 'TP Physique',
            'module_id' => $phys->id,
            'enseignant_id' => $enseignant1->id
        ], [
            'contenu' => 'TP sur les forces',
            'date_creation' => now()
        ]);

        Annonce::firstOrCreate([
            'titre' => 'TP Informatique',
            'module_id' => $info->id,
            'enseignant_id' => $enseignant2->id
        ], [
            'contenu' => 'TP sur les algorithmes',
            'date_creation' => now()
        ]);
    }
}

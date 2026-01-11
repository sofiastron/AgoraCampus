<?php

namespace Database\Seeders;

use App\Models\Enseignant;
use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\GroupeSeeder;
use Database\Seeders\EtudiantSeeder;
use Database\Seeders\EnseignantSeeder;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */

   public function run(): void
{
    $this->call([
        UserSeeder::class,
        GroupeSeeder::class,
         EtudiantSeeder::class,
            EnseignantSeeder::class,
    ]);
}


    
}

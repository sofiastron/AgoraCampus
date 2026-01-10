<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Groupe;
class GroupeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
 public function run(): void
    {
        // Générer 5 groupes factices
        Groupe::factory()->count(15)->create();
    }
}


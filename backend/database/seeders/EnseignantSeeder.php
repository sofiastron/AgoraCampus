<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class EnseignantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
          Enseignant::factory()->count(15)->create();
    }
}

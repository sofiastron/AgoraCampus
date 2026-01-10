<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
  public function run(): void
    {
        // Générer 50 utilisateurs factices
        User::factory()->count(15)->create();
    }
}

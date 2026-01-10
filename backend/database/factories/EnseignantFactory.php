<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Enseignant;
use App\Models\Groupe;
class EnseignantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
   protected $model = \App\Models\Enseignant::class;

    public function definition(): array
    {
        // Récupérer un user_id aléatoire avec le rôle 'enseignant'
        $user = User::where('role', 'enseignant')->inRandomOrder()->first();

        return [
            'nom' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'user_id' => $user ? $user->id : null,
        ];
    }
}

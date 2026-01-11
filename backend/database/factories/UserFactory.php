<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    /**
     * Le modèle associé à cette factory.
     */
    protected $model = \App\Models\User::class;

    /**
     * Définir l'état par défaut du modèle.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'nom' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'), // mot de passe par défaut
            'role' => $this->faker->randomElement(['etudiant', 'enseignant', 'administrateur']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indique que l'email n'est pas vérifié.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}

<?php

namespace Database\Factories;
use App\Models\User;
use App\Models\Groupe;
use App\Models\Etudiant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EtudiantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
  protected $model = \App\Models\Etudiant::class;

    public function definition(): array
    {
        // On ne doit générer qu'un seul étudiant à la fois
        $user = User::where('role', 'etudiant')->inRandomOrder()->first();
        $groupe = Groupe::inRandomOrder()->first();

        return [
            'cne' => strtoupper($this->faker->unique()->bothify('??####')),
            'niveau' => $this->faker->randomElement(['Licence 1', 'Licence 2', 'Licence 3', 'Master 1', 'Master 2']),
            'user_id' => $user ? $user->id : null,
            'groupe_id' => $groupe ? $groupe->id : null,
        ];
    }}
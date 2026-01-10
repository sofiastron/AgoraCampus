<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Groupe;
class GroupeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    
    protected $model = \App\Models\Groupe::class;

    public function definition(): array
    {
        return [
            'nom' => 'Groupe ' . strtoupper($this->faker->unique()->bothify('??')),
            'niveau' => $this->faker->randomElement(['Licence 1', 'Licence 2', 'Licence 3', 'Master 1', 'Master 2']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

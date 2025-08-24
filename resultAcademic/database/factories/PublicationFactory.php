<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Publication;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Publication>
 */
class PublicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(6),
            'type' => $this->faker->randomElement([
                'Revista',
                'Libro',
                'Capitulo de Libro'
            ]),
            'date' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'description' => $this->faker->paragraph(4),
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Event;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Congreso Internacional de Ciencias',
                'Simposio de Investigación',
                'Conferencia Académica Anual',
                'Workshop de Innovación',
                'Seminario de Tecnología',
                'Foro de Investigadores',
                'Encuentro Científico',
                'Jornada de Divulgación',
                'Coloquio Académico',
                'Mesa Redonda Científica'
            ]),
            'category' => $this->faker->randomElement([
                'Institucional',
                'Municipal',
                'Territorial',
                'Nacional',
                'Internacional'

            ]),
            'date' => $this->faker->dateTimeBetween('-5 years', '+1 year'),
            'description' => $this->faker->paragraph(3),
        ];
    }
}

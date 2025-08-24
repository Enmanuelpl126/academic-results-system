<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Award;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Award>
 */
class AwardFactory extends Factory
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
                'Premio Nacional de Ciencias',
                'Medalla de Honor Académica',
                'Premio a la Excelencia Docente',
                'Reconocimiento a la Investigación',
                'Premio Innovación Tecnológica',
                'Distinción Académica',
                'Premio al Mejor Investigador',
                'Medalla al Mérito Científico',
                'Premio de Divulgación Científica',
                'Reconocimiento Internacional'
            ]),
            'type' => $this->faker->randomElement([
                'CITMA Provincial',
                'Academia de Ciencias de Cuba'
            ]),
            'date' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'description' => $this->faker->paragraph(3),
        ];
    }
}

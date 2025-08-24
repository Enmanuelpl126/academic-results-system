<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Recognition;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recognition>
 */
class RecognitionFactory extends Factory
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
                'Reconocimiento por Años de Servicio',
                'Distinción por Excelencia Académica',
                'Mención Honorífica',
                'Certificado de Mérito',
                'Reconocimiento por Contribución Científica',
                'Diploma de Honor',
                'Distinción por Liderazgo',
                'Reconocimiento por Innovación',
                'Certificado de Excelencia',
                'Mención Especial'
            ]),
            'type' => $this->faker->randomElement([
                'Académico',
                'Profesional',
                'Científico',
                'Docente',
                'Administrativo'
            ]),
            'date' => $this->faker->dateTimeBetween('-3 years', 'now'),
            'description' => $this->faker->paragraph(2),
        ];
    }
}

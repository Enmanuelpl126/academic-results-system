<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Department;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
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
                'Departamento de Ciencias de la Computación',
                'Departamento de Matemáticas',
                'Departamento de Física',
                'Departamento de Química',
                'Departamento de Biología',
                'Departamento de Ingeniería',
                'Departamento de Medicina',
                'Departamento de Psicología',
                'Departamento de Filosofía',
                'Departamento de Historia'
            ]),
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Magazine;
use App\Models\Publication;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Magazine>
 */
class MagazineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Publication::factory(),
            'name' => $this->faker->randomElement([
                'Revista de Ciencias Aplicadas',
                'Journal of Computer Science',
                'Revista de Investigación Académica',
                'International Journal of Technology',
                'Revista de Matemáticas',
                'Scientific Research Journal',
                'Revista de Innovación',
                'Academic Review',
                'Journal of Applied Sciences',
                'Revista Científica Internacional'
            ]),
            'number' => $this->faker->numberBetween(1, 12),
            'volume' => $this->faker->numberBetween(1, 50),
            'doi' => '10.' . $this->faker->numberBetween(1000, 9999) . '/' . $this->faker->lexify('??????'),
        ];
    }
}

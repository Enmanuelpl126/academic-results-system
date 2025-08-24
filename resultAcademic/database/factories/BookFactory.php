<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Book;
use App\Models\Publication;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
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
            'editorial' => $this->faker->randomElement([
                'Editorial Académica',
                'Springer',
                'Elsevier',
                'McGraw-Hill',
                'Pearson',
                'Editorial Universitaria',
                'Cambridge University Press',
                'Oxford University Press',
                'Editorial Científica',
                'Wiley'
            ]),
            'place' => $this->faker->randomElement([
                'Madrid, España',
                'Barcelona, España',
                'Ciudad de México, México',
                'Buenos Aires, Argentina',
                'Bogotá, Colombia',
                'Lima, Perú',
                'Santiago, Chile',
                'Caracas, Venezuela',
                'La Habana, Cuba',
                'San José, Costa Rica'
            ]),
        ];
    }
}

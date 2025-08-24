<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ci' => $this->faker->unique()->numerify('###########'),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'teaching_category' => $this->faker->randomElement([
                'Profesor Titular',
                'Profesor Auxiliar',
                'Profesor Asistente',
                'Profesor Principal',
                'ninguna'
            ]),
            'scientific_category' => $this->faker->randomElement([
                'Doctor en Ciencias',
                'Titular',
                'Auxiliar',
                'Agregado',
                'ninguna'
            ]),
            'professional_level' => $this->faker->randomElement([
                'Doctor en Ciencias',
                'Master',
                'Licenciado',
                'Ingeniero',
                'ninguna'
            ]),
            'department_id' => \App\Models\Department::factory(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}

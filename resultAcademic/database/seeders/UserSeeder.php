<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @var array
     */

     protected array $users = [
        [
            'ci' => '12345678999',
            'name' => 'Administrador',
            'email' => 'admin@example.com',
            'teaching_category' => 'ninguna',
            'scientific_category' => 'ninguna',
            'professional_level' => 'ninguna',
            'role' => 'admin',
        ],
        [
            'ci' => '12345678990',
            'name' => 'Directivo',
            'email' => 'directivo@example.com',
            'teaching_category' => 'ninguna',
            'scientific_category' => 'Titular',
            'professional_level' => 'Doctor en Ciencias',
            'role' => 'directive',
        ],
        [
            'ci' => '22222222222',
            'name' => 'Jefe de Dep1',
            'email' => 'jefeD1@example.com',
            'teaching_category' => 'Profesor Principal',
            'scientific_category' => 'Titular',
            'professional_level' => 'Master',
            'role' => 'head_dp',
            
        ],
        [
            'ci' => '1010111122',
            'name' => 'Jefe de Dep2',
            'email' => 'jefeD2@example.com',
            'teaching_category' => 'Profesor Principal',
            'scientific_category' => 'Titular',
            'professional_level' => 'Master',
            'role' => 'head_dp',
        ],
        [
            'ci' => '2121214345',
            'name' => 'Prof1',
            'email' => 'prof1@example.com',
            'teaching_category' => 'Profesor Principal',
            'scientific_category' => 'Titular',
            'professional_level' => 'Master',
            'role' => 'profesor',
        ],
        [
            'ci' => '32323232324',
            'name' => 'Prof2',
            'email' => 'prof2@example.com',
            'teaching_category' => 'Profesor Principal',
            'scientific_category' => 'Titular',
            'professional_level' => 'Master',
            'role' => 'profesor',
        ],
    ];

    public function run(): void
    {
        // Los roles y permisos se crean en RolesAndPermissionsSeeder.
        // Aquí solo creamos/actualizamos usuarios y les asignamos el rol por nombre.
        foreach ($this->users as $userData) {
            $roleName = $userData['role'];
            unset($userData['role']);

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'password' => Hash::make('password'),
                ])
            );

            $user->syncRoles([$roleName]);
        }
    }
}

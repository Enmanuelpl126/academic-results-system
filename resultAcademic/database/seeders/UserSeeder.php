<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
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
        ],
        [
            'ci' => '12345678990',
            'name' => 'Directivo',
            'email' => 'directivo@example.com',
            'teaching_category' => 'ninguna',
            'scientific_category' => 'Titular',
            'professional_level' => 'Doctor en Ciencias',
        ],
        [
            'ci' => '22222222222',
            'name' => 'Jefe de Dep1',
            'email' => 'jefeD1@example.com',
            'teaching_category' => 'Profesor Principal',
            'scientific_category' => 'Titular',
            'professional_level' => 'Master',
        ],
        [
            'ci' => '1010111122',
            'name' => 'Jefe de Dep2',
            'email' => 'jefeD2@example.com',
            'teaching_category' => 'Profesor Principal',
            'scientific_category' => 'Titular',
            'professional_level' => 'Master',
        ],
        [
            'ci' => '2121214345',
            'name' => 'Prof1',
            'email' => 'prof1@example.com',
            'teaching_category' => 'Profesor Principal',
            'scientific_category' => 'Titular',
            'professional_level' => 'Master',
        ],
        [
            'ci' => '32323232324',
            'name' => 'Prof2',
            'email' => 'prof2@example.com',
            'teaching_category' => 'Profesor Principal',
            'scientific_category' => 'Titular',
            'professional_level' => 'Master',
        ],
        
    ];

    public function run(): void

    {
        foreach ($this->users as $userData) {
            User::create(array_merge($userData, [
                'password' => Hash::make('password')
            ]));
        }

        $this->users[0]->assignRole('admin');
        $this->users[1]->assignRole('directive');
        $this->users[2]->assignRole('head_dp');
        $this->users[3]->assignRole('profesor');
        $this->users[4]->assignRole('profesor');
        $this->users[5]->assignRole('profesor');
        

       
       $admin = Role::create(['name' => 'admin']);
       $directive = Role::create(['name' => 'directive']);
       $head_dp = Role::create(['name' => 'head_dp']);
       $profesor = Role::create(['name' => 'profesor']);

       Permission::create(['name' => 'edit results'])->syncRoles([$admin, $directive, $head_dp]);
       Permission::create(['name' => 'delete results'])->syncRoles($admin);
       Permission::create(['name' => 'create results'])->syncRoles([$admin, $directive, $head_dp, $profesor]);

    }
}

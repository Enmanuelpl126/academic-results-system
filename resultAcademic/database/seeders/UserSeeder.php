<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $admin = Role::create(['name' => 'admin']);
       $directive = Role::create(['name' => 'directive']);
       $head_dp = Role::create(['name' => 'head_dp']);
       $profesor = Role::create(['name' => 'profesor']);

       Permission::create(['name' => 'edit results'])->syncRoles([$admin, $directive, $head_dp]);
       Permission::create(['name' => 'delete results'])->syncRoles([$admin, $directive]);
       Permission::create(['name' => 'create results'])->syncRoles([$admin, $directive, $head_dp, $profesor]);

    }
}

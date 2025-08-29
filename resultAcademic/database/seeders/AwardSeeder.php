<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Award;
use \App\Models\User;

class AwardSeeder extends Seeder
{
    /**
     * Run the database seeds.
    */

    public function run(): void
    {
        
        $awards = Award::factory(10)->create();
        
        
        foreach ($awards as $award) {
            $users = User::inRandomOrder()->take(rand(1, 2))->get();
            $award->users()->attach($users);
        }
    }
}

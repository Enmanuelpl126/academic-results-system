<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Award;

class AwardSeeder extends Seeder
{
  
  
    public function run(): void
    {
        
        $awards = Award::factory(10)->create();
        
        
        foreach ($awards as $award) {
            $users = \App\Models\User::inRandomOrder()->take(rand(1, 2))->get();
            $award->users()->attach($users);
        }
    }
}

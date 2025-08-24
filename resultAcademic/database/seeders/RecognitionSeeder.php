<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Recognition;

class RecognitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $recognitions = Recognition::factory(10)->create();
        
       
        foreach ($recognitions as $recognition) {
            $users = \App\Models\User::inRandomOrder()->take(rand(1, 3))->get();
            $recognition->users()->attach($users);
        }
    }
}

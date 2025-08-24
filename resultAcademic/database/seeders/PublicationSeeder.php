<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Publication;

class PublicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       
        $publications = Publication::factory(10)->create();
        
       
        foreach ($publications as $publication) {
            $type = rand(1, 3);
            
            switch ($type) {
                case 1:
                    \App\Models\Magazine::factory()->create(['id' => $publication->id]);
                    break;
                case 2:
                    \App\Models\Book::factory()->create(['id' => $publication->id]);
                    break;
                case 3:
                    \App\Models\Chapter::factory()->create(['id' => $publication->id]);
                    break;
            }
            
           
            $users = \App\Models\User::inRandomOrder()->take(rand(1, 3))->get();
            $publication->users()->attach($users);
        }
    }
}

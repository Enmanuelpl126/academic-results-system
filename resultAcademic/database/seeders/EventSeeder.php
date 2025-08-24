<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $events = Event::factory(10)->create();
        
        
        foreach ($events as $event) {
            $users = \App\Models\User::inRandomOrder()->take(rand(2, 5))->get();
            $event->users()->attach($users);
        }
    }
}

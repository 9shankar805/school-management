<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\SchoolSession;

class EventSeeder extends Seeder
{
    public function run()
    {
        $session = SchoolSession::first();
        if ($session) {
            Event::firstOrCreate([
                'title' => 'Annual Sports Day',
                'session_id' => $session->id,
            ], [
                'start' => now()->addDays(15),
                'end' => now()->addDays(15)->addHours(8),
            ]);
            
            Event::firstOrCreate([
                'title' => 'Science Fair',
                'session_id' => $session->id,
            ], [
                'start' => now()->addMonths(1),
                'end' => now()->addMonths(1)->addHours(5),
            ]);
        }
    }
}

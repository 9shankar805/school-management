<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notice;
use App\Models\SchoolSession;

class NoticeSeeder extends Seeder
{
    public function run()
    {
        $session = SchoolSession::first();
        if ($session) {
            Notice::firstOrCreate([
                'notice' => 'Welcome to the new academic year 2026-2027! Please ensure all registrations are complete.',
                'session_id' => $session->id,
            ]);
            
            Notice::firstOrCreate([
                'notice' => 'School will be closed on Friday for maintenance.',
                'session_id' => $session->id,
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolSession;

class SchoolSessionSeeder extends Seeder
{
    public function run()
    {
        SchoolSession::firstOrCreate([
            'session_name' => '2025-2026'
        ]);
        SchoolSession::firstOrCreate([
            'session_name' => '2026-2027'
        ]);
    }
}

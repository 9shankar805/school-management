<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolSession;
use App\Models\SchoolClass;

class SchoolClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sessions = SchoolSession::all();
        $classes = ['Nursery'];
        for ($i = 1; $i <= 12; $i++) {
            $classes[] = 'Class ' . $i;
        }

        foreach ($sessions as $session) {
            foreach ($classes as $className) {
                SchoolClass::firstOrCreate([
                    'class_name' => $className,
                    'session_id' => $session->id,
                ]);
            }
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Section;
use App\Models\SchoolClass;
use App\Models\SchoolSession;

class SectionSeeder extends Seeder
{
    public function run()
    {
        $sessions = SchoolSession::all();
        $sections = ['A', 'B', 'C'];

        foreach ($sessions as $session) {
            $classes = SchoolClass::where('session_id', $session->id)->get();
            foreach ($classes as $class) {
                foreach ($sections as $index => $sectionName) {
                    Section::firstOrCreate([
                        'section_name' => $sectionName,
                        'class_id' => $class->id,
                        'session_id' => $session->id,
                    ], [
                        'room_no' => 'Room ' . (100 + $class->id + $index),
                    ]);
                }
            }
        }
    }
}

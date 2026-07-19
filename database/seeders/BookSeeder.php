<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Book::create([
            'title' => 'The Great Gatsby',
            'author' => 'F. Scott Fitzgerald',
            'publisher' => 'Scribner',
            'isbn' => '978-0743273565',
            'qty' => 5
        ]);
        \App\Models\Book::create([
            'title' => 'To Kill a Mockingbird',
            'author' => 'Harper Lee',
            'publisher' => 'J. B. Lippincott & Co.',
            'isbn' => '978-0060935467',
            'qty' => 3
        ]);
    }
}

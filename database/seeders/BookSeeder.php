<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fiction = \App\Models\Category::where('name', 'Fiction')->first();
        $science = \App\Models\Category::where('name', 'Science & Technology')->first();
        $history = \App\Models\Category::where('name', 'History & Geography')->first();

        \App\Models\Book::create([
            'title' => 'Madol Doova',
            'author' => 'Martin Wickramasinghe',
            'isbn' => '9789551234567',
            'category_id' => $fiction->id,
            'language' => 'Sinhala',
            'total_quantity' => 10,
            'available_quantity' => 10,
            'location_rack' => 'F-01'
        ]);

        \App\Models\Book::create([
            'title' => 'A Brief History of Time',
            'author' => 'Stephen Hawking',
            'isbn' => '9780553380163',
            'category_id' => $science->id,
            'language' => 'English',
            'total_quantity' => 5,
            'available_quantity' => 5,
            'location_rack' => 'S-05'
        ]);

        \App\Models\Book::create([
            'title' => 'Mahavamsa',
            'author' => 'Mahanama Thera',
            'isbn' => '9789559523451',
            'category_id' => $history->id,
            'language' => 'Pali/Sinhala/English',
            'total_quantity' => 3,
            'available_quantity' => 3,
            'location_rack' => 'H-01'
        ]);

        // Generate 20 random books
        $categories = \App\Models\Category::all();
        \App\Models\Book::factory(20)->make()->each(function ($book) use ($categories) {
            $book->category_id = $categories->random()->id;
            $book->save();
        });
    }
}

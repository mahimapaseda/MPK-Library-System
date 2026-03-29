<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add 20 more members
        \App\Models\Member::factory(20)->create();

        // Add 20 more books, assigning to existing random categories
        $categories = \App\Models\Category::all();
        if ($categories->isEmpty()) {
            $categories = \App\Models\Category::factory(5)->create();
        }

        \App\Models\Book::factory(20)->make()->each(function ($book) use ($categories) {
            $book->category_id = $categories->random()->id;
            $book->save();
        });
    }
}

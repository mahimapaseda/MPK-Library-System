<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'General Works', 'description' => 'Encyclopedias, Bibliographies'],
            ['name' => 'Fiction', 'description' => 'Novels, Short Stories'],
            ['name' => 'Science & Technology', 'description' => 'Mathematics, Physics, Engineering'],
            ['name' => 'History & Geography', 'description' => 'World History, Sri Lankan History'],
            ['name' => 'Literature', 'description' => 'Poetry, Drama, Classics'],
            ['name' => 'Religion', 'description' => 'Buddhism, Hinduism, Islam, Christianity'],
        ];

        foreach ($categories as $category) {
            \App\Models\Category::create($category);
        }
    }
}

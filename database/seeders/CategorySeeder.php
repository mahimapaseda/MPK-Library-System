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
            ['name' => 'General Works', 'description' => 'Encyclopedias, bibliographies, and general reference materials'],
            ['name' => 'Fiction', 'description' => 'Novels, short stories, fantasy, and literary fiction'],
            ['name' => 'Science & Technology', 'description' => 'Mathematics, physics, chemistry, biology, and engineering'],
            ['name' => 'History & Geography', 'description' => 'World history, geography, maps, and cultural studies'],
            ['name' => 'Literature', 'description' => 'Poetry, drama, literary criticism, and classics'],
            ['name' => 'Religion & Philosophy', 'description' => 'Buddhism, Hinduism, Islam, Christianity, and philosophical works'],
            ['name' => 'Art & Design', 'description' => 'Visual arts, architecture, design, and craftsmanship'],
            ['name' => 'Music & Performing Arts', 'description' => 'Music history, dance, theater, and cinema'],
            ['name' => 'Sports & Recreation', 'description' => 'Sports, games, fitness, and outdoor activities'],
            ['name' => 'Biography & Memoir', 'description' => 'Biographies, autobiographies, and personal narratives'],
            ['name' => 'Self-Help & Psychology', 'description' => 'Personal development, psychology, and behavioral sciences'],
            ['name' => 'Business & Economics', 'description' => 'Management, economics, finance, and entrepreneurship'],
            ['name' => 'Education & Career', 'description' => 'Educational resources, career guides, and academic texts'],
            ['name' => 'Travel & Tourism', 'description' => 'Travel guides, destination information, and cultural exploration'],
            ['name' => 'Food & Cooking', 'description' => 'Recipes, cooking techniques, and food culture'],
            ['name' => 'Health & Medicine', 'description' => 'Medical references, health care, and wellness topics'],
            ['name' => 'Nature & Environment', 'description' => 'Ecology, conservation, wildlife, and environmental science'],
            ['name' => 'Technology & Computer Science', 'description' => 'Programming, software, IT, and digital technologies'],
            ['name' => 'Social Sciences', 'description' => 'Sociology, anthropology, politics, and social studies'],
            ['name' => 'Language & Linguistics', 'description' => 'Language learning, grammar, dictionaries, and linguistics'],
        ];

        foreach ($categories as $category) {
            \App\Models\Category::create($category);
        }
    }
}

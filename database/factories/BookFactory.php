<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $total = fake()->numberBetween(1, 10);
        return [
            'title' => fake()->sentence(3),
            'author' => fake()->name(),
            'isbn' => fake()->unique()->isbn13(),
            'category_id' => Category::factory(),
            'language' => fake()->randomElement(['English', 'Sinhala', 'Tamil']),
            'total_quantity' => $total,
            'available_quantity' => $total,
            'location_rack' => 'Rack ' . fake()->randomElement(['A', 'B', 'C', 'D']) . fake()->numberBetween(1, 10),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Category;
use App\Services\BookInventoryService;
use Illuminate\Database\Eloquent\Factories\Factory;

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

    public function configure(): static
    {
        return $this->afterCreating(function ($book) {
            (new BookInventoryService())->provisionCopies($book, (int) $book->total_quantity);
        });
    }
}

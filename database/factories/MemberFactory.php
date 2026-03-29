<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
class MemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $regId = 'MEM' . fake()->unique()->numberBetween(1000, 9999);
        return [
            'member_id' => $regId,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password123'), // Default password for testing
            'type' => fake()->randomElement(['student', 'teacher', 'staff']),
            'grade' => fake()->randomElement(['Grade 10', 'Grade 11', 'Grade 12', 'Grade 13', 'Faculty']),
            'contact_number' => '+94' . fake()->numerify('#########'),
        ];
    }
}

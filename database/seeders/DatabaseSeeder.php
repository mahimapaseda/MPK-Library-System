<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Librarian (Admin)',
            'email' => 'admin@school.lk',
            'password' => bcrypt('password'),
            'role' => 'librarian',
        ]);

        $this->call([
            CategorySeeder::class,
            BookSeeder::class,
            MemberSeeder::class,
            SettingSeeder::class,
        ]);
    }
}

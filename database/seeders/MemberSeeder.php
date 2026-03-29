<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Member::create([
            'member_id' => 'ST-2024-001',
            'name' => 'Kamal Perera',
            'type' => 'student',
            'grade' => '10-A',
            'contact_number' => '0712345678'
        ]);

        \App\Models\Member::create([
            'member_id' => 'TR-2024-001',
            'name' => 'Mrs. Sunethra Silva',
            'type' => 'teacher',
            'grade' => 'Staff',
            'contact_number' => '0778765432'
        ]);

        // Generate 20 random members
        \App\Models\Member::factory(20)->create();
    }
}

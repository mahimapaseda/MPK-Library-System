<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'fine_per_day', 'value' => '5'],
            ['key' => 'loan_duration_days', 'value' => '14'],
            ['key' => 'max_books_per_member', 'value' => '3'],
            ['key' => 'library_name', 'value' => 'School Library'],
        ];

        foreach ($settings as $setting) {
            \App\Models\Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}

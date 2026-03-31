<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BookIssueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = \App\Models\Book::all();
        $members = \App\Models\Member::all();

        if ($books->isEmpty() || $members->isEmpty()) {
            return; // Skip if no books or members
        }

        // Create 20 sample issues with various statuses
        $statuses = ['issued', 'returned', 'overdue', 'lost'];
        $now = Carbon::now();

        for ($i = 0; $i < 20; $i++) {
            $book = $books->random();
            $member = $members->random();
            $status = $statuses[array_rand($statuses)];

            $issued_at = $now->copy()->subDays(rand(0, 60));
            $daysLoan = rand(7, 21);
            $due_date = $issued_at->copy()->addDays($daysLoan);

            // Determine issued_at date based on status
            if ($status === 'issued') {
                $issued_at = $now->copy()->subDays(rand(1, 7));
                $due_date = $issued_at->copy()->addDays($daysLoan);
            } elseif ($status === 'overdue') {
                $issued_at = $now->copy()->subDays(rand(20, 45));
                $due_date = $issued_at->copy()->addDays(rand(7, 14));
            } elseif ($status === 'returned') {
                $issued_at = $now->copy()->subDays(rand(5, 50));
                $due_date = $issued_at->copy()->addDays(rand(7, 21));
            } elseif ($status === 'lost') {
                $issued_at = $now->copy()->subDays(rand(50, 120));
                $due_date = $issued_at->copy()->addDays(14);
            }

            \App\Models\BookIssue::create([
                'book_id' => $book->id,
                'member_id' => $member->id,
                'issued_at' => $issued_at,
                'due_date' => $due_date,
                'returned_at' => in_array($status, ['returned', 'lost']) ? $now->copy()->subDays(rand(0, 10)) : null,
                'status' => $status,
            ]);

            // Update book available quantity
            if ($status === 'issued' || $status === 'overdue') {
                $book->decrement('available_quantity');
            }
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\BookIssue;
use App\Models\Fine;
use App\Services\BookInventoryService;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookIssueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = \App\Models\Book::all();
        $members = \App\Models\Member::all();
        $inventoryService = new BookInventoryService();

        if ($books->isEmpty() || $members->isEmpty()) {
            return;
        }

        $statuses = ['issued', 'returned', 'overdue', 'lost', 'damaged'];
        $now = Carbon::now();

        for ($i = 0; $i < 20; $i++) {
            $book = $books->random();
            if ($book->available_quantity <= 0) {
                continue;
            }

            $member = $members->random();
            $status = $statuses[array_rand($statuses)];
            $issuedAt = $now->copy()->subDays(rand(3, 60));
            $dueDate = $issuedAt->copy()->addDays(rand(7, 21));
            $copy = $inventoryService->reserveAvailableCopy($book);

            if (! $copy) {
                continue;
            }

            $issue = BookIssue::query()->create([
                'book_id' => $book->id,
                'book_copy_id' => $copy->id,
                'member_id' => $member->id,
                'issued_at' => $issuedAt,
                'due_date' => $dueDate,
                'status' => in_array($status, ['issued', 'overdue'], true) ? $status : 'issued',
            ]);

            if ($status === 'returned') {
                $issue->update([
                    'status' => 'returned',
                    'returned_at' => $now->copy()->subDays(rand(0, 10)),
                ]);
                $inventoryService->markCopyAvailable($copy);
                continue;
            }

            if (in_array($status, ['lost', 'damaged'], true)) {
                $fee = rand(500, 3000);
                $issue->update([
                    'status' => $status,
                    'returned_at' => $status === 'damaged' ? $now->copy()->subDays(rand(0, 10)) : null,
                    'resolved_at' => $now->copy()->subDays(rand(0, 10)),
                    'condition_notes' => fake()->sentence(),
                    'condition_fee' => $fee,
                ]);
                $inventoryService->markCopyIncident($copy, $status);
                Fine::query()->create([
                    'book_issue_id' => $issue->id,
                    'member_id' => $member->id,
                    'amount' => $fee,
                    'status' => 'unpaid',
                ]);
                continue;
            }
        }
    }
}

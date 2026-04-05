<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookIssue;
use App\Models\Fine;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FineAdministrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_librarian_can_mark_a_charge_as_paid(): void
    {
        $user = User::factory()->create(['role' => 'librarian']);
        $member = Member::factory()->create();
        $book = Book::factory()->create();
        $issue = BookIssue::query()->create([
            'book_id' => $book->id,
            'book_copy_id' => $book->copies()->first()->id,
            'member_id' => $member->id,
            'issued_at' => now()->subDays(5),
            'due_date' => now()->subDay(),
            'status' => 'returned',
            'returned_at' => now(),
        ]);
        $fine = Fine::query()->create([
            'book_issue_id' => $issue->id,
            'member_id' => $member->id,
            'amount' => 750,
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($user)->post(route('fines.resolve', $fine), [
            'status' => 'paid',
            'resolution_notes' => 'Cash receipt #1001',
        ]);

        $response->assertRedirect();

        $fine->refresh();
        $this->assertSame('paid', $fine->status);
        $this->assertNotNull($fine->paid_at);
        $this->assertNull($fine->waived_at);
        $this->assertSame($user->id, $fine->resolved_by_user_id);
    }

    public function test_librarian_can_mark_a_charge_as_waived(): void
    {
        $user = User::factory()->create(['role' => 'librarian']);
        $member = Member::factory()->create();
        $book = Book::factory()->create();
        $issue = BookIssue::query()->create([
            'book_id' => $book->id,
            'book_copy_id' => $book->copies()->first()->id,
            'member_id' => $member->id,
            'issued_at' => now()->subDays(5),
            'due_date' => now()->subDay(),
            'status' => 'lost',
            'resolved_at' => now(),
        ]);
        $fine = Fine::query()->create([
            'book_issue_id' => $issue->id,
            'member_id' => $member->id,
            'amount' => 1250,
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($user)->post(route('fines.resolve', $fine), [
            'status' => 'waived',
            'resolution_notes' => 'Approved by principal',
        ]);

        $response->assertRedirect();

        $fine->refresh();
        $this->assertSame('waived', $fine->status);
        $this->assertNotNull($fine->waived_at);
        $this->assertNull($fine->paid_at);
        $this->assertSame($user->id, $fine->resolved_by_user_id);
    }

    public function test_incidents_report_is_available_to_librarian(): void
    {
        $user = User::factory()->create(['role' => 'librarian']);
        $member = Member::factory()->create();
        $book = Book::factory()->create();
        $copy = $book->copies()->first();
        $copy->update(['status' => 'lost']);
        $issue = BookIssue::query()->create([
            'book_id' => $book->id,
            'book_copy_id' => $copy->id,
            'member_id' => $member->id,
            'issued_at' => now()->subDays(7),
            'due_date' => now()->subDays(2),
            'status' => 'lost',
            'resolved_at' => now(),
            'condition_notes' => 'Missing after follow-up',
        ]);
        Fine::query()->create([
            'book_issue_id' => $issue->id,
            'member_id' => $member->id,
            'amount' => 900,
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($user)->get(route('reports.incidents'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}

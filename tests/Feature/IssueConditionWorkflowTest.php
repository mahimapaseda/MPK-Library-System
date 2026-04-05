<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookIssue;
use App\Models\Fine;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IssueConditionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_librarian_can_mark_an_issue_as_lost_and_create_a_charge(): void
    {
        $user = User::factory()->create(['role' => 'librarian']);
        $member = Member::factory()->create();
        $book = Book::factory()->create(['total_quantity' => 5, 'available_quantity' => 5]);

        $this->actingAs($user)->post(route('issues.store'), [
            'book_id' => $book->id,
            'member_id' => $member->id,
            'due_date' => now()->addDays(7)->toDateString(),
        ])->assertRedirect();

        $issue = BookIssue::query()->latest()->first();

        $response = $this->actingAs($user)->post(route('issues.condition', $issue), [
            'status' => 'lost',
            'notes' => 'Member reported the book missing during follow-up.',
            'fine_amount' => 1500,
        ]);

        $response->assertRedirect();

        $issue->refresh();
        $book->refresh();

        $this->assertSame('lost', $issue->status);
        $this->assertNull($issue->returned_at);
        $this->assertNotNull($issue->resolved_at);
        $this->assertSame('1500.00', $issue->condition_fee);
        $this->assertSame(4, $book->total_quantity);
        $this->assertSame(4, $book->available_quantity);
        $this->assertSame('lost', $issue->copy->fresh()->status);

        $fine = Fine::query()->where('book_issue_id', $issue->id)->first();
        $this->assertNotNull($fine);
        $this->assertSame('1500.00', $fine->amount);
        $this->assertSame('unpaid', $fine->status);
    }

    public function test_librarian_can_mark_an_issue_as_damaged_and_reduce_stock(): void
    {
        $user = User::factory()->create(['role' => 'librarian']);
        $member = Member::factory()->create();
        $book = Book::factory()->create(['total_quantity' => 3, 'available_quantity' => 3]);

        $this->actingAs($user)->post(route('issues.store'), [
            'book_id' => $book->id,
            'member_id' => $member->id,
            'due_date' => now()->addDays(2)->toDateString(),
        ])->assertRedirect();

        $issue = BookIssue::query()->latest()->first();

        $response = $this->actingAs($user)->post(route('issues.condition', $issue), [
            'status' => 'damaged',
            'notes' => 'Water damage confirmed at desk return.',
            'fine_amount' => 500,
        ]);

        $response->assertRedirect();

        $issue->refresh();
        $book->refresh();

        $this->assertSame('damaged', $issue->status);
        $this->assertNotNull($issue->returned_at);
        $this->assertNotNull($issue->resolved_at);
        $this->assertSame(2, $book->total_quantity);
        $this->assertSame(2, $book->available_quantity);
        $this->assertSame('damaged', $issue->copy->fresh()->status);
    }

    public function test_closed_issue_cannot_be_marked_lost_or_damaged_again(): void
    {
        $user = User::factory()->create(['role' => 'librarian']);
        $member = Member::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user)->post(route('issues.store'), [
            'book_id' => $book->id,
            'member_id' => $member->id,
            'due_date' => now()->addDays(3)->toDateString(),
        ])->assertRedirect();

        $issue = BookIssue::query()->latest()->first();
        $this->actingAs($user)->post(route('issues.return', $issue))->assertRedirect();

        $response = $this->actingAs($user)->post(route('issues.condition', $issue->fresh()), [
            'status' => 'lost',
            'notes' => 'Should not be accepted.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertSame('returned', $issue->fresh()->status);
    }
}
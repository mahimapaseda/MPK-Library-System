<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookIssue;
use App\Models\Member;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\DueReminderNotification;
use App\Notifications\IssueReceiptNotification;
use App\Support\SettingCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LibraryNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_issue_receipt_is_sent_when_book_is_issued(): void
    {
        Notification::fake();

        Setting::query()->insert([
            ['key' => 'issue_receipt_enabled', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'library_name', 'value' => 'MPK Library', 'created_at' => now(), 'updated_at' => now()],
        ]);
        SettingCache::forget();

        $user = User::factory()->create([
            'role' => 'librarian',
        ]);
        $member = Member::factory()->create();
        $book = Book::factory()->create([
            'available_quantity' => 3,
            'total_quantity' => 3,
        ]);

        $response = $this->actingAs($user)->post(route('issues.store'), [
            'book_id' => $book->id,
            'member_id' => $member->id,
            'due_date' => now()->addDays(7)->toDateString(),
        ]);

        $response->assertRedirect();

        Notification::assertSentTo($member, IssueReceiptNotification::class);
    }

    public function test_due_reminder_command_sends_due_soon_notification(): void
    {
        Notification::fake();

        Setting::query()->insert([
            ['key' => 'send_due_reminders', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'reminder_days_before_due', 'value' => '2', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'auto_mark_overdue', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
        ]);
        SettingCache::forget();

        $member = Member::factory()->create();
        $book = Book::factory()->create();

        Carbon::setTestNow('2026-04-05 08:00:00');

        BookIssue::query()->create([
            'book_id' => $book->id,
            'member_id' => $member->id,
            'issued_at' => now()->subDays(5),
            'due_date' => now()->addDays(2),
            'status' => 'issued',
        ]);

        $this->artisan('library:send-notifications')
            ->expectsOutput('Library notifications processed.')
            ->assertExitCode(0);

        Notification::assertSentTo(
            $member,
            DueReminderNotification::class,
            fn (DueReminderNotification $notification) => str_contains($notification->toMail($member)->subject, 'Due Reminder')
        );

        Carbon::setTestNow();
    }

    public function test_due_reminder_command_marks_items_overdue_and_sends_alert(): void
    {
        Notification::fake();

        Setting::query()->insert([
            ['key' => 'send_due_reminders', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'reminder_days_before_due', 'value' => '2', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'auto_mark_overdue', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
        ]);
        SettingCache::forget();

        $member = Member::factory()->create();
        $book = Book::factory()->create();

        Carbon::setTestNow('2026-04-05 08:00:00');

        $issue = BookIssue::query()->create([
            'book_id' => $book->id,
            'member_id' => $member->id,
            'issued_at' => now()->subDays(10),
            'due_date' => now()->subDay(),
            'status' => 'issued',
        ]);

        $this->artisan('library:send-notifications')->assertExitCode(0);

        $this->assertSame('overdue', $issue->fresh()->status);
        Notification::assertSentTo(
            $member,
            DueReminderNotification::class,
            fn (DueReminderNotification $notification) => str_contains($notification->toMail($member)->subject, 'Overdue Notice')
        );

        Carbon::setTestNow();
    }
}
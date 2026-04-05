<?php

namespace App\Services;

use App\Models\BookIssue;
use App\Notifications\DueReminderNotification;
use App\Notifications\IssueReceiptNotification;
use App\Notifications\ReturnReceiptNotification;
use App\Support\SettingCache;

class LibraryNotificationService
{
    public function sendIssueReceipt(BookIssue $issue): void
    {
        if (! $this->shouldSendIssueReceipts() || ! $this->canNotify($issue)) {
            return;
        }

        $issue->member->notify(new IssueReceiptNotification($issue));
    }

    public function sendReturnReceipt(BookIssue $issue): void
    {
        if (! $this->shouldSendIssueReceipts() || ! $this->canNotify($issue)) {
            return;
        }

        $issue->member->notify(new ReturnReceiptNotification($issue));
    }

    public function sendDailyReminders(): array
    {
        if (! $this->shouldSendDueReminders()) {
            return [
                'due_reminders_sent' => 0,
                'overdue_alerts_sent' => 0,
                'issues_marked_overdue' => 0,
            ];
        }

        $reminderDays = max(0, (int) SettingCache::get('reminder_days_before_due', 2));
        $today = now()->startOfDay();
        $targetDueDate = $today->copy()->addDays($reminderDays);
        $dueReminderCount = 0;
        $overdueAlertCount = 0;
        $markedOverdueCount = 0;

        $dueSoonIssues = BookIssue::query()
            ->with(['book', 'member'])
            ->where('status', 'issued')
            ->whereNull('returned_at')
            ->whereDate('due_date', $targetDueDate)
            ->get();

        $dueSoonIssues
            ->filter(fn (BookIssue $issue) => $this->canNotify($issue))
            ->each(function (BookIssue $issue) use (&$dueReminderCount): void {
                $issue->member->notify(new DueReminderNotification($issue));
                $dueReminderCount++;
            });

        $overdueIssues = BookIssue::query()
            ->with(['book', 'member'])
            ->where('status', 'issued')
            ->whereNull('returned_at')
            ->whereDate('due_date', '<', $today)
            ->get();

        $autoMarkOverdue = $this->shouldAutoMarkOverdue();

        $overdueIssues
            ->filter(fn (BookIssue $issue) => $this->canNotify($issue))
            ->each(function (BookIssue $issue) use (&$overdueAlertCount, &$markedOverdueCount, $autoMarkOverdue): void {
                if ($autoMarkOverdue) {
                    $issue->forceFill(['status' => 'overdue'])->save();
                    $markedOverdueCount++;
                }

                $issue->member->notify(new DueReminderNotification($issue, true));
                $overdueAlertCount++;
            });

        return [
            'due_reminders_sent' => $dueReminderCount,
            'overdue_alerts_sent' => $overdueAlertCount,
            'issues_marked_overdue' => $markedOverdueCount,
        ];
    }

    private function shouldSendIssueReceipts(): bool
    {
        return SettingCache::get('issue_receipt_enabled', '1') === '1';
    }

    private function shouldSendDueReminders(): bool
    {
        return SettingCache::get('send_due_reminders', '1') === '1';
    }

    private function shouldAutoMarkOverdue(): bool
    {
        return SettingCache::get('auto_mark_overdue', '1') === '1';
    }

    private function canNotify(BookIssue $issue): bool
    {
        return filled($issue->member?->email);
    }
}
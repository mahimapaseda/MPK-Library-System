<?php

namespace App\Notifications;

use App\Models\BookIssue;
use App\Support\SettingCache;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DueReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly BookIssue $issue,
        private readonly bool $overdue = false,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $libraryName = SettingCache::get('library_name', config('app.name'));
        $currency = SettingCache::get('currency_symbol', 'LKR');
        $dueDate = optional($this->issue->due_date)->format('M d, Y');
        $subject = $this->overdue
            ? "Overdue Notice: {$this->issue->book->title}"
            : "Due Reminder: {$this->issue->book->title}";

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name},")
            ->line("This is a reminder from {$libraryName} for {$this->issue->book->title}.")
            ->line("Due date: {$dueDate}");

        if ($this->overdue) {
            $graceDays = (int) SettingCache::get('grace_period_days', 0);
            $finePerDay = (float) SettingCache::get('fine_per_day', 5);
            $overdueDays = now()->startOfDay()->diffInDays($this->issue->due_date->copy()->startOfDay());
            $chargeableDays = max(0, $overdueDays - $graceDays);
            $estimatedFine = $chargeableDays * $finePerDay;

            $message->line('This item is now overdue. Please return it as soon as possible.');

            if ($estimatedFine > 0) {
                $message->line("Estimated fine so far: {$currency} " . number_format($estimatedFine, 2));
            }
        } else {
            $daysRemaining = max(0, now()->startOfDay()->diffInDays($this->issue->due_date->copy()->startOfDay(), false));
            $message->line("Days remaining: {$daysRemaining}")
                ->line('Please return or renew the book before the due date.');
        }

        return $message;
    }
}
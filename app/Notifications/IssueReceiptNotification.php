<?php

namespace App\Notifications;

use App\Models\BookIssue;
use App\Support\SettingCache;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IssueReceiptNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly BookIssue $issue)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $libraryName = SettingCache::get('library_name', config('app.name'));
        $dueDate = optional($this->issue->due_date)->format('M d, Y');

        return (new MailMessage)
            ->subject("Book Issued: {$this->issue->book->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->issue->book->title} has been issued to your {$libraryName} account.")
            ->line("Issue date: {$this->issue->issued_at?->format('M d, Y')}")
            ->line("Due date: {$dueDate}")
            ->line('Please return or renew the book before the due date to avoid overdue fines.');
    }
}
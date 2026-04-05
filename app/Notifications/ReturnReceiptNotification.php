<?php

namespace App\Notifications;

use App\Models\BookIssue;
use App\Support\SettingCache;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReturnReceiptNotification extends Notification
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

        return (new MailMessage)
            ->subject("Book Returned: {$this->issue->book->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->issue->book->title} has been marked as returned in {$libraryName}.")
            ->line("Return date: {$this->issue->returned_at?->format('M d, Y')}")
            ->line('Thank you for returning your book.');
    }
}
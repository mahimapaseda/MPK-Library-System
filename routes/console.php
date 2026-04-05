<?php

use App\Services\LibraryNotificationService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('library:send-notifications', function (LibraryNotificationService $notificationService) {
    $results = $notificationService->sendDailyReminders();

    $this->info('Library notifications processed.');
    $this->line('Due reminders sent: ' . $results['due_reminders_sent']);
    $this->line('Overdue alerts sent: ' . $results['overdue_alerts_sent']);
    $this->line('Issues marked overdue: ' . $results['issues_marked_overdue']);
})->purpose('Send scheduled library due reminders and overdue alerts');

Schedule::command('library:send-notifications')->dailyAt('08:00');

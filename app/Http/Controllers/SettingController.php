<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\SettingCache;
use Inertia\Inertia;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/Index', [
            'settings' => Setting::all()->pluck('value', 'key')
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'settings' => 'required|array',
            'settings.library_email' => 'nullable|email',
            'settings.fine_per_day' => 'nullable|numeric|min:0',
            'settings.grace_period_days' => 'nullable|integer|min:0',
            'settings.loan_duration_days' => 'nullable|integer|min:1',
            'settings.max_books_per_member' => 'nullable|integer|min:1',
            'settings.reservation_expiry_days' => 'nullable|integer|min:0',
            'settings.reminder_days_before_due' => 'nullable|integer|min:0',
            'settings.max_renewals_per_issue' => 'nullable|integer|min:0',
            'settings.max_reservations_per_member' => 'nullable|integer|min:0',
            'settings.low_stock_threshold' => 'nullable|integer|min:0',
            'settings.overdue_lock_days' => 'nullable|integer|min:0',
            'settings.working_days_per_week' => 'nullable|integer|min:1|max:7',
            'settings.send_due_reminders' => 'nullable|in:0,1',
            'settings.auto_mark_overdue' => 'nullable|in:0,1',
            'settings.allow_member_renewal' => 'nullable|in:0,1',
            'settings.issue_receipt_enabled' => 'nullable|in:0,1',
            'settings.allow_registration' => 'nullable|in:0,1',
            'settings.maintenance_mode' => 'nullable|in:0,1',
        ]);

        foreach ($data['settings'] as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        SettingCache::forget();

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}

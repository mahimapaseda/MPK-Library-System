<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BookIssue;
use App\Models\Fine;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class MemberPortalController extends Controller
{
    public function dashboard()
    {
        $member = Auth::guard('member')->user();

        return Inertia::render('Member/Dashboard', [
            'stats' => [
                'current_loans' => BookIssue::where('member_id', $member->id)->where('status', 'issued')->count(),
                'total_borrowed' => BookIssue::where('member_id', $member->id)->count(),
                'unpaid_fines' => Fine::where('member_id', $member->id)->where('status', 'unpaid')->sum('amount'),
            ],
            'recent_loans' => BookIssue::with('book')
                ->where('member_id', $member->id)
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }

    public function history()
    {
        $member = Auth::guard('member')->user();
        $history = BookIssue::with('book')
            ->where('member_id', $member->id)
            ->latest()
            ->paginate(10);

        return Inertia::render('Member/History', [
            'history' => $history
        ]);
    }

    public function fines()
    {
        $member = Auth::guard('member')->user();
        $fines = Fine::with('book_issue.book')
            ->where('member_id', $member->id)
            ->latest()
            ->get();

        return Inertia::render('Member/Fines', [
            'fines' => $fines
        ]);
    }
}

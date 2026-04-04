<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Book;
use App\Models\Member;
use App\Models\BookIssue;
use App\Models\Fine;
use App\Models\Category;
use App\Services\AiInsightsService;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(AiInsightsService $insightsService)
    {
        // Monthly issues for the last 6 months
        $monthly_issues = BookIssue::selectRaw("strftime('%Y-%m', issued_at) as month, count(*) as count")
            ->where('issued_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Category distribution
        $category_distribution = Category::withCount('books')
            ->orderBy('books_count', 'desc')
            ->take(5)
            ->get();

        $stats = Cache::remember('dashboard_stats', 300, function () {
            return [
                'total_books' => Book::count(),
                'total_members' => Member::count(),
                'active_issues' => BookIssue::where('status', 'issued')->count(),
                'pending_fines' => Fine::where('status', 'unpaid')->sum('amount'),
            ];
        });

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'ai_insights' => $insightsService->buildDashboardInsights(),
            'recent_issues' => BookIssue::with(['book', 'member'])
                ->latest()
                ->take(5)
                ->get(),
            'charts' => [
                'monthly' => [
                    'labels' => $monthly_issues->pluck('month')->map(fn($m) => date('M Y', strtotime($m . '-01'))),
                    'data' => $monthly_issues->pluck('count'),
                ],
                'categories' => [
                    'labels' => $category_distribution->pluck('name'),
                    'data' => $category_distribution->pluck('books_count'),
                ]
            ]
        ]);
    }
}

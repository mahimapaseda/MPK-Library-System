<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookIssue;
use App\Models\Category;
use App\Models\Fine;
use App\Services\AiInsightsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        $analytics = Cache::remember('reports.analytics', 300, function () {
            $now = now();
            $days = collect(range(6, 0))->map(fn ($day) => $now->copy()->subDays($day)->format('Y-m-d'));

            $issuedStats = BookIssue::query()
                ->where('created_at', '>=', $now->copy()->subDays(7))
                ->selectRaw('DATE(created_at) as date, count(*) as total')
                ->groupBy('date')
                ->get()
                ->pluck('total', 'date');

            $returnedStats = BookIssue::query()
                ->where('returned_at', '>=', $now->copy()->subDays(7))
                ->selectRaw('DATE(returned_at) as date, count(*) as total')
                ->groupBy('date')
                ->get()
                ->pluck('total', 'date');

            $incidentSummary = [
                'lost' => BookIssue::query()->where('status', 'lost')->count(),
                'damaged' => BookIssue::query()->where('status', 'damaged')->count(),
            ];

            return [
                'trends' => $days->map(fn ($date) => [
                    'date' => $date,
                    'issued' => $issuedStats->get($date, 0),
                    'returned' => $returnedStats->get($date, 0),
                ]),
                'distribution' => Category::withCount('books')->get()->map(fn ($category) => [
                    'name' => $category->name,
                    'count' => $category->books_count,
                ]),
                'revenue' => [
                    'paid' => (float) Fine::where('status', 'paid')->sum('amount'),
                    'pending' => (float) Fine::where('status', 'unpaid')->sum('amount'),
                ],
                'incidents' => $incidentSummary,
            ];
        });

        return Inertia::render('Reports/Index', [
            'analytics' => $analytics,
        ]);
    }

    public function aiStrategy(AiInsightsService $insightsService)
    {
        $insights = $insightsService->buildDashboardInsights();

        return Pdf::loadView('reports.ai_strategy', compact('insights'))
            ->download('ai-strategy-report.pdf');
    }

    public function overdueBooks()
    {
        $overdueIssues = BookIssue::with(['book', 'member', 'copy'])
            ->where(function ($query) {
                $query->where('status', 'overdue')
                    ->orWhere(function ($issuedQuery) {
                        $issuedQuery->where('status', 'issued')
                            ->where('due_date', '<', now());
                    });
            })
            ->get();

        return Pdf::loadView('reports.overdue', compact('overdueIssues'))
            ->download('overdue-books-report.pdf');
    }

    public function inventorySummary()
    {
        $categories = Category::withCount('books')->get();
        $totalBooks = Book::sum('total_quantity');
        $availableBooks = Book::sum('available_quantity');
        $lostCopies = BookIssue::where('status', 'lost')->count();
        $damagedCopies = BookIssue::where('status', 'damaged')->count();

        return Pdf::loadView('reports.inventory', compact('categories', 'totalBooks', 'availableBooks', 'lostCopies', 'damagedCopies'))
            ->download('inventory-summary.pdf');
    }

    public function incidents()
    {
        $incidents = BookIssue::with(['book', 'member', 'copy', 'fine'])
            ->whereIn('status', ['lost', 'damaged'])
            ->latest('resolved_at')
            ->get();

        return Pdf::loadView('reports.incidents', compact('incidents'))
            ->download('incidents-report.pdf');
    }
}

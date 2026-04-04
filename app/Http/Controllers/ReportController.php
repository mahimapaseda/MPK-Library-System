<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookIssue;
use App\Models\Category;
use App\Services\AiInsightsService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        $analytics = Cache::remember('reports.analytics', 300, function () {
            $now = now();
            $days = collect(range(6, 0))->map(fn($d) => $now->copy()->subDays($d)->format('Y-m-d'));

            $issuedStats = BookIssue::where('created_at', '>=', $now->copy()->subDays(7))
                ->selectRaw('DATE(created_at) as date, count(*) as total')
                ->groupBy('date')
                ->get()
                ->pluck('total', 'date');

            $returnedStats = BookIssue::where('returned_at', '>=', $now->copy()->subDays(7))
                ->selectRaw('DATE(returned_at) as date, count(*) as total')
                ->groupBy('date')
                ->get()
                ->pluck('total', 'date');

            $trends = $days->map(fn($date) => [
                'date' => $date,
                'issued' => $issuedStats->get($date, 0),
                'returned' => $returnedStats->get($date, 0),
            ]);

            $distribution = Category::withCount('books')->get()->map(fn($c) => [
                'name' => $c->name,
                'count' => $c->books_count,
            ]);

            $revenue = [
                'paid' => (float) \App\Models\Fine::where('status', 'paid')->sum('amount'),
                'pending' => (float) \App\Models\Fine::where('status', 'unpaid')->sum('amount'),
            ];

            return [
                'trends' => $trends,
                'distribution' => $distribution,
                'revenue' => $revenue,
            ];
        });

        return Inertia::render('Reports/Index', [
            'analytics' => $analytics,
        ]);
    }

    public function aiStrategy(AiInsightsService $insightsService)
    {
        $insights = $insightsService->buildDashboardInsights();
        $pdf = Pdf::loadView('reports.ai_strategy', compact('insights'));
        return $pdf->download('ai-strategy-report.pdf');
    }

    public function overdueBooks()
    {
        $overdueIssues = BookIssue::with(['book', 'member'])
            ->where('status', 'issued')
            ->where('due_date', '<', now())
            ->get();

        $pdf = Pdf::loadView('reports.overdue', compact('overdueIssues'));
        return $pdf->download('overdue-books-report.pdf');
    }

    public function inventorySummary()
    {
        $categories = Category::withCount('books')->get();
        $totalBooks = Book::sum('total_quantity');
        $availableBooks = Book::sum('available_quantity');

        $pdf = Pdf::loadView('reports.inventory', compact('categories', 'totalBooks', 'availableBooks'));
        return $pdf->download('inventory-summary.pdf');
    }
}

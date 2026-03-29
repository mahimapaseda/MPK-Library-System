<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookIssue;
use App\Models\Fine;
use Illuminate\Support\Facades\Cache;

class AiInsightsService
{
    /**
     * Build actionable insights from current library activity.
     *
     * @return array<string, mixed>
     */
    public function buildDashboardInsights(): array
    {
        return Cache::remember('library_insights', 600, function () {
            $now = now();

            $activeIssues = BookIssue::where('status', 'issued')->count();
            $overdueIssues = BookIssue::where('status', 'issued')
                ->whereDate('due_date', '<', $now)
                ->count();

            $overdueRate = $activeIssues > 0 ? round(($overdueIssues / $activeIssues) * 100, 1) : 0.0;

            $highDemandBooks = Book::query()
                ->where('available_quantity', 0)
                ->where('total_quantity', '>', 0)
                ->orderByDesc('total_quantity')
                ->take(3)
                ->get(['id', 'title', 'author']);

            $pendingFineAmount = (float) Fine::where('status', 'unpaid')->sum('amount');
            $newFinesLast30Days = Fine::where('created_at', '>=', $now->copy()->subDays(30))->count();

            $lowStockCount = Book::query()
                ->whereRaw('available_quantity <= total_quantity * ?', [0.2])
                ->count();
            $totalBookTitles = Book::count();
            $lowStockRatio = $totalBookTitles > 0 ? round(($lowStockCount / $totalBookTitles) * 100, 1) : 0.0;

            $overdueCasesLast30Days = BookIssue::query()
                ->whereBetween('due_date', [$now->copy()->subDays(30), $now])
                ->where(function ($query) {
                    $query->whereNull('returned_at')
                        ->orWhereColumn('returned_at', '>', 'due_date');
                })
                ->count();

            $overdueCasesPrevious30Days = BookIssue::query()
                ->whereBetween('due_date', [$now->copy()->subDays(60), $now->copy()->subDays(30)])
                ->where(function ($query) {
                    $query->whereNull('returned_at')
                        ->orWhereColumn('returned_at', '>', 'due_date');
                })
                ->count();

            $finesPrevious30Days = Fine::whereBetween('created_at', [$now->copy()->subDays(60), $now->copy()->subDays(30)])->count();

            $riskLevel = 'low';
            if ($overdueRate >= 30 || $pendingFineAmount >= 5000) {
                $riskLevel = 'high';
            } elseif ($overdueRate >= 15 || $pendingFineAmount >= 2000) {
                $riskLevel = 'medium';
            }

            $recommendations = [];

            if ($overdueRate >= 20) {
                $recommendations[] = [
                    'title' => 'Run overdue follow-up campaign',
                    'detail' => 'Target members with books overdue more than 7 days and send reminders twice a week.',
                    'priority' => 'high',
                ];
            }

            if ($pendingFineAmount >= 2000) {
                $recommendations[] = [
                    'title' => 'Schedule fine recovery window',
                    'detail' => 'Set weekly payment hours and monitor clearance against total outstanding balance.',
                    'priority' => 'medium',
                ];
            }

            if ($highDemandBooks->count() > 0) {
                $recommendations[] = [
                    'title' => 'Restock high-demand titles',
                    'detail' => 'Prioritize new copies for books with zero availability to reduce queue pressure.',
                    'priority' => 'medium',
                ];
            }

            if (count($recommendations) === 0) {
                $recommendations[] = [
                    'title' => 'Performance is stable',
                    'detail' => 'Keep current lending policy and review trend changes at the end of the month.',
                    'priority' => 'low',
                ];
            }

            $alerts = [];

            $overdueSpikeThreshold = max(5, (int) ceil($overdueCasesPrevious30Days * 1.3));
            if ($overdueCasesLast30Days >= $overdueSpikeThreshold && $overdueCasesLast30Days > $overdueCasesPrevious30Days) {
                $alerts[] = [
                    'title' => 'Overdue spike detected',
                    'detail' => "Overdue cases increased from {$overdueCasesPrevious30Days} to {$overdueCasesLast30Days} in the last 30 days.",
                    'severity' => $overdueCasesLast30Days >= ($overdueCasesPrevious30Days * 1.6) ? 'high' : 'medium',
                    'rule' => 'overdue_spike',
                ];
            }

            $fineSpikeThreshold = max(4, (int) ceil($finesPrevious30Days * 1.3));
            if ($newFinesLast30Days >= $fineSpikeThreshold && $newFinesLast30Days > $finesPrevious30Days) {
                $alerts[] = [
                    'title' => 'Fine spike detected',
                    'detail' => "New fines increased from {$finesPrevious30Days} to {$newFinesLast30Days} in the last 30 days.",
                    'severity' => $newFinesLast30Days >= ($finesPrevious30Days * 1.6) ? 'high' : 'medium',
                    'rule' => 'fine_spike',
                ];
            }

            if ($lowStockCount >= 8 || $lowStockRatio >= 25) {
                $alerts[] = [
                    'title' => 'Low-stock pressure detected',
                    'detail' => "{$lowStockCount} titles are at or below 20% stock ({$lowStockRatio}% of catalog).",
                    'severity' => $lowStockRatio >= 35 ? 'high' : 'medium',
                    'rule' => 'low_stock_pressure',
                ];
            }

            if (count($alerts) === 0) {
                $alerts[] = [
                    'title' => 'No major spikes detected',
                    'detail' => 'Overdue, stock, and fine trends are stable versus recent history.',
                    'severity' => 'low',
                    'rule' => 'stable',
                ];
            }

            return [
                'summary' => [
                    'risk_level' => $riskLevel,
                    'overdue_rate' => $overdueRate,
                    'pending_fine_amount' => $pendingFineAmount,
                    'new_fines_last_30_days' => $newFinesLast30Days,
                    'low_stock_titles' => $lowStockCount,
                    'low_stock_ratio' => $lowStockRatio,
                ],
                'alerts' => $alerts,
                'high_demand_books' => $highDemandBooks,
                'recommendations' => $recommendations,
            ];
        });
    }
}

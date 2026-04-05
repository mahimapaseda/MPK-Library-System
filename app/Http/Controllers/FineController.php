<?php

namespace App\Http\Controllers;

use App\Models\Fine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class FineController extends Controller
{
    public function index(Request $request)
    {
        $fines = Fine::query()
            ->with(['issue.book', 'issue.copy', 'member'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery->whereHas('member', function ($memberQuery) use ($search) {
                        $memberQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('member_id', 'like', "%{$search}%");
                    })->orWhereHas('issue.book', function ($bookQuery) use ($search) {
                        $bookQuery->where('title', 'like', "%{$search}%");
                    })->orWhereHas('issue.copy', function ($copyQuery) use ($search) {
                        $copyQuery->where('accession_number', 'like', "%{$search}%");
                    });
                });
            })
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Fines/Index', [
            'fines' => $fines,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function resolve(Request $request, Fine $fine)
    {
        $validated = $request->validate([
            'status' => 'required|in:paid,waived',
            'resolution_notes' => 'nullable|string|max:1000',
        ]);

        $fine->update([
            'status' => $validated['status'],
            'paid_at' => $validated['status'] === 'paid' ? now() : null,
            'waived_at' => $validated['status'] === 'waived' ? now() : null,
            'resolved_by_user_id' => auth()->id(),
            'resolution_notes' => $validated['resolution_notes'] ?? null,
        ]);

        Cache::forget('dashboard_stats');
        Cache::forget('reports.analytics');
        Cache::forget('library_insights');

        return redirect()->back()->with('success', 'Charge updated successfully.');
    }
}

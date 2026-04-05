<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookIssue;
use App\Models\Fine;
use App\Models\Member;
use App\Services\BookInventoryService;
use App\Services\LibraryNotificationService;
use App\Support\SettingCache;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class IssueController extends Controller
{
    public function __construct(
        private readonly LibraryNotificationService $notificationService,
        private readonly BookInventoryService $inventoryService,
    ) {
    }

    public function pos()
    {
        return Inertia::render('Issues/POS', [
            'defaultLoanDays' => (int) SettingCache::get('loan_duration_days', 14),
            'maxBooksPerMember' => (int) SettingCache::get('max_books_per_member', 5),
        ]);
    }

    public function searchBooks(Request $request)
    {
        $query = $request->get('q', '');
        $normalizedQuery = preg_replace('/[\s-]+/', '', (string) $query);

        $books = Book::with('category')
            ->where('available_quantity', '>', 0)
            ->where(function ($nestedQuery) use ($query, $normalizedQuery) {
                $nestedQuery->where('title', 'like', '%' . $query . '%')
                    ->orWhere('author', 'like', '%' . $query . '%')
                    ->orWhere('isbn', 'like', '%' . $query . '%')
                    ->orWhereHas('copies', function ($copyQuery) use ($query) {
                        $copyQuery->where('status', 'available')
                            ->where('accession_number', 'like', '%' . $query . '%');
                    });

                if (! empty($normalizedQuery)) {
                    $nestedQuery->orWhereRaw("REPLACE(REPLACE(isbn, '-', ''), ' ', '') like ?", ['%' . $normalizedQuery . '%']);
                }
            })
            ->limit(10)
            ->get(['id', 'title', 'author', 'isbn', 'available_quantity', 'category_id']);

        return response()->json($books);
    }

    public function searchMembers(Request $request)
    {
        $query = $request->get('q', '');

        return response()->json(
            Member::where('name', 'like', '%' . $query . '%')
                ->orWhere('member_id', 'like', '%' . $query . '%')
                ->limit(10)
                ->get(['id', 'name', 'member_id', 'type', 'grade'])
        );
    }

    public function issueMultiple(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'books' => 'required|array|min:1',
            'books.*.book_id' => 'required|exists:books,id',
            'books.*.due_date' => 'required|date|after:today',
        ]);

        $errors = [];
        $issued = 0;
        $maxBooksPerMember = (int) SettingCache::get('max_books_per_member', 5);

        DB::transaction(function () use ($validated, $maxBooksPerMember, &$errors, &$issued) {
            $activeIssueCount = BookIssue::query()
                ->where('member_id', $validated['member_id'])
                ->whereIn('status', ['issued', 'overdue'])
                ->count();

            $remainingSlots = max(0, $maxBooksPerMember - $activeIssueCount);

            if ($remainingSlots === 0) {
                $errors[] = "Member has reached the maximum active issues ({$maxBooksPerMember}).";
                return;
            }

            foreach ($validated['books'] as $item) {
                if ($issued >= $remainingSlots) {
                    $errors[] = "Maximum active issues limit reached ({$maxBooksPerMember}).";
                    continue;
                }

                $book = Book::query()->lockForUpdate()->find($item['book_id']);

                if (! $book || $book->available_quantity <= 0) {
                    $errors[] = "'{$book?->title}' is not available.";
                    continue;
                }

                $exists = BookIssue::query()
                    ->where('member_id', $validated['member_id'])
                    ->where('book_id', $item['book_id'])
                    ->whereIn('status', ['issued', 'overdue'])
                    ->exists();

                if ($exists) {
                    $errors[] = "Member already has '{$book->title}' issued.";
                    continue;
                }

                $copy = $this->inventoryService->reserveAvailableCopy($book);

                if (! $copy) {
                    $errors[] = "No accession copy is currently available for '{$book->title}'.";
                    continue;
                }

                $issue = BookIssue::query()->create([
                    'book_id' => $book->id,
                    'book_copy_id' => $copy->id,
                    'member_id' => $validated['member_id'],
                    'issued_at' => now(),
                    'due_date' => $item['due_date'],
                    'status' => 'issued',
                ]);

                DB::afterCommit(fn () => $this->notificationService->sendIssueReceipt($issue->loadMissing(['book', 'member', 'copy'])));
                $issued++;
            }
        });

        if ($issued === 0) {
            return back()->with('error', 'Could not issue any books. ' . implode(' ', $errors));
        }

        $message = "{$issued} book" . ($issued > 1 ? 's' : '') . ' issued successfully!';
        if ($errors) {
            $message .= ' Skipped: ' . implode(' ', $errors);
        }

        $member = Member::find($validated['member_id']);
        $member?->logAction('issued', "Bulk checkout: {$issued} items for {$member->name}");

        $this->forgetAnalyticsCaches();

        return back()->with('success', $message);
    }

    public function index(Request $request)
    {
        $issues = BookIssue::with(['book', 'member', 'copy'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery->whereHas('book', fn ($bookQuery) => $bookQuery->where('title', 'like', "%{$search}%"))
                        ->orWhereHas('member', function ($memberQuery) use ($search) {
                            $memberQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('member_id', 'like', "%{$search}%");
                        })
                        ->orWhereHas('copy', fn ($copyQuery) => $copyQuery->where('accession_number', 'like', "%{$search}%"));
                });
            })
            ->when($request->status, function ($query, $status) {
                if ($status === 'overdue') {
                    $query->where(function ($overdueQuery) {
                        $overdueQuery->where('status', 'overdue')
                            ->orWhere(function ($issuedQuery) {
                                $issuedQuery->where('status', 'issued')
                                    ->whereDate('due_date', '<', now());
                            });
                    });
                } else {
                    $query->where('status', $status);
                }
            })
            ->when($request->book_id, fn ($query, $bookId) => $query->where('book_id', $bookId))
            ->when($request->member_id, fn ($query, $memberId) => $query->where('member_id', $memberId))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Issues/Index', [
            'issues' => $issues,
            'books' => Book::where('available_quantity', '>', 0)->latest()->take(50)->get(['id', 'title']),
            'members' => Member::latest()->take(50)->get(['id', 'name', 'member_id']),
            'filters' => $request->only(['search', 'status', 'book_id', 'member_id']),
        ]);
    }

    public function issueBook(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'member_id' => 'required|exists:members,id',
            'due_date' => 'required|date|after:today',
        ]);

        return DB::transaction(function () use ($validated) {
            $book = Book::query()->lockForUpdate()->find($validated['book_id']);
            $maxBooksPerMember = (int) SettingCache::get('max_books_per_member', 5);
            $activeIssueCount = BookIssue::query()
                ->where('member_id', $validated['member_id'])
                ->whereIn('status', ['issued', 'overdue'])
                ->count();

            if ($activeIssueCount >= $maxBooksPerMember) {
                return redirect()->back()->withErrors([
                    'member_id' => "This member has reached the maximum active issues ({$maxBooksPerMember}).",
                ]);
            }

            if (! $book || $book->available_quantity <= 0) {
                return redirect()->back()->withErrors(['book_id' => 'This book is not currently available.']);
            }

            $exists = BookIssue::query()
                ->where('member_id', $validated['member_id'])
                ->where('book_id', $validated['book_id'])
                ->whereIn('status', ['issued', 'overdue'])
                ->exists();

            if ($exists) {
                return redirect()->back()->withErrors(['member_id' => 'This member already has an active issue of this book.']);
            }

            $copy = $this->inventoryService->reserveAvailableCopy($book);

            if (! $copy) {
                return redirect()->back()->withErrors(['book_id' => 'No accession copy is currently available.']);
            }

            $issue = BookIssue::query()->create([
                'book_id' => $book->id,
                'book_copy_id' => $copy->id,
                'member_id' => $validated['member_id'],
                'issued_at' => now(),
                'due_date' => $validated['due_date'],
                'status' => 'issued',
            ]);

            DB::afterCommit(fn () => $this->notificationService->sendIssueReceipt($issue->loadMissing(['book', 'member', 'copy'])));

            $member = Member::find($validated['member_id']);
            $member?->logAction('issued', "Issued '{$book->title}' ({$copy->accession_number}) to {$member->name}");

            $this->forgetAnalyticsCaches();

            return redirect()->back()->with('success', 'Book issued successfully!');
        });
    }

    public function returnBook(Request $request, BookIssue $issue)
    {
        if (in_array($issue->status, ['returned', 'lost', 'damaged'], true)) {
            return redirect()->back()->with('error', 'This issue is already closed.');
        }

        return DB::transaction(function () use ($issue) {
            $issue = BookIssue::with(['book', 'member', 'copy'])->lockForUpdate()->findOrFail($issue->id);

            $issue->update([
                'returned_at' => now(),
                'resolved_at' => null,
                'status' => 'returned',
            ]);

            if ($issue->copy) {
                $this->inventoryService->markCopyAvailable($issue->copy);
            }

            DB::afterCommit(fn () => $this->notificationService->sendReturnReceipt($issue->loadMissing(['book', 'member', 'copy'])));

            $issue->member->logAction('returned', "Returned '{$issue->book->title}' from {$issue->member->name}");

            if (now()->greaterThan($issue->due_date)) {
                $overdueDays = (int) now()->startOfDay()->diffInDays(Carbon::parse($issue->due_date)->startOfDay());
                $graceDays = (int) SettingCache::get('grace_period_days', 0);

                if ($overdueDays > $graceDays) {
                    $finePerDay = (float) SettingCache::get('fine_per_day', 5);
                    $fineAmount = $overdueDays * $finePerDay;

                    Fine::updateOrCreate(
                        [
                            'book_issue_id' => $issue->id,
                            'member_id' => $issue->member_id,
                        ],
                        [
                            'amount' => $fineAmount,
                            'status' => 'unpaid',
                            'paid_at' => null,
                            'waived_at' => null,
                            'resolution_notes' => null,
                            'resolved_by_user_id' => null,
                        ]
                    );

                    $this->forgetAnalyticsCaches();

                    return redirect()->back()->with('warning', "Book returned late ({$overdueDays} days)! Fine of " . SettingCache::get('currency_symbol', 'LKR') . ' ' . number_format($fineAmount, 2) . ' generated.');
                }
            }

            $this->forgetAnalyticsCaches();

            return redirect()->back()->with('success', 'Book returned successfully!');
        });
    }

    public function markCondition(Request $request, BookIssue $issue)
    {
        $validated = $request->validate([
            'status' => 'required|in:lost,damaged',
            'notes' => 'nullable|string|max:1000',
            'fine_amount' => 'nullable|numeric|min:0',
        ]);

        if (in_array($issue->status, ['returned', 'lost', 'damaged'], true)) {
            return redirect()->back()->with('error', 'This issue is already closed and cannot be marked again.');
        }

        return DB::transaction(function () use ($issue, $validated) {
            $issue = BookIssue::with(['book', 'member', 'copy'])->lockForUpdate()->findOrFail($issue->id);

            if (in_array($issue->status, ['returned', 'lost', 'damaged'], true)) {
                return redirect()->back()->with('error', 'This issue is already closed and cannot be marked again.');
            }

            $issue->update([
                'status' => $validated['status'],
                'returned_at' => $validated['status'] === 'damaged' ? now() : null,
                'resolved_at' => now(),
                'condition_notes' => $validated['notes'] ?? null,
                'condition_fee' => $validated['fine_amount'] ?? null,
            ]);

            if ($issue->copy) {
                $this->inventoryService->markCopyIncident($issue->copy, $validated['status']);
            }

            $fineAmount = (float) ($validated['fine_amount'] ?? 0);

            if ($fineAmount > 0) {
                Fine::updateOrCreate(
                    [
                        'book_issue_id' => $issue->id,
                        'member_id' => $issue->member_id,
                    ],
                    [
                        'amount' => $fineAmount,
                        'status' => 'unpaid',
                        'paid_at' => null,
                        'waived_at' => null,
                        'resolution_notes' => null,
                        'resolved_by_user_id' => null,
                    ]
                );
            }

            $incidentLabel = $validated['status'] === 'lost' ? 'lost' : 'damaged';
            $feeMessage = $fineAmount > 0
                ? ' with a charge of ' . SettingCache::get('currency_symbol', 'LKR') . ' ' . number_format($fineAmount, 2)
                : '';

            $issue->member->logAction(
                $validated['status'],
                ucfirst($incidentLabel) . " '{$issue->book->title}' ({$issue->copy?->accession_number}) for {$issue->member->name}{$feeMessage}"
            );

            $this->forgetAnalyticsCaches();

            return redirect()->back()->with('warning', ucfirst($incidentLabel) . " workflow completed{$feeMessage}.");
        });
    }

    private function forgetAnalyticsCaches(): void
    {
        Cache::forget('dashboard_stats');
        Cache::forget('reports.analytics');
        Cache::forget('library_insights');
    }
}

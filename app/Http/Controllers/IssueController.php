<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Book;
use App\Models\Member;
use App\Models\BookIssue;
use App\Models\Fine;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class IssueController extends Controller
{
    public function pos()
    {
        $defaultLoanDays = (int) (\App\Models\Setting::where('key', 'loan_duration_days')->first()?->value ?? 14);
        $maxBooksPerMember = (int) (\App\Models\Setting::where('key', 'max_books_per_member')->first()?->value ?? 5);

        return Inertia::render('Issues/POS', [
            'defaultLoanDays' => $defaultLoanDays,
            'maxBooksPerMember' => $maxBooksPerMember,
        ]);
    }

    public function searchBooks(Request $request)
    {
        $query = $request->get('q', '');
        $books = Book::with('category')
            ->where('available_quantity', '>', 0)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', '%' . $query . '%')
                  ->orWhere('author', 'like', '%' . $query . '%')
                  ->orWhere('isbn', 'like', '%' . $query . '%');
            })
            ->limit(10)
            ->get(['id', 'title', 'author', 'isbn', 'available_quantity', 'category_id']);

        return response()->json($books);
    }

    public function searchMembers(Request $request)
    {
        $query = $request->get('q', '');
        $members = Member::where('name', 'like', '%' . $query . '%')
            ->orWhere('member_id', 'like', '%' . $query . '%')
            ->limit(10)
            ->get(['id', 'name', 'member_id', 'type', 'grade']);

        return response()->json($members);
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

        DB::transaction(function () use ($validated, &$errors, &$issued) {
            foreach ($validated['books'] as $item) {
                $book = Book::lockForUpdate()->find($item['book_id']);

                if ($book->available_quantity <= 0) {
                    $errors[] = "'{$book->title}' is not available.";
                    continue;
                }

                $exists = BookIssue::where('member_id', $validated['member_id'])
                    ->where('book_id', $item['book_id'])
                    ->where('status', 'issued')
                    ->exists();

                if ($exists) {
                    $errors[] = "Member already has '{$book->title}' issued.";
                    continue;
                }

                BookIssue::create([
                    'book_id' => $item['book_id'],
                    'member_id' => $validated['member_id'],
                    'issued_at' => now(),
                    'due_date' => $item['due_date'],
                    'status' => 'issued',
                ]);

                $book->decrement('available_quantity');
                $issued++;
            }
        });

        if ($issued === 0) {
            return back()->with('error', 'Could not issue any books. ' . implode(' ', $errors));
        }

        $message = "{$issued} book" . ($issued > 1 ? 's' : '') . " issued successfully!";
        if ($errors) {
            $message .= ' Skipped: ' . implode(' ', $errors);
        }

        $member = Member::find($validated['member_id']);
        $member->logAction('issued', "Bulk checkout: {$issued} items for {$member->name}");

        Cache::forget('library_insights');

        return back()->with('success', $message);
    }

    public function index(Request $request)
    {
        $issues = BookIssue::with(['book', 'member'])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('book', function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%");
                })->orWhereHas('member', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('member_id', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($query, $status) {
                if ($status === 'overdue') {
                    $query->where('status', 'issued')
                        ->whereDate('due_date', '<', now());
                } else {
                    $query->where('status', $status);
                }
            })
            ->when($request->book_id, function ($query, $book_id) {
                $query->where('book_id', $book_id);
            })
            ->when($request->member_id, function ($query, $member_id) {
                $query->where('member_id', $member_id);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Issues/Index', [
            'issues' => $issues,
            'books' => Book::where('available_quantity', '>', 0)->latest()->take(50)->get(['id', 'title']),
            'members' => Member::latest()->take(50)->get(['id', 'name', 'member_id']),
            'filters' => $request->only(['search', 'status', 'book_id', 'member_id'])
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
            $book = Book::lockForUpdate()->find($validated['book_id']);

            if ($book->available_quantity <= 0) {
                return redirect()->back()->withErrors(['book_id' => 'This book is not currently available.']);
            }

            // Check if member already has this book issued
            $exists = BookIssue::where('member_id', $validated['member_id'])
                ->where('book_id', $validated['book_id'])
                ->where('status', 'issued')
                ->exists();

            if ($exists) {
                return redirect()->back()->withErrors(['member_id' => 'This member already has an active issue of this book.']);
            }

            BookIssue::create([
                'book_id' => $validated['book_id'],
                'member_id' => $validated['member_id'],
                'issued_at' => now(),
                'due_date' => $validated['due_date'],
                'status' => 'issued'
            ]);

            $book->decrement('available_quantity');

            $member = Member::find($validated['member_id']);
            $member->logAction('issued', "Issued '{$book->title}' to {$member->name}");

            Cache::forget('library_insights');

            return redirect()->back()->with('success', 'Book issued successfully!');
        });
    }

    public function returnBook(Request $request, BookIssue $issue)
    {
        if ($issue->status === 'returned') {
            return redirect()->back()->with('error', 'Book is already returned.');
        }

        $issue->update([
            'returned_at' => now(),
            'status' => 'returned'
        ]);

        $issue->book->increment('available_quantity');

        $issue->member->logAction('returned', "Returned '{$issue->book->title}' from {$issue->member->name}");

        // Check for fine
        if (now()->greaterThan($issue->due_date)) {
            $overdueDays = (int) now()->startOfDay()->diffInDays(Carbon::parse($issue->due_date)->startOfDay());
            $graceDays = (int) (\App\Models\Setting::where('key', 'grace_period_days')->first()?->value ?? 0);

            if ($overdueDays > $graceDays) {
                $finePerDay = \App\Models\Setting::where('key', 'fine_per_day')->first()?->value ?? 5;
                $fineAmount = $overdueDays * $finePerDay;

                Fine::create([
                    'book_issue_id' => $issue->id,
                    'member_id' => $issue->member_id,
                    'amount' => $fineAmount,
                    'status' => 'unpaid'
                ]);

                return redirect()->back()->with('warning', "Book returned late ({$overdueDays} days)! Fine of LKR {$fineAmount} generated.");
            }
        }

        Cache::forget('library_insights');

        return redirect()->back()->with('success', 'Book returned successfully!');
    }
}

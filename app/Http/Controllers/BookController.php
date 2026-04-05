<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Services\BookInventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class BookController extends Controller
{
    public function __construct(private readonly BookInventoryService $inventoryService)
    {
    }

    public function index(Request $request)
    {
        $books = Book::with('category')
            ->withCount([
                'copies as available_copies_count' => fn ($query) => $query->where('status', 'available'),
                'copies as issued_copies_count' => fn ($query) => $query->where('status', 'issued'),
                'copies as lost_copies_count' => fn ($query) => $query->where('status', 'lost'),
                'copies as damaged_copies_count' => fn ($query) => $query->where('status', 'damaged'),
            ])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('author', 'like', "%{$search}%")
                        ->orWhere('isbn', 'like', "%{$search}%")
                        ->orWhereHas('copies', fn ($copyQuery) => $copyQuery->where('accession_number', 'like', "%{$search}%"));
                });
            })
            ->when($request->category_id, fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($request->language, fn ($query, $language) => $query->where('language', $language))
            ->when($request->available === 'true', fn ($query) => $query->where('available_quantity', '>', 0))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Books/Index', [
            'books' => $books,
            'categories' => Category::all(),
            'filters' => $request->only(['search', 'category_id', 'language', 'available']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'nullable|string|unique:books,isbn',
            'category_id' => 'required|exists:categories,id',
            'language' => 'required|string',
            'total_quantity' => 'required|integer|min:1',
            'location_rack' => 'nullable|string',
        ]);

        $book = Book::query()->create([
            ...$validated,
            'available_quantity' => $validated['total_quantity'],
        ]);

        $this->inventoryService->provisionCopies($book, (int) $validated['total_quantity']);
        $this->forgetAnalyticsCaches();

        return redirect()->back()->with('success', 'Book added successfully with accession-level copies.');
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'nullable|string|unique:books,isbn,' . $book->id,
            'category_id' => 'required|exists:categories,id',
            'language' => 'required|string',
            'total_quantity' => 'required|integer|min:0',
            'location_rack' => 'nullable|string',
        ]);

        $activeIssuedCount = BookCopy::query()
            ->where('book_id', $book->id)
            ->where('status', 'issued')
            ->count();

        if ((int) $validated['total_quantity'] < $activeIssuedCount) {
            return redirect()->back()->withErrors([
                'total_quantity' => "Active copy count cannot be less than currently issued copies ({$activeIssuedCount}).",
            ]);
        }

        $book->update([
            'title' => $validated['title'],
            'author' => $validated['author'],
            'isbn' => $validated['isbn'],
            'category_id' => $validated['category_id'],
            'language' => $validated['language'],
            'location_rack' => $validated['location_rack'],
        ]);

        $this->inventoryService->alignActiveCopyCount($book, (int) $validated['total_quantity']);
        BookCopy::query()->where('book_id', $book->id)->update(['location_rack' => $validated['location_rack']]);
        $this->forgetAnalyticsCaches();

        return redirect()->back()->with('success', 'Book inventory updated successfully.');
    }

    public function destroy(Book $book)
    {
        if ($book->issues()->exists()) {
            return redirect()->back()->with('error', 'Books with circulation history cannot be deleted.');
        }

        $book->delete();
        $this->forgetAnalyticsCaches();

        return redirect()->back()->with('success', 'Book deleted successfully!');
    }

    private function forgetAnalyticsCaches(): void
    {
        Cache::forget('dashboard_stats');
        Cache::forget('reports.analytics');
        Cache::forget('library_insights');
    }
}

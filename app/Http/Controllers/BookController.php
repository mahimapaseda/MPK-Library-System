<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Category;
use Inertia\Inertia;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $books = Book::with('category')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('author', 'like', "%{$search}%")
                        ->orWhere('isbn', 'like', "%{$search}%");
                });
            })
            ->when($request->category_id, function ($query, $category_id) {
                $query->where('category_id', $category_id);
            })
            ->when($request->language, function ($query, $language) {
                $query->where('language', $language);
            })
            ->when($request->available === 'true', function ($query) {
                $query->where('available_quantity', '>', 0);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = Category::all();

        return Inertia::render('Books/Index', [
            'books' => $books,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category_id', 'language', 'available'])
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

        $validated['available_quantity'] = $validated['total_quantity'];

        Book::create($validated);

        return redirect()->back()->with('success', 'Book added successfully!');
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

        // Adjust available quantity based on total change
        $diff = $validated['total_quantity'] - $book->total_quantity;
        $validated['available_quantity'] = max(0, $book->available_quantity + $diff);

        $book->update($validated);

        return redirect()->back()->with('success', 'Book updated successfully!');
    }

    public function destroy(Book $book)
    {
        $book->delete();
        return redirect()->back()->with('success', 'Book deleted successfully!');
    }
}

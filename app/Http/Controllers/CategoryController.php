<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get();

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
        ]);

        Category::create($validated);
        Cache::forget('reports.analytics');

        return redirect()->back()->with('success', 'Category created successfully!');
    }

    public function destroy(Category $category)
    {
        if ($category->books()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete category that has books.');
        }

        $category->delete();
        Cache::forget('reports.analytics');
        return redirect()->back()->with('success', 'Category deleted successfully!');
    }
}

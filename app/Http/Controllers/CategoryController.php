<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Categories/Index', [
            'categories' => Category::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
        ]);

        Category::create($validated);

        return redirect()->back()->with('success', 'Category created successfully!');
    }

    public function destroy(Category $category)
    {
        if ($category->books()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete category that has books.');
        }

        $category->delete();
        return redirect()->back()->with('success', 'Category deleted successfully!');
    }
}

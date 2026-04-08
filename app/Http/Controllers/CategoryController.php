<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('assets')->latest()->paginate(20);

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:categories,name|max:100',
        ]);

        Category::create(['name' => $request->name]);

        flash_success('Category created.');
        return back();
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
        ]);

        $category->update(['name' => $request->name]);

        flash_success('Category updated.');
        return back();
    }

    public function destroy(Category $category)
    {
        if ($category->assets()->count() > 0) {
            flash_error('Cannot delete a category that has assets. Reassign assets first.');
            return back();
        }

        $category->delete();

        flash_success('Category deleted.');
        return back();
    }
}
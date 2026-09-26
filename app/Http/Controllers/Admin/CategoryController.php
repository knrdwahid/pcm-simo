<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $categories = Category::withCount('articles')->latest()->get();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{3,6}$/'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $count = Category::where('slug', 'like', "{$validated['slug']}%")->count();
        if ($count > 0) {
            $validated['slug'] .= '-' . ($count + 1);
        }

        Category::create($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori baru berhasil ditambahkan!');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{3,6}$/'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $category->update($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil diperbarui!');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->articles()->count() > 0) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih memiliki artikel terkait.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil dihapus!');
    }
}

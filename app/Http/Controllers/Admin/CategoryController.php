<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')->withCount(['products', 'subcategories'])
            ->orderBy('parent_id')->orderBy('sort_order')->orderBy('name')->paginate(30);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = Category::whereNull('parent_id')->orderBy('name')->get();
        return view('admin.categories.form', ['category' => new Category(), 'parents' => $parents]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $category = Category::create($this->withImage($request, $data));
        ActivityLogger::log('category.create', $category->name, 'Category created');
        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category)
    {
        $parents = Category::whereNull('parent_id')->where('id', '!=', $category->id)->orderBy('name')->get();
        return view('admin.categories.form', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validated($request, $category->parent_id ? false : true);
        $category->update($this->withImage($request, $data, $category));
        ActivityLogger::log('category.update', $category->name, 'Category updated');
        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->withErrors(['category' => 'Cannot delete a category that still has products. Move or delete its products first.']);
        }
        if ($category->subcategories()->exists()) {
            return back()->withErrors(['category' => 'Cannot delete a category that has subcategories.']);
        }
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }
        ActivityLogger::log('category.delete', $category->name, 'Category deleted');
        $category->delete();
        return back()->with('success', 'Category deleted.');
    }

    private function withImage(Request $request, array $data, ?Category $category = null): array
    {
        if ($request->hasFile('image')) {
            if ($category?->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }
        return $data;
    }

    private function validated(Request $request, bool $isParent = true): array
    {
        $data = $request->validate([
            'parent_id' => 'nullable|exists:categories,id|not_in:' . ($request->route('category')?->id ?? 0),
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        unset($data['image']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        return $data;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}

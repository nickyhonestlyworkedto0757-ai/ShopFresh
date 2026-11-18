<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HomepageSection;
use Illuminate\Http\Request;

class HomepageSectionController extends Controller
{
    public function index()
    {
        $sections = HomepageSection::orderBy('sort_order')->orderBy('id')->get();
        return view('admin.homepage-sections.index', compact('sections'));
    }

    public function edit(HomepageSection $section)
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.homepage-sections.form', compact('section', 'categories'));
    }

    public function update(Request $request, HomepageSection $section)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'mode' => 'required|in:flag,category,categories,promo',
            'value' => 'nullable|string|max:100',
            'max_items' => 'required|integer|min:4|max:60',
            'is_active' => 'nullable|boolean',
        ]);
        if (in_array($validated['mode'], ['categories', 'promo'])) {
            $validated['value'] = null;
        }
        $validated['is_active'] = $request->boolean('is_active');
        $section->update($validated);
        return redirect()->route('admin.homepage-sections.index')->with('success', 'Section updated.');
    }

    public function toggle(HomepageSection $section)
    {
        $section->update(['is_active' => ! $section->is_active]);
        return back()->with('success', $section->is_active ? 'Section enabled.' : 'Section disabled.');
    }

    public function reorder(Request $request)
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'integer|exists:homepage_sections,id']);
        foreach ($request->input('order') as $sort => $id) {
            HomepageSection::where('id', $id)->update(['sort_order' => ($sort + 1) * 10]);
        }
        return back()->with('success', 'Section order updated.');
    }
}

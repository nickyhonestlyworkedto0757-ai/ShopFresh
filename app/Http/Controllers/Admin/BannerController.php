<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('sort_order')->orderBy('id', 'desc')->paginate(15);
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.form', ['banner' => new Banner()]);
    }

    public function store(Request $request)
    {
        // Bulk upload: multiple banner images in one go (one banner per image)
        if ($request->hasFile('images')) {
            $request->validate([
                'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096',
            ]);
            $count = 0;
            foreach ($request->file('images') as $file) {
                Banner::create([
                    'title' => null,
                    'image' => $file->store('banners', 'public'),
                    'sort_order' => Banner::max('sort_order') + 1,
                    'is_active' => $request->boolean('is_active', true),
                ]);
                $count++;
            }
            ActivityLogger::log('banner.bulk', 'banners', "Bulk uploaded {$count} banners");
            return redirect()->route('admin.banners.index')->with('success', "{$count} banners created.");
        }

        $data = $this->validated($request);
        $data['image'] = $request->file('image')->store('banners', 'public');
        Banner::create($data);
        return redirect()->route('admin.banners.index')->with('success', 'Banner created.');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.form', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $this->validated($request, $banner->exists);
        if ($request->hasFile('image')) {
            if ($banner->image) {
                Storage::disk('public')->delete($banner->image);
            }
            $data['image'] = $request->file('image')->store('banners', 'public');
        }
        $banner->update($data);
        return redirect()->route('admin.banners.index')->with('success', 'Banner updated.');
    }

    public function destroy(Banner $banner)
    {
        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }
        $banner->delete();
        return back()->with('success', 'Banner deleted.');
    }

    private function validated(Request $request, bool $updating = false): array
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image' => ($updating ? 'nullable' : 'required') . '|image|mimes:jpg,jpeg,png,webp|max:4096',
            'link_url' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        unset($data['image']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = $request->boolean('is_active');
        return $data;
    }
}

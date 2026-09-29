<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBannerRequest;
use App\Http\Requests\Admin\UpdateBannerRequest;
use App\Models\Banner;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $currentType = $request->get('type', 'all');

        $query = Banner::with('category')->orderBy('position')->latest();

        if ($currentType === 'home_slider') {
            $query->where(function ($q) {
                $q->where('type', 'home_slider')->orWhereNull('type');
            });
        } elseif ($currentType === 'category_header') {
            $query->where('type', 'category_header');
        }

        $banners = $query->paginate(15)->withQueryString();

        $counts = [
            'all' => Banner::count(),
            'home_slider' => Banner::where(function ($q) {
                $q->where('type', 'home_slider')->orWhereNull('type');
            })->count(),
            'category_header' => Banner::where('type', 'category_header')->count(),
        ];

        return view('admin.banners.index', compact('banners', 'currentType', 'counts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categoryTree = $this->getCategoryTree();
        return view('admin.banners.create', compact('categoryTree'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBannerRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('banners', 'public');
            $validated['image_url'] = $path;
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['position'] = $validated['position'] ?? 0;

        if (($validated['type'] ?? 'home_slider') === 'home_slider') {
            $validated['category_id'] = null;
        }

        Banner::create($validated);

        return redirect()->route('admin.banners.index')->with('success', 'Banner đã được thêm thành công.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Banner $banner)
    {
        $categoryTree = $this->getCategoryTree();
        return view('admin.banners.edit', compact('banner', 'categoryTree'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBannerRequest $request, Banner $banner)
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            // Delete old image
            if ($banner->image_url && Storage::disk('public')->exists($banner->image_url)) {
                Storage::disk('public')->delete($banner->image_url);
            }
            $path = $request->file('image')->store('banners', 'public');
            $validated['image_url'] = $path;
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['position'] = $validated['position'] ?? 0;

        if (($validated['type'] ?? 'home_slider') === 'home_slider') {
            $validated['category_id'] = null;
        }

        $banner->update($validated);

        return redirect()->route('admin.banners.index')->with('success', 'Banner đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Banner $banner)
    {
        if ($banner->image_url && Storage::disk('public')->exists($banner->image_url)) {
            Storage::disk('public')->delete($banner->image_url);
        }

        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'Banner đã được xóa thành công.');
    }

    /**
     * Helper to build flattened category tree for select dropdown.
     */
    protected function getCategoryTree()
    {
        $categories = Category::all();
        $tree = [];

        $buildTree = function ($parentId = null, $prefix = '') use (&$buildTree, $categories, &$tree) {
            $children = $categories->where('parent_id', $parentId);
            foreach ($children as $child) {
                $tree[] = [
                    'id' => $child->id,
                    'name' => $prefix . $child->name,
                    'slug' => $child->slug,
                ];
                $buildTree($child->id, $prefix . '— ');
            }
        };

        $buildTree(null, '');

        return $tree;
    }
}

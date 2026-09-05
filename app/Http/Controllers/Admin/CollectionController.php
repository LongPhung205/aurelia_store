<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CollectionController extends Controller
{
    public function index()
    {
        $collections = Collection::orderBy('position')->orderBy('id', 'desc')->get();
        return view('admin.collections.index', compact('collections'));
    }

    public function create()
    {
        $products = Product::where('status', 'active')->orderBy('name')->get();
        return view('admin.collections.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'position' => 'nullable|integer',
            'is_active' => 'boolean',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        
        // Ensure slug is unique
        $originalSlug = $validated['slug'];
        $count = 1;
        while (Collection::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $count;
            $count++;
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('collections', 'public');
            $validated['image'] = $path;
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['position'] = $validated['position'] ?? 0;

        $collection = Collection::create($validated);

        if ($request->has('products')) {
            $collection->products()->sync($request->products);
        }

        return redirect()->route('admin.collections.index')->with('success', 'Bộ sưu tập đã được thêm thành công.');
    }

    public function edit(Collection $collection)
    {
        $products = Product::where('status', 'active')->orderBy('name')->get();
        $selectedProducts = $collection->products->pluck('id')->toArray();
        return view('admin.collections.edit', compact('collection', 'products', 'selectedProducts'));
    }

    public function update(Request $request, Collection $collection)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'position' => 'nullable|integer',
            'is_active' => 'boolean',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id',
        ]);

        if ($request->name !== $collection->name) {
            $validated['slug'] = Str::slug($validated['name']);
            $originalSlug = $validated['slug'];
            $count = 1;
            while (Collection::where('slug', $validated['slug'])->where('id', '!=', $collection->id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $count;
                $count++;
            }
        }

        if ($request->hasFile('image')) {
            if ($collection->image && Storage::disk('public')->exists($collection->image)) {
                Storage::disk('public')->delete($collection->image);
            }
            $path = $request->file('image')->store('collections', 'public');
            $validated['image'] = $path;
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['position'] = $validated['position'] ?? 0;

        $collection->update($validated);

        if ($request->has('products')) {
            $collection->products()->sync($request->products);
        } else {
            $collection->products()->sync([]);
        }

        return redirect()->route('admin.collections.index')->with('success', 'Bộ sưu tập đã được cập nhật thành công.');
    }

    public function destroy(Collection $collection)
    {
        if ($collection->image && Storage::disk('public')->exists($collection->image)) {
            Storage::disk('public')->delete($collection->image);
        }
        
        $collection->delete();

        return redirect()->route('admin.collections.index')->with('success', 'Bộ sưu tập đã được xóa thành công.');
    }
}

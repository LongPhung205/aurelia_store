<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Product;
use App\Models\Category;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['categories', 'variants', 'images']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $categoryId = $request->category_id;
            $allCategories = Category::all();
            
            $getDescendants = function($parentId) use (&$getDescendants, $allCategories) {
                $ids = [$parentId];
                $children = $allCategories->where('parent_id', $parentId);
                foreach ($children as $child) {
                    $ids = array_merge($ids, $getDescendants($child->id));
                }
                return $ids;
            };
            
            $categoryIds = $getDescendants($categoryId);

            $query->whereHas('categories', function($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('color_id')) {
            $query->whereHas('variants', function($q) use ($request) {
                $q->where('color_id', $request->color_id);
            });
        }

        if ($request->filled('size_id')) {
            $query->whereHas('variants', function($q) use ($request) {
                $q->where('size_id', $request->size_id);
            });
        }

        $products = $query->latest()->paginate(10)->withQueryString();

        $categories = \App\Models\Category::whereNull('parent_id')->with('children.children')->get();
        $colors = \App\Models\Color::all();
        $sizes = \App\Models\Size::all();
        $materials = \App\Models\Material::all();

        return view('admin.products.index', compact('products', 'categories', 'colors', 'sizes', 'materials'));
    }

    public function create()
    {
        $categories = Category::whereNull('parent_id')->with('children')->get();
        $colors = \App\Models\Color::all();
        $sizes = \App\Models\Size::all();
        $materials = \App\Models\Material::all();
        return view('admin.products.create', compact('categories', 'colors', 'sizes', 'materials'));
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        $uploadedFiles = [];
        $colorImagePaths = [];

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($data, $request, &$uploadedFiles, &$colorImagePaths) {
                // Upload color images first
                if ($request->hasFile('color_images')) {
                    foreach ($request->file('color_images') as $colorId => $file) {
                        $path = $file->store('variants', 'public');
                        $colorImagePaths[$colorId] = $path;
                        $uploadedFiles[] = $path;
                    }
                }

                $product = Product::create([
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'description' => $data['description'] ?? null,
                    'material' => $data['material'] ?? null,
                    'status' => $data['status'],
                ]);
                
                if (!empty($data['material'])) {
                    \App\Models\Material::firstOrCreate(['name' => trim($data['material'])]);
                }

                $product->categories()->sync($data['category_ids']);

                // Handle variant creation
                if (!empty($data['variants']) && is_array($data['variants'])) {
                    foreach ($data['variants'] as $variant) {
                        $colorId = $variant['color_id'] ?? null;
                        $thumbnailUrl = $colorId && isset($colorImagePaths[$colorId]) ? $colorImagePaths[$colorId] : null;

                        \App\Models\ProductVariant::create([
                            'product_id' => $product->id,
                            'color_id' => $colorId,
                            'size_id' => $variant['size_id'] ?? null,
                            'sku' => $variant['sku'],
                            'price' => $variant['price'],
                            'thumbnail_url' => $thumbnailUrl,
                            'is_active' => true,
                        ]);
                    }
                }
            });
            
            // Return to index page after creation
            return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được tạo thành công.');
            
        } catch (\Exception $e) {
            // Rollback files if transaction failed
            foreach ($uploadedFiles as $path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
            }
            
            return redirect()->back()->withInput()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function show(Product $product)
    {
        $product->load(['categories', 'variants', 'images']);
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = \App\Models\Category::whereNull('parent_id')->with('children')->get();
        $colors = \App\Models\Color::all();
        $sizes = \App\Models\Size::all();
        $product->load(['variants.color', 'variants.size', 'images']);
        return view('admin.products.edit', compact('product', 'categories', 'colors', 'sizes'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        $uploadedFiles = [];

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($data, $request, $product, &$uploadedFiles) {
                $product->update([
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'description' => $data['description'] ?? null,
                    'material' => $data['material'] ?? null,
                    'status' => $data['status'],
                ]);

                if (!empty($data['material'])) {
                    \App\Models\Material::firstOrCreate(['name' => trim($data['material'])]);
                }

                $product->categories()->sync($data['category_ids']);

                // Handle color images upload if present (from the 'Ảnh theo phân loại' tab)
                if ($request->hasFile('color_images')) {
                    foreach ($request->file('color_images') as $colorId => $file) {
                        $path = $file->store('variants', 'public');
                        $uploadedFiles[] = $path;
                        // Update all variants of this product with this color
                        \App\Models\ProductVariant::where('product_id', $product->id)
                                                  ->where('color_id', $colorId)
                                                  ->update(['thumbnail_url' => $path]);
                    }
                }

                // Handle image upload if present
                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $file) {
                        $path = $file->store('products', 'public');
                        $uploadedFiles[] = $path;
                        \App\Models\ProductImage::create([
                            'product_id' => $product->id,
                            'image_url' => $path,
                            'variant_id' => null,
                            'is_primary' => false,
                        ]);
                    }
                }
            });

            return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được cập nhật thành công.');
        } catch (\Exception $e) {
            foreach ($uploadedFiles as $path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
            }

            return redirect()->back()->withInput()->with('error', 'Có lỗi xảy ra khi cập nhật sản phẩm: ' . $e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }
}


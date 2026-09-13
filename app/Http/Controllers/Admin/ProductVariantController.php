<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\ProductVariant;
use App\Models\Product;
use App\Http\Requests\Admin\StoreProductVariantRequest;
use App\Http\Requests\Admin\UpdateProductVariantRequest;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    /**
     * Display a listing of the variants for a specific product.
     */
    public function index(Product $product)
    {
        $variants = $product->variants()->with(['color', 'size', 'image'])->get();
        return response()->json($variants);
    }

    /**
     * Store a newly created variant in storage.
     */
    public function store(StoreProductVariantRequest $request, Product $product)
    {
        $data = $request->validated();
        if (empty($data['variants']) || !is_array($data['variants'])) return redirect()->back();

        $colorImagePaths = [];
        if ($request->hasFile('color_images')) {
            foreach ($request->file('color_images') as $colorId => $file) {
                $colorImagePaths[$colorId] = $file->store('variants', 'public');
            }
        }
        
        $colorIds = collect($data['variants'])->pluck('color_id')->filter()->unique();
        $sizeIds = collect($data['variants'])->pluck('size_id')->filter()->unique();
        
        $colors = \App\Models\Color::whereIn('id', $colorIds)->pluck('name', 'id');
        $sizes = \App\Models\Size::whereIn('id', $sizeIds)->pluck('name', 'id');
        $productSlug = \Illuminate\Support\Str::slug($product->name);

        \Illuminate\Support\Facades\DB::transaction(function() use ($data, $product, $colorImagePaths, $colors, $sizes, $productSlug) {
            foreach ($data['variants'] as $variantData) {
                $colorId = $variantData['color_id'] ?? null;
                $sizeId = $variantData['size_id'] ?? null;
                
                $sku = $variantData['sku'] ?? null;
                if (empty($sku)) {
                    $skuParts = [$productSlug];
                    if ($colorId && isset($colors[$colorId])) $skuParts[] = \Illuminate\Support\Str::slug($colors[$colorId]);
                    if ($sizeId && isset($sizes[$sizeId])) $skuParts[] = \Illuminate\Support\Str::slug($sizes[$sizeId]);
                    $sku = strtoupper($product->id . '-' . implode('-', $skuParts));
                }

                $variant = ProductVariant::firstOrNew([
                    'product_id' => $product->id,
                    'color_id' => $colorId,
                    'size_id' => $sizeId,
                ]);

                $variant->sku = $sku;
                $variant->price = $variantData['price'];
                $variant->is_active = true;
                if (!$variant->exists) $variant->stock_quantity = 0;
                if ($colorId && isset($colorImagePaths[$colorId])) $variant->thumbnail_url = $colorImagePaths[$colorId];
                
                $variant->save();
            }
        });
        
        return redirect()->back()->with('success', 'Đã lưu các biến thể thành công.');
    }

    /**
     * Update the specified variant in storage.
     */
    public function update(UpdateProductVariantRequest $request, Product $product, ProductVariant $variant)
    {
        // Ensure variant belongs to the correct product
        if ($variant->product_id !== $product->id) {
            return response()->json(['message' => 'Variant does not belong to this product'], 403);
        }

        $data = $request->validated();
        
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('variants', 'public');
            $data['thumbnail_url'] = $path;
        }

        $variant->update($data);
        
        return redirect()->back()->with('success', 'Biến thể đã được cập nhật thành công.');
    }

    /**
     * Remove the specified variant from storage.
     */
    public function destroy(Product $product, ProductVariant $variant)
    {
        if ($variant->product_id !== $product->id) {
            abort(403);
        }

        if ($variant->thumbnail_url) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($variant->thumbnail_url);
        }

        $variant->delete();
        
        return redirect()->back()->with('success', 'Đã xóa biến thể.');
    }
}


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
        
        $colorImagePaths = [];
        if ($request->hasFile('color_images')) {
            foreach ($request->file('color_images') as $colorId => $file) {
                $path = $file->store('variants', 'public');
                $colorImagePaths[$colorId] = $path;
            }
        }
        
        if (!empty($data['variants']) && is_array($data['variants'])) {
            foreach ($data['variants'] as $variantData) {
                $colorId = $variantData['color_id'] ?? null;
                $sizeId = $variantData['size_id'] ?? null;
                
                // Get image path if uploaded for this color
                $thumbnailUrl = $colorId && isset($colorImagePaths[$colorId]) ? $colorImagePaths[$colorId] : null;
                
                // Generate SKU if empty
                $sku = $variantData['sku'] ?? null;
                if (empty($sku)) {
                    $color = $colorId ? \App\Models\Color::find($colorId) : null;
                    $size = $sizeId ? \App\Models\Size::find($sizeId) : null;
                    
                    $skuParts = [];
                    $skuParts[] = \Illuminate\Support\Str::slug($product->name);
                    if ($color) $skuParts[] = \Illuminate\Support\Str::slug($color->name);
                    if ($size) $skuParts[] = \Illuminate\Support\Str::slug($size->name);
                    
                    $sku = strtoupper(implode('-', $skuParts));
                }

                ProductVariant::create([
                    'product_id' => $product->id,
                    'color_id' => $colorId,
                    'size_id' => $sizeId,
                    'sku' => $sku,
                    'price' => $variantData['price'],
                    'stock_quantity' => 0,
                    'thumbnail_url' => $thumbnailUrl,
                    'is_active' => true,
                ]);
            }
        }
        
        return redirect()->back()->with('success', 'Đã thêm các biến thể mới.');
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


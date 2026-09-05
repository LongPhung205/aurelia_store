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
        $data['product_id'] = $product->id;
        
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('variants', 'public');
            $data['thumbnail_url'] = $path;
        }
        
        ProductVariant::create($data);
        
        return redirect()->back()->with('success', 'Đã thêm biến thể mới.');
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


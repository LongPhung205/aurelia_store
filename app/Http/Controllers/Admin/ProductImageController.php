<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\ProductImage;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('products', 'public');
                
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_url' => $path,
                    'variant_id' => null, // This is a general product image
                    'is_primary' => false,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Đã tải ảnh lên thành công.');
    }

    public function destroy(ProductImage $productImage)
    {
        if (Storage::disk('public')->exists($productImage->image_url)) {
            Storage::disk('public')->delete($productImage->image_url);
        }
        
        $productImage->delete();
        
        return redirect()->back()->with('success', 'Đã xóa ảnh.');
    }
}


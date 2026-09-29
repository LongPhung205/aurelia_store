<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductImageRequest;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function store(StoreProductImageRequest $request, Product $product)
    {
        $uploadedPaths = [];

        try {
            DB::transaction(function () use ($request, $product, &$uploadedPaths) {
                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $file) {
                        $path = $file->store('products', 'public');
                        $uploadedPaths[] = $path;

                        ProductImage::create([
                            'product_id' => $product->id,
                            'image_url' => $path,
                            'variant_id' => null,
                            'is_primary' => false,
                        ]);
                    }
                }
            });

            return redirect()->back()->with('success', 'Đã tải ảnh lên thành công.');
        } catch (\Exception $e) {
            foreach ($uploadedPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tải ảnh: ' . $e->getMessage());
        }
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

<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreReviewRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Product $product)
    {
        // Kiểm tra quyền: Chỉ cho phép người dùng đã mua sản phẩm và hoàn thành đơn hàng đánh giá
        $hasPurchased = Order::where('user_id', auth()->id())
            ->where('status', 'completed')
            ->whereHas('items.productVariant', fn($q) => $q->where('product_id', $product->id))
            ->exists();

        if (!$hasPurchased) {
            return redirect()->back()->with('error', 'Bạn chỉ có thể đánh giá sản phẩm sau khi đã mua và đơn hàng hoàn thành.');
        }

        $uploadedPaths = [];
        try {
            DB::transaction(function () use ($request, $product, &$uploadedPaths) {
                $review = $product->reviews()->create([
                    'user_id' => auth()->id(),
                    'rating' => $request->validated()['rating'],
                    'content' => $request->validated()['content'],
                    'is_approved' => true,
                ]);

                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $file) {
                        $path = $file->store('reviews', 'public');
                        $uploadedPaths[] = $path;
                        $review->images()->create([
                            'image_url' => $path,
                        ]);
                    }
                }
            });

            return redirect()->back()->with('success', 'Cảm ơn bạn đã đánh giá sản phẩm!');
        } catch (\Exception $e) {
            foreach ($uploadedPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            return redirect()->back()->withInput()->with('error', 'Có lỗi xảy ra khi lưu đánh giá. Vui lòng thử lại.');
        }
    }
}

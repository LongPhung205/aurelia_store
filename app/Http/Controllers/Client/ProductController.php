<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Coupon;
use Illuminate\Http\Request;

use App\Services\MarketBasketMiningService;

class ProductController extends Controller
{
    public function show($slug, MarketBasketMiningService $basketService)
    {
        $product = Product::with([
            'variants.color', 
            'variants.size', 
            'images', 
            'categories', 
            'reviews.user',
            'reviews.images'
        ])->where('slug', $slug)->firstOrFail();

        // Get unique colors and sizes for this product
        $colors = $product->variants->map(function($v) { return $v->color; })->filter()->unique('id');
        $sizes = $product->variants->map(function($v) { return $v->size; })->filter()->unique('id');

        // Fetch active coupons
        $coupons = Coupon::where('is_active', true)
                         ->where('start_time', '<=', now())
                         ->where('end_time', '>=', now())
                         ->get();

        // Check if user has purchased this product (Stub logic for now)
        $hasPurchased = false;
        if (auth()->check()) {
            $hasPurchased = true; // TODO: Replace with real order check
        }

        // Related Products (same category)
        $categoryIds = $product->categories->pluck('id');
        $relatedProducts = Product::whereHas('categories', function($q) use ($categoryIds) {
            $q->whereIn('categories.id', $categoryIds);
        })->where('id', '!=', $product->id)
          ->with(['variants.color', 'variants.size', 'primaryImage'])
          ->take(8)
          ->get();

        // Apriori Algorithm Frequently Bought Together Combo
        $frequentlyBoughtTogether = $basketService->getFrequentlyBoughtTogether($product->id);

        return view('client.products.show', compact(
            'product', 
            'colors', 
            'sizes', 
            'coupons', 
            'hasPurchased', 
            'relatedProducts',
            'frequentlyBoughtTogether'
        ));
    }
}

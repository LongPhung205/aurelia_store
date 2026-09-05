<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\FlashSale;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // 1. Lấy Flash Sale đang active (nếu có) kèm items và variants
        $flashSale = FlashSale::with(['items.product.variants.color', 'items.product.variants.size', 'items.product.primaryImage'])
            ->where('is_active', true)
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->first();

        // 2. Lấy Banners (active, sort by position)
        $banners = \App\Models\Banner::where('is_active', true)
            ->orderBy('position')
            ->get();

        // 3. Lấy 8 sản phẩm nổi bật (Top view_count)
        $featuredProducts = Product::with(['variants.color', 'variants.size', 'primaryImage'])
            ->where('status', 'active') // Giả sử có trạng thái active
            ->orderBy('view_count', 'desc')
            ->take(8)
            ->get();
            
        // Nếu trường status không có thì cần check lại migration Product, nhưng mình đã xem bảng Product có trường 'status'

        // 4. Lấy Collections (Bộ sưu tập)
        $collections = \App\Models\Collection::where('is_active', true)
            ->orderBy('position', 'asc')
            ->take(6)
            ->get();

        // 5. Lấy danh sách ID sản phẩm đã yêu thích
        $wishlistedProductIds = [];
        if (auth()->check()) {
            $wishlistedProductIds = auth()->user()->wishlists()->pluck('product_id')->toArray();
        }
        
        // 6. Lấy các danh mục Mức 2 (đồng cấp, không lấy sâu hơn)
        $homeCategories = Category::whereNotNull('parent_id')
            ->whereHas('parent', function ($query) {
                $query->whereNull('parent_id');
            })
            ->where('is_active', true)
            ->take(6)
            ->get();

        // 7. Lấy Tạp chí / Lookbook mới nhất
        $lookbooks = \App\Models\Post::where('is_active', true)
            ->whereNotNull('published_at')
            ->latest('published_at')
            ->take(4)
            ->get();

        return view('client.home.index', compact('flashSale', 'banners', 'featuredProducts', 'collections', 'wishlistedProductIds', 'homeCategories', 'lookbooks'));
    }
}

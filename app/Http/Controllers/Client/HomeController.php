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
            ->home()
            ->orderBy('position')
            ->get();

        // 3. Lấy 8 sản phẩm nổi bật (Top view_count)
        $featuredProducts = Product::with(['variants.color', 'variants.size', 'primaryImage'])
            ->where('status', 'active') // Giả sử có trạng thái active
            ->orderBy('view_count', 'desc')
            ->take(8)
            ->get();
            
        // Nếu trường status không có thì cần check lại migration Product, nhưng mình đã xem bảng Product có trường 'status'



        // 5. Lấy danh sách ID sản phẩm đã yêu thích
        $wishlistedProductIds = [];
        if (auth()->check()) {
            $wishlistedProductIds = auth()->user()->wishlists()->pluck('product_id')->toArray();
        }
        
        // 6. Lấy tất cả danh mục các cấp có hình ảnh
        $homeCategories = Category::where('is_active', true)
            ->whereNotNull('image')
            ->where('image', '!=', '')
            ->get();

        // 7. Lấy Tạp chí / Lookbook mới nhất
        $lookbooks = \App\Models\Post::where('is_active', true)
            ->whereNotNull('published_at')
            ->latest('published_at')
            ->take(4)
            ->get();

        // 8. Lấy tất cả sản phẩm (phân trang 12 cái 1 trang)
        $allProducts = Product::with(['variants.color', 'variants.size', 'primaryImage'])
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('client.home.index', compact('flashSale', 'banners', 'featuredProducts', 'wishlistedProductIds', 'homeCategories', 'lookbooks', 'allProducts'));
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        $productsQuery = Product::where('status', 'active')
            ->with(['variants.color', 'variants.size', 'primaryImage']);

        if (!empty($query)) {
            $productsQuery->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            });
        }

        // Apply filters
        if ($request->filled('colors')) {
            $colorIds = (array) $request->colors;
            $productsQuery->whereHas('variants', fn($q) => $q->whereIn('color_id', $colorIds));
        }

        if ($request->filled('sizes')) {
            $sizeIds = (array) $request->sizes;
            $productsQuery->whereHas('variants', fn($q) => $q->whereIn('size_id', $sizeIds));
        }

        if ($request->filled('price_min') || $request->filled('price_max')) {
            $priceMin = $request->price_min ?? 0;
            $priceMax = $request->price_max ?? 999999999;
            $productsQuery->whereHas('variants', function ($q) use ($priceMin, $priceMax) {
                $q->where(function($q2) use ($priceMin, $priceMax) {
                    $q2->whereBetween('sale_price', [$priceMin, $priceMax])
                       ->orWhere(function($q3) use ($priceMin, $priceMax) {
                           $q3->whereNull('sale_price')->whereBetween('price', [$priceMin, $priceMax]);
                       });
                });
            });
        }

        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'price_asc':
                $productsQuery->withMin('variants', 'price')->orderBy('variants_min_price', 'asc');
                break;
            case 'price_desc':
                $productsQuery->withMin('variants', 'price')->orderBy('variants_min_price', 'desc');
                break;
            case 'name_asc':
                $productsQuery->orderBy('name', 'asc');
                break;
            default:
                $productsQuery->orderBy('id', 'desc');
                break;
        }

        $products = $productsQuery->paginate(12)->withQueryString();

        // Data for sidebar filters
        $searchProductIds = Product::where('status', 'active');
        if (!empty($query)) {
            $searchProductIds->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            });
        }
        $searchProductIds = $searchProductIds->pluck('id');

        $allColors = \App\Models\Color::whereHas('variants', fn($q) =>
            $q->whereIn('product_id', $searchProductIds)
        )->get();

        $allSizes = \App\Models\Size::whereHas('variants', fn($q) =>
            $q->whereIn('product_id', $searchProductIds)
        )->get();

        $priceStats = \App\Models\ProductVariant::whereIn('product_id', $searchProductIds)
            ->selectRaw('MIN(COALESCE(sale_price, price)) as min_price, MAX(COALESCE(sale_price, price)) as max_price')
            ->first();

        $wishlistedProductIds = [];
        if (auth()->check()) {
            $wishlistedProductIds = auth()->user()->wishlists()->pluck('product_id')->toArray();
        }

        return view('client.search.index', compact('products', 'wishlistedProductIds', 'query', 'sort', 'allColors', 'allSizes', 'priceStats'));
    }

    public function apiSearch(Request $request)
    {
        $query = $request->get('q', '');
        if (empty($query)) {
            return response()->json([]);
        }

        $products = Product::where('status', 'active')
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->with('primaryImage', 'variants')
            ->take(5)
            ->get()
            ->map(function ($product) {
                // Calculate display price (min price of variants or fallback)
                $minPrice = $product->variants->min(function($v) {
                    return $v->sale_price ?? $v->price;
                });
                
                $imgUrl = $product->primary_image_url;
                if ($imgUrl && !\Illuminate\Support\Str::startsWith($imgUrl, ['http://', 'https://'])) {
                    $imgUrl = \Illuminate\Support\Facades\Storage::url($imgUrl);
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price_formatted' => number_format($minPrice ?: 0, 0, ',', '.') . 'đ',
                    'image_url' => $imgUrl ?: asset('images/no-image.png'),
                    'url' => route('products.show', $product->slug)
                ];
            });

        return response()->json($products);
    }
}

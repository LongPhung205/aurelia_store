<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Color;
use App\Models\Size;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)
            ->firstOrFail();

        // Base query: products in this category
        $query = $category->products()
            ->where('status', 'active')
            ->with(['variants.color', 'variants.size', 'primaryImage']);

        // --- FILTERS ---
        if ($request->filled('colors')) {
            $colorIds = (array) $request->colors;
            $query->whereHas('variants', fn($q) => $q->whereIn('color_id', $colorIds));
        }

        if ($request->filled('sizes')) {
            $sizeIds = (array) $request->sizes;
            $query->whereHas('variants', fn($q) => $q->whereIn('size_id', $sizeIds));
        }

        if ($request->filled('price_min') || $request->filled('price_max')) {
            $priceMin = $request->price_min ?? 0;
            $priceMax = $request->price_max ?? 999999999;
            $query->whereHas('variants', function ($q) use ($priceMin, $priceMax) {
                $q->where(function($q2) use ($priceMin, $priceMax) {
                    $q2->whereBetween('sale_price', [$priceMin, $priceMax])
                       ->orWhere(function($q3) use ($priceMin, $priceMax) {
                           $q3->whereNull('sale_price')->whereBetween('price', [$priceMin, $priceMax]);
                       });
                });
            });
        }

        // --- SORT ---
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'price_asc':
                $query->withMin('variants', 'price')->orderBy('variants_min_price', 'asc');
                break;
            case 'price_desc':
                $query->withMin('variants', 'price')->orderBy('variants_min_price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            default:
                $query->orderBy('id', 'desc');
                break;
        }

        $products = $query->paginate(12)->withQueryString();

        // --- Filter data for sidebar ---
        $categoryProductIds = $category->products()->where('status', 'active')->pluck('products.id');

        $allColors = Color::whereHas('variants', fn($q) =>
            $q->whereIn('product_id', $categoryProductIds)
        )->get();

        $allSizes = Size::whereHas('variants', fn($q) =>
            $q->whereIn('product_id', $categoryProductIds)
        )->get();

        $priceStats = \App\Models\ProductVariant::whereIn('product_id', $categoryProductIds)
            ->selectRaw('MIN(COALESCE(sale_price, price)) as min_price, MAX(COALESCE(sale_price, price)) as max_price')
            ->first();

        $wishlistedProductIds = [];
        if (auth()->check()) {
            $wishlistedProductIds = auth()->user()->wishlists()->pluck('product_id')->toArray();
        }

        return view('client.categories.show', compact(
            'category', 'products', 'wishlistedProductIds',
            'allColors', 'allSizes', 'priceStats', 'sort'
        ));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\Category;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Product::with(['categories', 'images', 'variants'])
            ->withSum('variants', 'stock_quantity')
            ->withCount('variants')
            ->withCount(['variants as low_stock_count' => function ($q) {
                $q->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
            }]);

        if ($request->has('low_stock') && $request->low_stock == '1') {
            $query->whereHas('variants', function($q) {
                $q->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
            });
        }

        if ($request->filled('category_id')) {
            $categoryId = $request->category_id;
            $query->whereHas('categories', function($q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            });
        }
        
        if ($request->filled('sku')) {
            $sku = $request->sku;
            $query->where(function($q) use ($sku) {
                $q->where('name', 'like', '%' . $sku . '%')
                  ->orWhereHas('variants', function($q2) use ($sku) {
                      $q2->where('sku', 'like', '%' . $sku . '%');
                  });
            });
        }

        $products = $query->orderBy('id', 'desc')->paginate(15)->appends($request->all());
        
        $categories = Category::whereNull('parent_id')->with('children.children')->get();

        return view('admin.inventory.index', compact('products', 'categories'));
    }

    public function details(Request $request, \App\Models\Product $product)
    {
        $variants = $product->variants()->with(['color', 'size'])->get();
        return view('admin.inventory.partials.details', compact('variants'));
    }
}

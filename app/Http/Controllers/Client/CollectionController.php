<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function show($slug)
    {
        $collection = Collection::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Paginate products in this collection
        $products = $collection->products()
            ->where('status', 'active')
            ->with(['variants.color', 'variants.size', 'primaryImage'])
            ->paginate(12);

        $wishlistedProductIds = [];
        if (auth()->check()) {
            $wishlistedProductIds = auth()->user()->wishlists()->pluck('product_id')->toArray();
        }

        return view('client.collections.show', compact('collection', 'products', 'wishlistedProductIds'));
    }
}

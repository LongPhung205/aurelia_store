<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index()
    {
        $cart = $this->cartService->getCart();
        $cart->load(['items.productVariant.product.primaryImage', 'items.productVariant.color', 'items.productVariant.size']);
        
        // Gắn thêm url ảnh để frontend dễ lấy
        $cart->items->each(function($item) {
            $item->display_image_url = $item->productVariant->thumbnail_url ?: ($item->productVariant->product->primary_image_url ?? null);
        });
        
        // Lấy danh sách ID sản phẩm đã có trong giỏ hàng để loại trừ
        $inCartProductIds = $cart->items->pluck('productVariant.product.id')->filter()->unique()->toArray();
        
        // Lấy 4 sản phẩm ngẫu nhiên để gợi ý (Cross-selling)
        $crossSellProducts = \App\Models\Product::where('status', 'active')
            ->whereNotIn('id', $inCartProductIds)
            ->with(['primaryImage'])
            ->inRandomOrder()
            ->take(4)
            ->get();
        
        return view('client.cart.index', compact('cart', 'crossSellProducts'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $result = $this->cartService->addItem($request->variant_id, $request->quantity);

        return response()->json($result);
    }

    public function update(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id',
            'quantity' => 'required|integer|min:0',
        ]);

        $result = $this->cartService->updateItem($request->cart_item_id, $request->quantity);

        return response()->json($result);
    }

    public function remove(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id',
        ]);

        $result = $this->cartService->removeItem($request->cart_item_id);

        return response()->json($result);
    }
}

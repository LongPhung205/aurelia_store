<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\AddToCartRequest;
use App\Http\Requests\Client\UpdateCartItemRequest;
use App\Http\Requests\Client\RemoveCartItemRequest;
use App\Models\Product;
use App\Services\CartService;

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
        $crossSellProducts = Product::where('status', 'active')
            ->whereNotIn('id', $inCartProductIds)
            ->with(['primaryImage'])
            ->inRandomOrder()
            ->take(4)
            ->get();
        
        return view('client.cart.index', compact('cart', 'crossSellProducts'));
    }

    public function add(AddToCartRequest $request)
    {
        $validated = $request->validated();
        $result = $this->cartService->addItem($validated['variant_id'], $validated['quantity']);

        return response()->json($result);
    }

    public function update(UpdateCartItemRequest $request)
    {
        $validated = $request->validated();
        $result = $this->cartService->updateItem($validated['cart_item_id'], $validated['quantity']);

        return response()->json($result);
    }

    public function remove(RemoveCartItemRequest $request)
    {
        $validated = $request->validated();
        $result = $this->cartService->removeItem($validated['cart_item_id']);

        return response()->json($result);
    }
}

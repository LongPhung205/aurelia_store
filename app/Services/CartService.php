<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartService
{
    /**
     * Lấy guest_token từ cookie hoặc tạo mới.
     */
    public function getGuestToken(): string
    {
        $token = Cookie::get('guest_token');
        if (!$token) {
            $token = (string) Str::uuid();
            Cookie::queue('guest_token', $token, 60 * 24 * 30); // 30 ngày
        }
        return $token;
    }

    /**
     * Xóa guest_token khỏi cookie.
     */
    public function forgetGuestToken(): void
    {
        Cookie::queue(Cookie::forget('guest_token'));
    }

    /**
     * Lấy giỏ hàng hiện tại (của User hoặc Guest).
     * Tạo mới nếu chưa có.
     */
    public function getCart(): Cart
    {
        if (Auth::check()) {
            return Cart::firstOrCreate(
                ['user_id' => Auth::id()],
                ['guest_token' => null]
            );
        }

        $token = $this->getGuestToken();
        return Cart::firstOrCreate(
            ['guest_token' => $token],
            ['user_id' => null]
        );
    }

    /**
     * Thêm sản phẩm vào giỏ hàng.
     */
    public function addItem(int $variantId, int $quantity): array
    {
        $cart = $this->getCart();
        $variant = ProductVariant::findOrFail($variantId);

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_variant_id', $variantId)
            ->first();

        $currentQuantity = $cartItem ? $cartItem->quantity : 0;
        $newQuantity = $currentQuantity + $quantity;

        // Check tồn kho
        if ($newQuantity > $variant->stock_quantity) {
            return [
                'success' => false,
                'message' => 'Số lượng vượt quá tồn kho (Tồn: ' . $variant->stock_quantity . ')'
            ];
        }

        if ($cartItem) {
            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_variant_id' => $variantId,
                'quantity' => $newQuantity,
            ]);
        }

        return [
            'success' => true,
            'message' => 'Đã thêm vào giỏ hàng',
            'cart_quantity' => $cart->fresh()->total_quantity,
        ];
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ hàng.
     */
    public function updateItem(int $cartItemId, int $quantity): array
    {
        $cartItem = CartItem::with('productVariant')->findOrFail($cartItemId);
        
        $cart = $this->getCart();
        if ($cartItem->cart_id !== $cart->id) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }

        if ($quantity <= 0) {
            $cartItem->delete();
            return [
                'success' => true,
                'message' => 'Đã xóa khỏi giỏ hàng',
                'cart_quantity' => $cart->fresh()->total_quantity,
            ];
        }

        if ($quantity > $cartItem->productVariant->stock_quantity) {
            return [
                'success' => false,
                'message' => 'Số lượng vượt quá tồn kho hiện tại',
                'cart_quantity' => $cart->fresh()->total_quantity,
            ];
        }

        $cartItem->update(['quantity' => $quantity]);

        return [
            'success' => true,
            'message' => 'Đã cập nhật số lượng',
            'cart_quantity' => $cart->fresh()->total_quantity,
        ];
    }

    /**
     * Xóa 1 sản phẩm khỏi giỏ.
     */
    public function removeItem(int $cartItemId): array
    {
        $cart = $this->getCart();
        $cartItem = CartItem::where('id', $cartItemId)->where('cart_id', $cart->id)->first();
        
        if ($cartItem) {
            $cartItem->delete();
        }

        return [
            'success' => true,
            'message' => 'Đã xóa sản phẩm khỏi giỏ',
            'cart_quantity' => $cart->fresh()->total_quantity,
        ];
    }

    /**
     * Xóa toàn bộ giỏ.
     */
    public function clearCart(): void
    {
        $cart = $this->getCart();
        $cart->items()->delete();
    }

    /**
     * Gộp giỏ hàng Guest vào giỏ hàng User sau khi đăng nhập.
     */
    public function mergeGuestCart(): void
    {
        if (!Auth::check()) {
            return;
        }

        $token = Cookie::get('guest_token');
        if (!$token) {
            return;
        }

        $guestCart = Cart::with('items')->where('guest_token', $token)->first();
        if (!$guestCart || $guestCart->items->isEmpty()) {
            if ($guestCart) $guestCart->delete();
            $this->forgetGuestToken();
            return;
        }

        DB::transaction(function () use ($guestCart) {
            $userCart = Cart::firstOrCreate(
                ['user_id' => Auth::id()],
                ['guest_token' => null]
            );

            foreach ($guestCart->items as $guestItem) {
                $userItem = CartItem::where('cart_id', $userCart->id)
                    ->where('product_variant_id', $guestItem->product_variant_id)
                    ->first();

                if ($userItem) {
                    $newQuantity = $userItem->quantity + $guestItem->quantity;
                    
                    // Lấy stock_quantity
                    $stock = ProductVariant::find($guestItem->product_variant_id)->stock_quantity ?? 0;
                    if ($newQuantity > $stock) {
                        $newQuantity = $stock; // max = stock
                    }

                    $userItem->update(['quantity' => $newQuantity]);
                } else {
                    CartItem::create([
                        'cart_id' => $userCart->id,
                        'product_variant_id' => $guestItem->product_variant_id,
                        'quantity' => $guestItem->quantity,
                    ]);
                }
            }

            $guestCart->delete();
        });

        $this->forgetGuestToken();
    }
}

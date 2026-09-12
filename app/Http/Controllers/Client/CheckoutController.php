<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\GhnService;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    protected $cartService;
    protected $ghnService;

    public function __construct(CartService $cartService, GhnService $ghnService)
    {
        $this->cartService = $cartService;
        $this->ghnService = $ghnService;
    }

    public function prepare(Request $request)
    {
        $request->validate([
            'selected_items' => 'required|array|min:1',
            'selected_items.*' => 'integer|exists:cart_items,id'
        ]);

        session(['selected_cart_items' => $request->selected_items]);
        
        return response()->json(['success' => true, 'redirect' => route('checkout.index')]);
    }

    public function index()
    {
        $cart = $this->cartService->getCart();
        $selectedItems = session('selected_cart_items', []);
        
        if (empty($selectedItems)) {
            return redirect()->route('cart.index')->with('error', 'Vui lòng chọn sản phẩm để thanh toán.');
        }

        $cartItems = $cart->items()->whereIn('id', $selectedItems)->with(['productVariant.product', 'productVariant.color', 'productVariant.size'])->get();
        
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Sản phẩm đã chọn không tồn tại trong giỏ hàng.');
        }

        $subtotal = $cartItems->sum(function($item) {
            $price = $item->productVariant->sale_price ?? $item->productVariant->price;
            return $price * $item->quantity;
        });
        
        $provinces = $this->ghnService->getProvinces();

        return view('client.checkout.index', compact('cartItems', 'subtotal', 'provinces'));
    }

    public function applyCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string',
            'subtotal' => 'required|numeric'
        ]);

        $coupon = Coupon::where('code', $request->coupon_code)
            ->where('is_active', true)
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->first();

        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn.']);
        }

        if ($coupon->min_order_value && $request->subtotal < $coupon->min_order_value) {
            return response()->json(['success' => false, 'message' => 'Đơn hàng chưa đạt giá trị tối thiểu ' . number_format($coupon->min_order_value, 0, ',', '.') . 'đ để sử dụng mã này.']);
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            return response()->json(['success' => false, 'message' => 'Mã giảm giá này đã hết lượt sử dụng.']);
        }

        $discount = 0;
        if ($coupon->type === 'percent') {
            $discount = ($request->subtotal * $coupon->value) / 100;
            if ($coupon->max_discount && $discount > $coupon->max_discount) {
                $discount = $coupon->max_discount;
            }
        } else {
            $discount = $coupon->value;
        }

        if ($discount > $request->subtotal) {
            $discount = $request->subtotal;
        }

        return response()->json([
            'success' => true,
            'message' => 'Áp dụng mã giảm giá thành công!',
            'discount' => $discount,
            'coupon_code' => $coupon->code
        ]);
    }

    // Ajax calls
    public function getDistricts(Request $request)
    {
        $districts = $this->ghnService->getDistricts($request->province_id);
        return response()->json($districts);
    }

    public function getWards(Request $request)
    {
        $wards = $this->ghnService->getWards($request->district_id);
        return response()->json($wards);
    }

    public function calculateFee(Request $request)
    {
        $fee = $this->ghnService->calculateFee(
            $request->to_district_id,
            $request->to_ward_code,
            $request->weight ?? 200
        );
        return response()->json(['fee' => $fee]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'province_id' => 'required|integer',
            'district_id' => 'required|integer',
            'ward_code' => 'required|string',
            'payment_method' => 'required|in:cod,payos,momo',
            'coupon_code' => 'nullable|string',
        ]);

        $cart = $this->cartService->getCart();
        $selectedItems = session('selected_cart_items', []);
        
        if (empty($selectedItems)) {
            return redirect()->route('cart.index')->with('error', 'Vui lòng chọn lại sản phẩm để thanh toán.');
        }

        $cartItems = $cart->items()->whereIn('id', $selectedItems)->with(['productVariant.product'])->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Sản phẩm thanh toán không hợp lệ.');
        }

        // Calculate totals
        $subtotal = $cartItems->sum(function($item) {
            $price = $item->productVariant->sale_price ?? $item->productVariant->price;
            return $price * $item->quantity;
        });

        $discount = 0;
        $couponId = null;

        if ($request->filled('coupon_code')) {
            $coupon = Coupon::where('code', $request->coupon_code)
                ->where('is_active', true)
                ->where('start_time', '<=', now())
                ->where('end_time', '>=', now())
                ->first();
                
            if ($coupon && (!$coupon->min_order_value || $subtotal >= $coupon->min_order_value) && ($coupon->usage_limit === null || $coupon->used_count < $coupon->usage_limit)) {
                if ($coupon->type === 'percent') {
                    $discount = ($subtotal * $coupon->value) / 100;
                    if ($coupon->max_discount && $discount > $coupon->max_discount) {
                        $discount = $coupon->max_discount;
                    }
                } else {
                    $discount = $coupon->value;
                }
                
                if ($discount > $subtotal) $discount = $subtotal;
                $couponId = $coupon->id;
            }
        }

        // In reality, you'd re-calculate the fee here or pass from frontend. We will re-calculate for security.
        $shippingFee = $this->ghnService->calculateFee($request->district_id, $request->ward_code, 200);
        $totalAmount = $subtotal + $shippingFee - $discount;

        DB::beginTransaction();
        try {
            // Create Order
            $order = Order::create([
                'user_id' => auth()->id(),
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'address' => $request->address,
                'province_id' => $request->province_id,
                'district_id' => $request->district_id,
                'ward_code' => $request->ward_code,
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'coupon_id' => $couponId,
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'note' => $request->note,
            ]);

            // Create Order Items
            foreach ($cartItems as $item) {
                $variantId = $item->productVariant->id;
                // Use lockForUpdate to ensure no race condition during checkout
                $lockedVariant = \App\Models\ProductVariant::where('id', $variantId)->lockForUpdate()->first();
                
                if (!$lockedVariant || $lockedVariant->stock_quantity < $item->quantity) {
                    throw new \Exception("Sản phẩm hiện không đủ số lượng trong kho.");
                }

                $product = $lockedVariant->product;
                
                $price = $lockedVariant->sale_price ?? $lockedVariant->price;
                
                $order->items()->create([
                    'product_variant_id' => $lockedVariant->id,
                    'product_name' => $product->name,
                    'variant_attributes' => ($lockedVariant->color->name ?? '') . ' - ' . ($lockedVariant->size->name ?? ''),
                    'quantity' => $item->quantity,
                    'price' => $price,
                    'total' => $price * $item->quantity,
                ]);

                // Deduct stock immediately to reserve it, regardless of payment method
                $lockedVariant->decrement('stock_quantity', $item->quantity);
            }

            // Clear only selected items from cart
            $cart->items()->whereIn('id', $selectedItems)->delete();
            session()->forget('selected_cart_items');

            if ($couponId) {
                Coupon::where('id', $couponId)->increment('used_count');
            }

            DB::commit();

            if ($request->payment_method === 'payos') {
                return redirect()->route('payos.create', ['order' => $order->id]);
            }

            if ($request->payment_method === 'momo') {
                return redirect()->route('momo.start', ['order' => $order->id]);
            }

            return redirect()->route('home')->with('success', 'Đặt hàng thành công! Đơn hàng của bạn đang chờ xác nhận (Mã đơn: ORD-' . $order->id . ')');

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Checkout Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi đặt hàng. Vui lòng thử lại sau.');
        }
    }
}

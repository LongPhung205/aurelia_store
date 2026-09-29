<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\ProductVariant;
use App\Models\User;
use App\Notifications\LowStockNotification;
use App\Notifications\NewOrderNotification;
use App\Services\CartService;
use App\Services\GhnService;
use Illuminate\Http\Request;
use App\Http\Requests\Client\PrepareCheckoutRequest;
use App\Http\Requests\Client\ApplyCouponRequest;
use App\Http\Requests\Client\StoreCheckoutRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class CheckoutController extends Controller
{
    protected $cartService;

    protected $ghnService;

    public function __construct(CartService $cartService, GhnService $ghnService)
    {
        $this->cartService = $cartService;
        $this->ghnService = $ghnService;
    }

    public function prepare(PrepareCheckoutRequest $request)
    {
        session(['selected_cart_items' => $request->validated()['selected_items']]);

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

        $subtotal = $cartItems->sum(function ($item) {
            $price = $item->productVariant->sale_price ?? $item->productVariant->price;

            return $price * $item->quantity;
        });

        $provinces = $this->ghnService->getProvinces();

        return view('client.checkout.index', compact('cartItems', 'subtotal', 'provinces'));
    }

    public function applyCoupon(ApplyCouponRequest $request)
    {

        $coupon = Coupon::where('code', $request->coupon_code)
            ->where('is_active', true)
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->first();

        if (! $coupon) {
            return response()->json(['success' => false, 'message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn.']);
        }

        if ($coupon->min_order_value && $request->subtotal < $coupon->min_order_value) {
            return response()->json(['success' => false, 'message' => 'Đơn hàng chưa đạt giá trị tối thiểu '.number_format($coupon->min_order_value, 0, ',', '.').'đ để sử dụng mã này.']);
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
            'coupon_code' => $coupon->code,
        ]);
    }

    // Ajax calls
    public function getDistricts(Request $request)
    {
        $provinceId = (int) $request->input('province_id');
        $districts = $provinceId > 0 ? $this->ghnService->getDistricts($provinceId) : [];

        return response()->json($districts);
    }

    public function getWards(Request $request)
    {
        $districtId = (int) $request->input('district_id');
        $wards = $districtId > 0 ? $this->ghnService->getWards($districtId) : [];

        return response()->json($wards);
    }

    public function calculateFee(Request $request)
    {
        $districtId = (int) $request->input('to_district_id');
        $wardCode = (string) $request->input('to_ward_code');
        $weight = (int) ($request->input('weight') ?? 200);

        $fee = ($districtId > 0 && $wardCode !== '')
            ? $this->ghnService->calculateFee($districtId, $wardCode, $weight)
            : 0;

        return response()->json(['fee' => $fee]);
    }

    public function store(StoreCheckoutRequest $request)
    {
        $validated = $request->validated();

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
        $subtotal = $cartItems->sum(function ($item) {
            $price = $item->productVariant->sale_price ?? $item->productVariant->price;

            return $price * $item->quantity;
        });

        $discount = 0;
        $couponId = null;

        if ($request->filled('coupon_code')) {
            $coupon = Coupon::where('code', $request->coupon_code)->first();
            if ($coupon && $coupon->isValid($subtotal)) {
                $discount = $coupon->calculateDiscount($subtotal);
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
                'is_inventory_deducted' => true,
                'note' => $request->note,
            ]);

            // Create Order Items
            foreach ($cartItems as $item) {
                $variantId = $item->productVariant->id;
                // Use lockForUpdate to ensure no race condition during checkout
                $lockedVariant = ProductVariant::where('id', $variantId)->lockForUpdate()->first();

                if (! $lockedVariant || $lockedVariant->stock_quantity < $item->quantity) {
                    throw new \Exception('Sản phẩm hiện không đủ số lượng trong kho.');
                }

                $product = $lockedVariant->product;

                $price = $lockedVariant->sale_price ?? $lockedVariant->price;

                $order->items()->create([
                    'product_variant_id' => $lockedVariant->id,
                    'product_name' => $product->name,
                    'variant_attributes' => ($lockedVariant->color->name ?? '').' - '.($lockedVariant->size->name ?? ''),
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
            session(['last_order_id' => $order->id]);

            if ($couponId) {
                Coupon::where('id', $couponId)->increment('used_count');
            }

            // Auto generate transaction for COD order
            if ($order->payment_method === 'cod') {
                PaymentTransaction::create([
                    'order_id'       => $order->id,
                    'transaction_id' => 'COD-ORD-' . $order->id,
                    'amount'         => $order->total_amount,
                    'payment_method' => 'cod',
                    'status'         => 'pending',
                ]);
            }

            DB::commit();

            // Notifications
            try {
                $admins = User::where('role', 'admin')->get();
                if ($admins->count() > 0) {
                    Notification::send($admins, new NewOrderNotification($order));

                    foreach ($cartItems as $item) {
                        $variant = ProductVariant::find($item->productVariant->id);
                        if ($variant && $variant->stock_quantity <= 10) {
                            Notification::send($admins, new LowStockNotification($variant));
                        }
                    }
                }
            } catch (\Exception $notifyException) {
                Log::error('Notification Error: '.$notifyException->getMessage());
            }

            if ($request->payment_method === 'payos') {
                return redirect()->route('payos.create', ['order' => $order->id]);
            }

            if ($request->payment_method === 'momo') {
                return redirect()->route('momo.start', ['order' => $order->id]);
            }

            return redirect()->route('checkout.success', ['order' => $order->id])->with('success', 'Đặt hàng thành công! Đơn hàng của bạn đang chờ xác nhận (Mã đơn: ORD-'.$order->id.')');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout Error: '.$e->getMessage());

            return redirect()->back()->withInput()->with('error', 'Có lỗi xảy ra khi đặt hàng. Vui lòng thử lại sau.');
        }
    }

    /**
     * Display order success page
     */
    public function success(Order $order)
    {
        $isOwner = auth()->check() && $order->user_id === auth()->id();
        $isSessionGuest = session('last_order_id') == $order->id;

        if (!$isOwner && !$isSessionGuest && $order->user_id) {
            return redirect()->route('login')->with('info', 'Vui lòng đăng nhập để xem chi tiết đơn hàng.');
        }

        $order->load(['items.productVariant.product.images', 'items.productVariant.color', 'items.productVariant.size']);

        return view('client.checkout.success', compact('order'));
    }
}

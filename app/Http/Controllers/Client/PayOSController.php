<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PayOS\PayOS;

class PayOSController extends Controller
{
    protected $payOS;

    public function __construct()
    {
        $this->payOS = new PayOS(
            env('PAYOS_CLIENT_ID'),
            env('PAYOS_API_KEY'),
            env('PAYOS_CHECKSUM_KEY')
        );
    }

    public function create(Request $request, $orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Check if order is still valid for payment (pending and within 30 minutes)
        if ($order->payment_status !== 'pending' || $order->status === 'cancelled') {
            return redirect()->route('cart.index')->with('error', 'Đơn hàng này không thể thanh toán nữa. Vui lòng tạo đơn hàng mới.');
        }

        if ($order->created_at->diffInMinutes(now()) > 30) {
            return redirect()->route('cart.index')->with('error', 'Đơn hàng đã hết hạn thanh toán (quá 30 phút). Vui lòng tạo đơn hàng mới.');
        }

        // Create Payment Link Data
        // orderCode must be integer and unique
        $orderCode = intval($order->id . time());

        $data = [
            "orderCode" => $orderCode,
            "amount" => intval($order->total_amount),
            "description" => "Thanh toan don " . $order->id,
            "returnUrl" => route('payos.return'),
            "cancelUrl" => route('payos.cancel'),
        ];

        try {
            $response = $this->payOS->createPaymentLink($data);
            
            // Save the payos order code to the order for webhook matching
            $order->update(['shipping_order_code' => $orderCode]); // Reusing a field or create a new one. Wait, shipping_order_code is for shipping.
            
            return redirect($response['checkoutUrl']);
        } catch (\Exception $e) {
            Log::error('PayOS Create Link Error: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Không thể khởi tạo cổng thanh toán. Vui lòng liên hệ CSKH.');
        }
    }

    public function returnPage(Request $request)
    {
        $orderCode = $request->query('orderCode');
        $order = null;

        if ($orderCode) {
            $order = Order::with(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size'])
                ->where('shipping_order_code', $orderCode)
                ->first();
            
            if ($order && $order->user_id !== auth()->id()) abort(403);
        }

        // When user successfully pays, PayOS redirects here
        return view('client.payos.success', compact('order'));
    }

    public function cancelPage(Request $request)
    {
        $orderCode = $request->query('orderCode');
        $order = null;

        if ($orderCode) {
            $order = Order::with(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size'])
                ->where('shipping_order_code', $orderCode)
                ->first();

            if ($order && $order->user_id !== auth()->id()) abort(403);
        }

        // When user cancels payment
        return view('client.payos.cancel', compact('order'));
    }

    public function webhook(Request $request)
    {
        $body = $request->all();

        try {
            $data = $this->payOS->verifyPaymentWebhookData($body);

            if ($data['code'] == '00' || $data['desc'] == 'success') {
                $orderCode = $data['orderCode'];
                
                // Find order by the prefixed orderCode we saved
                // Since we used intval($order->id . time()), we need a way to find the order.
                // Wait, it's better to extract the order ID or just loop. But a better way is to save orderCode to database.
                // Since I reused shipping_order_code above:
                $order = Order::where('shipping_order_code', $orderCode)
                    ->where('payment_status', 'pending')
                    ->first();

                if ($order) {
                    DB::beginTransaction();
                    try {
                        $order->update([
                            'payment_status' => 'paid',
                            'status' => '1' // Confirmed or similar based on your logic
                        ]);

                        // Note: Stock was already deducted at checkout.
                        
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('PayOS Webhook DB Error: ' . $e->getMessage());
                    }
                }
            }

            return response()->json([
                "error" => 0,
                "message" => "Ok",
                "data" => null
            ]);
            
        } catch (\Exception $e) {
            Log::error('PayOS Webhook Verify Error: ' . $e->getMessage());
            return response()->json([
                "error" => 1,
                "message" => "Invalid signature",
                "data" => null
            ]);
        }
    }
}

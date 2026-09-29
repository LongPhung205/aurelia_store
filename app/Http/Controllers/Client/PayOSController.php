<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PayOS\PayOS;

class PayOSController extends Controller
{
    protected $payOS;

    public function __construct(?PayOS $payOS = null)
    {
        $this->payOS = $payOS ?? (app()->bound(PayOS::class) ? app(PayOS::class) : new PayOS(
            config('services.payos.client_id'),
            config('services.payos.api_key'),
            config('services.payos.checksum_key')
        ));
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
        $orderCode = intval($order->id.time());

        $data = [
            'orderCode' => $orderCode,
            'amount' => intval($order->total_amount),
            'description' => 'Thanh toan don '.$order->id,
            'returnUrl' => route('payos.return'),
            'cancelUrl' => route('payos.cancel'),
        ];

        try {
            $response = $this->payOS->createPaymentLink($data);

            // Decouple orderCode: Record transaction in payment_transactions instead of overwriting shipping_order_code
            PaymentTransaction::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'payment_method' => 'payos',
                ],
                [
                    'transaction_id' => (string) $orderCode,
                    'amount' => $order->total_amount,
                    'status' => 'pending',
                ]
            );

            return redirect($response['checkoutUrl']);
        } catch (\Exception $e) {
            Log::error('PayOS Create Link Error: '.$e->getMessage());

            return redirect()->route('home')->with('error', 'Không thể khởi tạo cổng thanh toán. Vui lòng liên hệ CSKH.');
        }
    }

    public function returnPage(Request $request)
    {
        $orderCode = $request->query('orderCode');
        $order = null;

        if ($orderCode) {
            $transaction = PaymentTransaction::where('transaction_id', (string) $orderCode)->first();
            $order = $transaction ? $transaction->order : (Order::find($orderCode) ?? Order::where('shipping_order_code', (string) $orderCode)->first());

            if ($order) {
                $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']);
                if ($order->user_id !== auth()->id()) {
                    abort(403);
                }
            }
        }

        // When user successfully pays, PayOS redirects here
        return view('client.payos.success', compact('order'));
    }

    public function cancelPage(Request $request)
    {
        $orderCode = $request->query('orderCode');
        $order = null;

        if ($orderCode) {
            $transaction = PaymentTransaction::where('transaction_id', (string) $orderCode)->first();
            $order = $transaction ? $transaction->order : (Order::find($orderCode) ?? Order::where('shipping_order_code', (string) $orderCode)->first());

            if ($order) {
                $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']);
                if ($order->user_id !== auth()->id()) {
                    abort(403);
                }
            }
        }

        // When user cancels payment
        return view('client.payos.cancel', compact('order'));
    }

    public function webhook(Request $request)
    {
        $webhookData = $request->all();

        // 1. Scalar / Malformed Payload Protection
        if (isset($webhookData['data']) && ! is_array($webhookData['data'])) {
            return response()->json(['error' => 1, 'message' => 'Invalid data payload'], 400);
        }

        // Signature Verification
        try {
            $verifiedData = $this->payOS->verifyPaymentWebhookData($webhookData);
        } catch (\Throwable $e) {
            Log::error('PayOS Webhook Invalid Signature: '.$e->getMessage(), ['payload' => $webhookData]);

            return response()->json(['error' => 1, 'message' => 'Invalid signature'], 400);
        }

        // 2. Safe Order Code Extraction & Order Lookup
        $orderCode = $verifiedData['orderCode'] ?? null;
        if (! $orderCode) {
            Log::error('PayOS Webhook Missing orderCode in payload', ['payload' => $webhookData]);

            return response()->json(['error' => 1, 'message' => 'Order not found'], 404);
        }

        $transaction = PaymentTransaction::where('transaction_id', (string) $orderCode)->first();
        $order = $transaction ? $transaction->order : (Order::find($orderCode) ?? Order::where('shipping_order_code', (string) $orderCode)->first());

        if (! $order) {
            Log::error("PayOS Webhook Order Not Found for orderCode: {$orderCode}");

            return response()->json(['error' => 1, 'message' => 'Order not found'], 404);
        }

        // 3. Idempotency & Concurrency with Row Locking
        DB::beginTransaction();
        try {
            $order = Order::where('id', $order->id)->lockForUpdate()->first();

            // Amount Integrity Verification
            if (intval($verifiedData['amount'] ?? 0) < intval($order->total_amount)) {
                DB::rollBack();
                Log::warning("PayOS Webhook Payment amount mismatch for Order #{$order->id}: expected {$order->total_amount}, received ".($verifiedData['amount'] ?? 0));

                return response()->json(['error' => 1, 'message' => 'Payment amount mismatch'], 400);
            }

            if ($order->payment_status === 'paid') {
                if ($transaction && $transaction->status !== 'success') {
                    $transaction->update([
                        'status' => 'success',
                        'response_data' => $webhookData,
                    ]);
                }
                DB::commit();

                return response()->json(['error' => 0, 'message' => 'Order already processed', 'data' => null]);
            }

            // 4. State Update on Success (code === '00')
            if (($verifiedData['code'] ?? null) === '00') {
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'processing',
                    'is_inventory_deducted' => true,
                ]);

                if ($transaction) {
                    $transaction->update([
                        'status' => 'success',
                        'response_data' => $webhookData,
                    ]);
                }

                DB::commit();

                return response()->json(['error' => 0, 'message' => 'Payment processed successfully', 'data' => null]);
            } else {
                if ($transaction) {
                    $transaction->update([
                        'status' => 'failed',
                        'response_data' => $webhookData,
                    ]);
                }

                DB::commit();

                return response()->json(['error' => 0, 'message' => 'Payment failed', 'data' => null]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PayOS Webhook Processing Error: '.$e->getMessage(), [
                'order_id' => $order->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 1, 'message' => 'Internal server error'], 500);
        }
    }
}

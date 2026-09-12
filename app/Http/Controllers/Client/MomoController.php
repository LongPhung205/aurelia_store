<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\MomoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MomoController extends Controller
{
    protected $momoService;

    public function __construct(MomoService $momoService)
    {
        $this->momoService = $momoService;
    }

    public function start(Order $order)
    {
        if ($order->payment_status === 'paid') {
            return redirect()->route('home')->with('error', 'Đơn hàng này đã được thanh toán.');
        }

        $transactionId = time() . '_' . $order->id;
        $transaction = $this->newTransaction($order, $transactionId, $order->total_amount);

        return $this->redirectToMomo($order, $transaction);
    }

    public function payAgain(Order $order)
    {
        if ($order->payment_status === 'paid') {
            return redirect()->route('home')->with('error', 'Đơn hàng này đã được thanh toán.');
        }

        $transactionId = time() . '_' . $order->id . '_retry';
        $transaction = $this->newTransaction($order, $transactionId, $order->total_amount);

        return $this->redirectToMomo($order, $transaction);
    }

    protected function newTransaction(Order $order, $transactionId, $amount)
    {
        return PaymentTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'payment_method' => 'momo',
            'status' => 'pending'
        ]);
    }

    protected function redirectToMomo(Order $order, PaymentTransaction $transaction)
    {
        // Truyền transaction_id từ PaymentTransaction sang MomoService để đồng bộ
        $result = $this->momoService->createPayment($order, $transaction->transaction_id);

        if ($result['success']) {
            return redirect()->away($result['payUrl']);
        }

        $this->markFailed($order, $transaction, ['error' => $result['message'] ?? 'Lỗi khởi tạo MoMo']);
        return redirect()->route('home')->with('error', 'Không thể khởi tạo thanh toán MoMo: ' . ($result['message'] ?? 'Lỗi không xác định'));
    }

    public function callback(Request $request)
    {
        $data = $request->all();
        
        if (!$this->momoService->verifySignature($data)) {
            return redirect()->route('home')->with('error', 'Chữ ký số không hợp lệ từ MoMo.');
        }

        $parts = explode('_', $data['orderId']);
        $realOrderId = $parts[0];
        
        $order = Order::find($realOrderId);
        if (!$order) {
            return redirect()->route('home')->with('error', 'Không tìm thấy đơn hàng.');
        }

        $transaction = PaymentTransaction::where('order_id', $order->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if (!$transaction) {
            if ($order->payment_status === 'paid') {
                return view('client.momo.success', compact('order'));
            }
            return view('client.momo.cancel', compact('order'));
        }

        if ($data['resultCode'] == 0) {
            $this->completePayment($order, $transaction, $data);
            return view('client.momo.success', compact('order'));
        } else {
            $this->markFailed($order, $transaction, $data);
            return view('client.momo.cancel', compact('order'));
        }
    }

    public function ipn(Request $request)
    {
        $data = $request->all();
        Log::info('MoMo IPN Request: ', $data);

        if (!$this->momoService->verifySignature($data)) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $parts = explode('_', $data['orderId']);
        $realOrderId = $parts[0];
        
        $order = Order::find($realOrderId);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $transaction = PaymentTransaction::where('order_id', $order->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if ($data['resultCode'] == 0) {
            if ($transaction && $transaction->status !== 'success') {
                $this->completePayment($order, $transaction, $data);
            } elseif ($order->payment_status !== 'paid') {
                $order->update(['payment_status' => 'paid']);
            }
        } else {
            if ($transaction && $transaction->status !== 'failed') {
                $this->markFailed($order, $transaction, $data);
            }
        }

        return response()->json(['message' => 'IPN Processed'], 200);
    }

    protected function completePayment(Order $order, PaymentTransaction $transaction, $responseData)
    {
        DB::transaction(function () use ($order, $transaction, $responseData) {
            $transaction->update([
                'status' => 'success',
                'transaction_id' => $responseData['transId'] ?? $transaction->transaction_id,
                'response_data' => $responseData
            ]);

            if ($order->payment_status !== 'paid') {
                $order->update(['payment_status' => 'paid']);
            }
            
            // Việc tạo vận đơn tự động nếu có thể được thực hiện ở đây hoặc thông qua Event
        });
    }

    protected function markFailed(Order $order, PaymentTransaction $transaction, $responseData)
    {
        $transaction->update([
            'status' => 'failed',
            'transaction_id' => $responseData['transId'] ?? $transaction->transaction_id,
            'response_data' => $responseData
        ]);
    }
}

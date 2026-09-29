<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Http\Requests\Admin\UpdateTransactionRequest;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = PaymentTransaction::with(['order.user', 'reconciledBy'])
            ->latest('id');

        // Search by transaction_id, order_id, customer name or phone
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhere('order_id', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($oq) use ($search) {
                      $oq->where('customer_name', 'like', "%{$search}%")
                         ->orWhere('customer_phone', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by payment method
        if ($method = $request->input('payment_method')) {
            if ($method !== 'all') {
                $query->where('payment_method', $method);
            }
        }

        // Filter by status
        if ($status = $request->input('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        // Filter by date range
        if ($fromDate = $request->input('from_date')) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        if ($toDate = $request->input('to_date')) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $transactions = $query->paginate(15)->withQueryString();

        // Calculate counts for quick badges
        $stats = [
            'total_count'   => PaymentTransaction::count(),
            'success_count' => PaymentTransaction::where('status', 'success')->count(),
            'pending_count' => PaymentTransaction::where('status', 'pending')->count(),
            'failed_count'  => PaymentTransaction::where('status', 'failed')->count(),
            'total_amount'  => PaymentTransaction::where('status', 'success')->sum('amount'),
        ];

        return view('admin.transactions.index', compact('transactions', 'stats'));
    }

    public function update(UpdateTransactionRequest $request, PaymentTransaction $transaction)
    {
        $validated = $request->validated();

        $transaction->update([
            'status'        => $validated['status'],
            'note'          => $validated['note'],
            'admin_id'      => auth()->id(),
            'reconciled_at' => $validated['status'] === 'success' ? now() : $transaction->reconciled_at,
        ]);

        // Sync payment status with Order
        if ($transaction->order) {
            $orderStatus = match ($validated['status']) {
                'success' => 'paid',
                'failed'  => 'failed',
                default   => 'pending',
            };
            $transaction->order->update(['payment_status' => $orderStatus]);
        }

        return redirect()->back()->with('success', "Cập nhật đối soát giao dịch #{$transaction->transaction_id} thành công!");
    }
}

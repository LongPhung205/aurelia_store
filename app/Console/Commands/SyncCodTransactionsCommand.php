<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\PaymentTransaction;

class SyncCodTransactionsCommand extends Command
{
    protected $signature = 'app:sync-cod-transactions';
    protected $description = 'Tự động sinh PaymentTransaction cho các đơn hàng COD chưa có bản ghi giao dịch';

    public function handle(): int
    {
        $orders = Order::where('payment_method', 'cod')
            ->whereDoesntHave('transactions')
            ->get();

        $count = 0;
        foreach ($orders as $order) {
            PaymentTransaction::create([
                'order_id' => $order->id,
                'transaction_id' => 'COD-ORD-' . $order->id,
                'amount' => $order->total_amount,
                'payment_method' => 'cod',
                'status' => $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'failed' ? 'failed' : 'pending'),
                'note' => 'Đồng bộ tự động từ hệ thống',
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ]);
            $count++;
        }

        $this->info("Đã đồng bộ giao dịch cho các đơn hàng COD: {$count} đơn.");
        return 0;
    }
}

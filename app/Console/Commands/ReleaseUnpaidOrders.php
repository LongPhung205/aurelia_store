<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PayOS\PayOS;

class ReleaseUnpaidOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:release-unpaid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Release stock and cancel unpaid PayOS orders after 30 minutes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting release of unpaid orders...');

        $payOS = new PayOS(
            env('PAYOS_CLIENT_ID'),
            env('PAYOS_API_KEY'),
            env('PAYOS_CHECKSUM_KEY')
        );

        // Find orders created more than 30 minutes ago that are still pending
        $orders = Order::where('payment_method', 'payos')
            ->where('payment_status', 'pending')
            ->where('created_at', '<', now()->subMinutes(30))
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No unpaid orders found.');
            return;
        }

        foreach ($orders as $order) {
            $this->info("Processing order #{$order->id} (Code: {$order->shipping_order_code})");

            try {
                // 1. Double check with PayOS API to see if it was actually paid (Webhook missed/delayed)
                if ($order->shipping_order_code) {
                    try {
                        $paymentInfo = $payOS->getPaymentLinkInformation($order->shipping_order_code);
                        
                        if ($paymentInfo && isset($paymentInfo['status']) && $paymentInfo['status'] === 'PAID') {
                            $this->info("Order #{$order->id} was actually paid. Updating status and skipping cancellation.");
                            $order->update([
                                'payment_status' => 'paid',
                                'status' => '1' // Confirmed
                            ]);
                            continue; // Skip cancellation
                        }
                    } catch (\Exception $e) {
                        // PayOS might throw exception if orderCode not found or invalid
                        Log::warning("PayOS API check failed for Order #{$order->id}: " . $e->getMessage());
                    }
                }

                // 2. If definitely not paid, cancel order and restore stock safely
                DB::transaction(function () use ($order) {
                    // Update order status first
                    $order->update([
                        'status' => 'cancelled',
                        'payment_status' => 'failed'
                    ]);

                    // Restore stock for each item using lockForUpdate
                    foreach ($order->items as $item) {
                        $variant = ProductVariant::where('id', $item->product_variant_id)
                            ->lockForUpdate()
                            ->first();

                        if ($variant) {
                            $variant->increment('stock_quantity', $item->quantity);
                        }
                    }
                });

                $this->info("Order #{$order->id} cancelled and stock restored successfully.");

            } catch (\Exception $e) {
                Log::error("Failed to release order #{$order->id}: " . $e->getMessage());
                $this->error("Failed to release order #{$order->id}: " . $e->getMessage());
            }
        }

        $this->info('Finished releasing unpaid orders.');
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\ProductVariant;
use Illuminate\Console\Command;
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

        $payOS = app()->bound(PayOS::class) ? app(PayOS::class) : new PayOS(
            config('services.payos.client_id'),
            config('services.payos.api_key'),
            config('services.payos.checksum_key')
        );

        // Find orders created more than 30 minutes ago that are still pending
        $orders = Order::whereIn('payment_method', ['payos', 'momo'])
            ->where('payment_status', 'pending')
            ->where('created_at', '<', now()->subMinutes(30))
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No unpaid orders found.');

            return;
        }

        foreach ($orders as $order) {
            $this->info("Processing order #{$order->id} (Method: {$order->payment_method})");

            try {
                if ($order->payment_method === 'payos') {
                    $payosTransaction = PaymentTransaction::where('order_id', $order->id)
                        ->where('payment_method', 'payos')
                        ->latest()
                        ->first();
                    $payosOrderCode = $payosTransaction ? $payosTransaction->transaction_id : $order->shipping_order_code;

                    if ($payosOrderCode) {
                        try {
                            $paymentInfo = $payOS->getPaymentLinkInformation((int) $payosOrderCode);

                            if ($paymentInfo && isset($paymentInfo['status']) && $paymentInfo['status'] === 'PAID') {
                                $this->info("Order #{$order->id} was actually paid. Updating status and skipping cancellation.");
                                $order->update([
                                    'payment_status' => 'paid',
                                    'status' => 'processing',
                                    'is_inventory_deducted' => true,
                                ]);
                                if ($payosTransaction) {
                                    $payosTransaction->update([
                                        'status' => 'success',
                                        'response_data' => $paymentInfo,
                                    ]);
                                }

                                continue; // Skip cancellation
                            }
                        } catch (\Throwable $e) {
                            Log::warning("PayOS API check failed for Order #{$order->id}: ".$e->getMessage());

                            continue;
                        }
                    }
                }

                // 2. If definitely not paid, cancel order and restore stock safely
                DB::transaction(function () use ($order) {
                    $order = Order::where('id', $order->id)->lockForUpdate()->first();
                    if (! $order || $order->payment_status === 'paid') {
                        return;
                    }

                    // Update order status first
                    $order->update([
                        'status' => 'cancelled',
                        'payment_status' => 'failed',
                        'is_inventory_deducted' => false,
                    ]);

                    // Restore stock for each item using lockForUpdate
                    foreach ($order->items as $item) {
                        $variant = ProductVariant::where('id', $item->product_variant_id)
                            ->lockForUpdate()
                            ->first();

                        if ($variant) {
                            $stockBefore = $variant->stock_quantity;
                            $variant->increment('stock_quantity', $item->quantity);
                            $stockAfter = $stockBefore + $item->quantity;

                            // Ghi log hoàn trả (thẻ kho type 'import')
                            \App\Models\InventoryHistory::create([
                                'product_variant_id' => $variant->id,
                                'reference_type' => \App\Models\Order::class,
                                'reference_id' => $order->id,
                                'type' => 'import',
                                'quantity_changed' => $item->quantity,
                                'stock_before' => $stockBefore,
                                'stock_after' => $stockAfter,
                                'user_id' => null, // Hệ thống tự động
                                'note' => 'Nhập lại kho do hủy/hoàn đơn hàng ORD-' . $order->id . ' (Quá hạn thanh toán)',
                            ]);
                        }
                    }
                });

                $this->info("Order #{$order->id} cancelled and stock restored successfully.");

            } catch (\Exception $e) {
                Log::error("Failed to release order #{$order->id}: ".$e->getMessage());
                $this->error("Failed to release order #{$order->id}: ".$e->getMessage());
            }
        }

        $this->info('Finished releasing unpaid orders.');
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\InventoryHistory;

class SyncOrderInventoryHistoryCommand extends Command
{
    protected $signature = 'app:sync-order-inventory-history';
    protected $description = 'Đồng bộ tạo thẻ xuất kho (InventoryHistory) cho các đơn hàng đã trừ kho mà chưa có lịch sử';

    public function handle(): int
    {
        $orders = Order::with('items.productVariant')->where('is_inventory_deducted', true)->get();
        $synced = 0;

        foreach ($orders as $order) {
            $hasHistory = InventoryHistory::where('reference_type', Order::class)
                ->where('reference_id', $order->id)
                ->where('type', 'export')
                ->exists();

            if (!$hasHistory && $order->items->isNotEmpty()) {
                foreach ($order->items as $item) {
                    $variant = $item->productVariant;
                    if ($variant) {
                        InventoryHistory::create([
                            'product_variant_id' => $variant->id,
                            'reference_type' => Order::class,
                            'reference_id' => $order->id,
                            'type' => 'export',
                            'quantity_changed' => -$item->quantity,
                            'stock_before' => $variant->stock_quantity + $item->quantity,
                            'stock_after' => $variant->stock_quantity,
                            'user_id' => $order->user_id,
                            'note' => 'Xuất kho cho đơn hàng ORD-' . $order->id,
                            'created_at' => $order->created_at,
                            'updated_at' => $order->created_at,
                        ]);
                    }
                }
                $synced++;
            }
        }

        $this->info("Đã đồng bộ thẻ xuất kho cho {$synced} đơn hàng.");
        return 0;
    }
}

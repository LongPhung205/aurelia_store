<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\InventoryHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncOrderInventoryHistoryCommand extends Command
{
    protected $signature = 'app:sync-order-inventory-history';
    protected $description = 'Đồng bộ tạo thẻ xuất kho (InventoryHistory) cho các đơn hàng đã trừ kho mà chưa có lịch sử';

    public function handle(): int
    {
        $this->info("Bắt đầu kiểm tra và đồng bộ thẻ xuất kho...");

        // Lấy admin đầu tiên làm người đại diện thao tác hệ thống (nếu có)
        $systemAdminId = User::where('role', 'admin')->value('id');

        $ordersQuery = Order::with(['items.productVariant'])
            ->whereIn('status', ['shipping', 'completed']);

        $synced = 0;
        $totalOrders = $ordersQuery->count();

        if ($totalOrders > 0) {
            $this->output->progressStart($totalOrders);
        }

        // Dùng chunkById để tối ưu bộ nhớ
        $ordersQuery->chunkById(100, function ($orders) use (&$synced, $systemAdminId) {
            foreach ($orders as $order) {
                $hasHistory = InventoryHistory::where('reference_type', Order::class)
                    ->where('reference_id', $order->id)
                    ->where('type', 'export')
                    ->exists();

                if (!$hasHistory && $order->items->isNotEmpty()) {
                    try {
                        DB::transaction(function () use ($order, $systemAdminId) {
                            foreach ($order->items as $item) {
                                $variant = $item->productVariant;
                                if ($variant) {
                                    InventoryHistory::create([
                                        'product_variant_id' => $variant->id,
                                        'reference_type'    => Order::class,
                                        'reference_id'      => $order->id,
                                        'type'              => 'export',
                                        'quantity_changed'  => -$item->quantity,
                                        'stock_before'      => $variant->stock_quantity + $item->quantity,
                                        'stock_after'       => $variant->stock_quantity,
                                        'user_id'           => $systemAdminId,
                                        'note'              => 'Xuất kho cho đơn hàng ORD-' . $order->id . ' (Đồng bộ bổ sung)',
                                        'created_at'        => $order->created_at,
                                        'updated_at'        => $order->created_at,
                                    ]);
                                }
                            }

                            // Cập nhật cờ để tránh hệ thống trừ kho thêm một lần nữa
                            $order->update(['is_inventory_deducted' => true]);
                        });

                        $synced++;
                    } catch (\Exception $e) {
                        Log::error("Lỗi đồng bộ thẻ kho cho đơn #{$order->id}: " . $e->getMessage());
                    }
                } elseif (!$order->is_inventory_deducted) {
                    // Nếu đã có thẻ kho rồi nhưng cờ chưa bật, bật cờ để thống nhất logic
                    $order->update(['is_inventory_deducted' => true]);
                }

                $this->output->progressAdvance();
            }
        });

        if ($totalOrders > 0) {
            $this->output->progressFinish();
        }
        $this->info("Đã đồng bộ thẻ xuất kho thành công cho {$synced} đơn hàng.");
        
        return 0;
    }
}

<?php

namespace App\Services;

use App\Models\Order;
use App\Models\InventoryHistory;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryService
{
    /**
     * Deduct inventory for an order and record history.
     */
    public function deductForOrder(Order $order, $userId = null)
    {
        if ($order->is_inventory_deducted) {
            return true;
        }

        DB::beginTransaction();

        try {
            foreach ($order->items as $item) {
                $variant = $item->productVariant;

                if (!$variant) {
                    continue; // Variant might have been deleted, skip or throw error depending on business logic
                }

                if ($variant->stock_quantity < $item->quantity) {
                    throw new Exception("Sản phẩm {$item->product_name} không đủ tồn kho (Còn lại: {$variant->stock_quantity}, Cần: {$item->quantity}).");
                }

                $stockBefore = $variant->stock_quantity;
                $stockAfter = $stockBefore - $item->quantity;

                // Update variant stock
                $variant->update(['stock_quantity' => $stockAfter]);

                // Record history
                InventoryHistory::create([
                    'product_variant_id' => $variant->id,
                    'reference_type' => Order::class,
                    'reference_id' => $order->id,
                    'type' => 'export',
                    'quantity_changed' => -$item->quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'user_id' => $userId ?? auth()->id(),
                    'note' => 'Xuất kho cho đơn hàng ORD-' . $order->id,
                ]);
            }

            $order->update(['is_inventory_deducted' => true]);

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Restock inventory for a cancelled/returned order and record history.
     */
    public function restockForOrder(Order $order, $userId = null)
    {
        if (!$order->is_inventory_deducted) {
            return true;
        }

        DB::beginTransaction();

        try {
            foreach ($order->items as $item) {
                $variant = $item->productVariant;

                if (!$variant) {
                    continue;
                }

                $stockBefore = $variant->stock_quantity;
                $stockAfter = $stockBefore + $item->quantity;

                // Update variant stock
                $variant->update(['stock_quantity' => $stockAfter]);

                // Record history
                InventoryHistory::create([
                    'product_variant_id' => $variant->id,
                    'reference_type' => Order::class,
                    'reference_id' => $order->id,
                    'type' => 'import',
                    'quantity_changed' => $item->quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'user_id' => $userId ?? auth()->id(),
                    'note' => 'Nhập lại kho do hủy/hoàn đơn hàng ORD-' . $order->id,
                ]);
            }

            $order->update(['is_inventory_deducted' => false]);

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

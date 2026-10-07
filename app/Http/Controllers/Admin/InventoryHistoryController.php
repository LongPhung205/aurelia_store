<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryHistory;
use Illuminate\Http\Request;

class InventoryHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryHistory::select(
            'reference_type',
            'reference_id',
            'type',
            'user_id',
            \DB::raw('MAX(created_at) as created_at'),
            \DB::raw('SUM(quantity_changed) as total_quantity'),
            \DB::raw('COUNT(DISTINCT product_variant_id) as variant_count'),
            \DB::raw('MAX(note) as note'),
            \DB::raw('IF(reference_id IS NULL, id, 0) as group_id')
        )
        ->with(['reference', 'user'])
        ->groupBy('reference_type', 'reference_id', 'type', 'user_id', 'group_id')
        ->orderBy('created_at', 'desc');

        // Fetch users who have inventory history for the filter
        $users = \App\Models\User::whereHas('inventoryHistories')->get();
            
        // Chỉ hiển thị thẻ xuất kho của đơn hàng khi đơn hàng đang ở trạng thái 'shipping' (Đang giao hàng) hoặc 'completed' (Hoàn thành)
        $visibleFilter = function($q) {
            $q->where('reference_type', '!=', \App\Models\Order::class)
              ->orWhereNull('reference_type')
              ->orWhereExists(function ($sub) {
                  $sub->select(\DB::raw(1))
                      ->from('orders')
                      ->whereColumn('orders.id', 'inventory_histories.reference_id')
                      ->whereIn('orders.status', ['shipping', 'completed']);
              });
        };

        $query->where($visibleFilter);

        if ($request->filled('type') && in_array($request->type, ['import', 'export', 'adjustment'])) {
            $query->where('type', $request->type);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', \Carbon\Carbon::parse($request->date_from)->startOfDay());
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', \Carbon\Carbon::parse($request->date_to)->endOfDay());
        }

        if ($request->filled('sku')) {
            $query->whereHas('variant', function($q) use ($request) {
                $q->where('sku', 'like', '%' . $request->sku . '%');
            });
        }

        $histories = $query->paginate(20)->appends($request->all());

        $counts = [
            'all' => InventoryHistory::where($visibleFilter)->count(),
            'import' => InventoryHistory::where('type', 'import')->where($visibleFilter)->count(),
            'export' => InventoryHistory::where('type', 'export')->where($visibleFilter)->count(),
            'adjustment' => InventoryHistory::where('type', 'adjustment')->where($visibleFilter)->count(),
        ];

        foreach ($histories as $history) {
            $history->preview_variants = collect();
            if ($history->reference_type && $history->reference_id) {
                $previewHistories = InventoryHistory::with('variant.product.images')
                    ->where('reference_type', $history->reference_type)
                    ->where('reference_id', $history->reference_id)
                    ->get();
                
                $uniqueProducts = $previewHistories->map->variant->unique('product_id');
                $history->product_count = $uniqueProducts->count();
                $history->preview_variants = $uniqueProducts->take(3);
            } else {
                $history->product_count = 1;
                $individual = InventoryHistory::with('variant.product.images')->find($history->group_id);
                if ($individual && $individual->variant) {
                    $history->preview_variants->push($individual->variant);
                }
            }
        }

        return view('admin.inventory_history.index', compact('histories', 'counts', 'users'));
    }

    public function details(Request $request)
    {
        $query = InventoryHistory::with(['variant.product.images', 'variant.color', 'variant.size', 'user', 'reference']);
        
        if ($request->filled('reference_type') && $request->filled('reference_id')) {
            $query->where('reference_type', $request->reference_type)
                  ->where('reference_id', $request->reference_id);
        } else {
            // Trường hợp không có reference, fallback về id
            $query->where('id', $request->id);
        }

        $details = $query->orderBy('id', 'desc')->get();
        return view('admin.inventory_history.partials.details', compact('details'));
    }
}

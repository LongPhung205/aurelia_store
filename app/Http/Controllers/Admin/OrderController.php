<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\GhnService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query();

        // 1. Lọc theo trạng thái đơn hàng
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 2. Tìm kiếm (Mã đơn hàng, Tên khách hàng, Số điện thoại)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                // Giả sử mã đơn hàng bắt đầu bằng 'ORD-', ta có thể lấy phần số hoặc tìm chính xác id
                if (stripos($search, 'ORD-') === 0) {
                    $id = substr($search, 4);
                    $q->where('id', $id);
                } else {
                    $q->where('id', $search)
                      ->orWhere('customer_name', 'like', "%{$search}%")
                      ->orWhere('customer_phone', 'like', "%{$search}%");
                }
            });
        }

        // 3. Lọc theo khoảng thời gian
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15)->appends($request->all());

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']);
        return view('admin.orders.show', compact('order'));
    }

    public function syncGhnStatus(Order $order, GhnService $ghnService, \App\Services\InventoryService $inventoryService)
    {
        if (!$order->shipping_order_code) {
            return redirect()->back()->with('error', 'Đơn hàng này chưa có mã vận đơn GHN.');
        }

        $ghnResponse = $ghnService->getOrderInfo($order->shipping_order_code);

        if ($ghnResponse['success']) {
            $status = $ghnResponse['status'];
            // Cập nhật trạng thái GHN
            $order->update(['shipping_status' => $status]);
            
            try {
                // Tự động xuất kho nếu GHN đang giao hoặc đã giao
                if (in_array($status, ['delivering', 'delivered'])) {
                    $inventoryService->deductForOrder($order);
                }
                
                // Có thể tự động update trạng thái đơn nội bộ dựa trên GHN
                if (in_array($status, ['delivered'])) {
                    $order->update(['status' => 'completed', 'payment_status' => 'paid']);
                } elseif (in_array($status, ['return', 'cancel'])) {
                    $order->update(['status' => 'cancelled']);
                    $inventoryService->restockForOrder($order);
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Lỗi xử lý kho: ' . $e->getMessage());
            }
            
            return redirect()->back()->with('success', 'Đã đồng bộ trạng thái: ' . $status);
        }

        return redirect()->back()->with('error', 'Không thể đồng bộ với GHN lúc này.');
    }

    public function updateStatus(Request $request, Order $order, \App\Services\InventoryService $inventoryService)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,ready_to_pick,shipping,completed,cancelled'
        ]);

        try {
            if (in_array($request->status, ['shipping', 'completed'])) {
                $inventoryService->deductForOrder($order);
            } elseif ($request->status == 'cancelled') {
                $inventoryService->restockForOrder($order);
            }
            
            $order->update(['status' => $request->status]);

            // Nếu chuyển sang completed thì tự động cập nhật thanh toán
            if ($request->status == 'completed' && $order->payment_status != 'paid') {
                $order->update(['payment_status' => 'paid']);
            }

            return redirect()->back()->with('success', 'Đã cập nhật trạng thái đơn hàng thành công.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi xử lý kho: ' . $e->getMessage());
        }
    }

    public function confirmAndCreateGhn(Order $order, GhnService $ghnService)
    {
        if ($order->shipping_order_code) {
            return redirect()->back()->with('error', 'Đơn hàng này đã có mã vận đơn.');
        }

        $ghnItems = [];
        foreach ($order->items as $item) {
            $ghnItems[] = [
                'name' => $item->product_name,
                'quantity' => $item->quantity,
                'price' => (int)$item->price,
                'weight' => 200 // Mặc định 200g mỗi sản phẩm
            ];
        }

        $ghnOrderData = [
            'payment_type_id' => 2, // COD
            'note' => $order->note ?? '',
            'required_note' => 'CHOXEMHANGKHONGTHU',
            'return_phone' => '0339999999', // SĐT Shop
            'return_address' => 'Hà Nội',
            'return_district_id' => (int)env('GHN_FROM_DISTRICT_ID'),
            'return_ward_code' => (string)env('GHN_FROM_WARD_CODE'),
            'client_order_code' => 'ORD-' . $order->id,
            'to_name' => $order->customer_name,
            'to_phone' => $order->customer_phone,
            'to_address' => $order->address,
            'to_ward_code' => (string)$order->ward_code,
            'to_district_id' => (int)$order->district_id,
            'cod_amount' => (int)$order->total_amount,
            'content' => 'Sản phẩm thời trang',
            'weight' => count($ghnItems) * 200,
            'length' => 20,
            'width' => 20,
            'height' => 10,
            'pick_station_id' => null,
            'insurance_value' => (int)$order->subtotal,
            'service_id' => 53320,
            'service_type_id' => 2,
            'items' => $ghnItems
        ];

        $ghnResponse = $ghnService->createOrder($ghnOrderData);

        if ($ghnResponse['success']) {
            $order->update([
                'shipping_order_code' => $ghnResponse['order_code'],
                'shipping_status' => 'ready_to_pick',
                'status' => 'ready_to_pick' // Cập nhật sang Đã chuẩn bị hàng
            ]);
            return redirect()->back()->with('success', 'Đã xác nhận đơn và tạo mã vận đơn GHN: ' . $ghnResponse['order_code']);
        }

        \Illuminate\Support\Facades\Log::error('Create GHN Order Error: ' . $ghnResponse['message']);
        return redirect()->back()->with('error', 'Lỗi khi tạo đơn GHN: ' . $ghnResponse['message']);
    }
}

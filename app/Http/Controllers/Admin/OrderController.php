<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\GhnService;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order, InventoryService $inventoryService)
    {
        $newStatus = $request->validated()['status'];

        $levels = [
            'pending' => 1,
            'processing' => 2,
            'ready_to_pick' => 3,
            'shipping' => 4,
            'completed' => 5,
            'cancelled' => 6
        ];

        // Prevent reversing from shipping or completed to an earlier state
        if ($levels[$order->status] >= 4 && $levels[$newStatus] < $levels[$order->status]) {
            return redirect()->back()->with('error', 'Không thể lùi trạng thái đơn hàng khi đã ở mức Đang giao hoặc Hoàn thành.');
        }

        try {
            DB::transaction(function () use ($order, $newStatus, $inventoryService) {
                if (in_array($newStatus, ['shipping', 'completed'])) {
                    $inventoryService->deductForOrder($order);
                } elseif ($newStatus === 'cancelled') {
                    $inventoryService->restockForOrder($order);
                }
                
                $order->update(['status' => $newStatus]);

                // Nếu chuyển sang completed thì tự động cập nhật thanh toán
                if ($newStatus === 'completed' && $order->payment_status !== 'paid') {
                    $order->update(['payment_status' => 'paid']);
                }
            });

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
            'return_district_id' => (int)config('services.ghn.from_district_id'),
            'return_ward_code' => (string)config('services.ghn.from_ward_code'),
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

    public function bulkAction(Request $request, InventoryService $inventoryService, GhnService $ghnService)
    {
        $action = $request->input('action');
        $orderIds = $request->input('order_ids', []);

        if (empty($orderIds)) {
            return redirect()->back()->with('error', 'Vui lòng chọn ít nhất 1 đơn hàng.');
        }

        $orders = Order::whereIn('id', $orderIds)->get();
        $successCount = 0;
        $errorMessages = [];

        foreach ($orders as $order) {
            try {
                DB::transaction(function () use ($order, $action, $inventoryService, $ghnService) {
                    if ($action === 'processing' && $order->status === 'pending') {
                        $order->update(['status' => 'processing']);
                    } 
                    elseif ($action === 'ready_to_pick') {
                        if (!in_array($order->status, ['shipping', 'completed', 'cancelled'])) {
                            $order->update(['status' => 'ready_to_pick']);
                        }
                    } 
                    elseif ($action === 'push_ghn') {
                        if (!$order->shipping_order_code) {
                            $ghnItems = [];
                            foreach ($order->items as $item) {
                                $ghnItems[] = [
                                    'name' => $item->product_name,
                                    'quantity' => $item->quantity,
                                    'price' => (int)$item->price,
                                    'weight' => 200
                                ];
                            }
                            $ghnOrderData = [
                                'payment_type_id' => 2,
                                'required_note' => 'CHOXEMHANGKHONGTHU',
                                'return_phone' => '0339999999',
                                'return_address' => 'Hà Nội',
                                'return_district_id' => (int)config('services.ghn.from_district_id'),
                                'return_ward_code' => (string)config('services.ghn.from_ward_code'),
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
                                'service_id' => 53320,
                                'service_type_id' => 2,
                                'items' => $ghnItems
                            ];
                            $ghnResponse = $ghnService->createOrder($ghnOrderData);
                            if ($ghnResponse['success']) {
                                $order->update([
                                    'shipping_order_code' => $ghnResponse['order_code'],
                                    'shipping_status' => 'ready_to_pick',
                                    'status' => 'ready_to_pick'
                                ]);
                            } else {
                                throw new \Exception('Lỗi GHN đơn ' . $order->id . ': ' . $ghnResponse['message']);
                            }
                        }
                    }
                });
                $successCount++;
            } catch (\Exception $e) {
                $errorMessages[] = $e->getMessage();
            }
        }

        $msg = "Đã xử lý thành công $successCount đơn hàng.";
        if (count($errorMessages) > 0) {
            $msg .= " Có lỗi: " . implode('; ', $errorMessages);
            return redirect()->back()->with('warning', $msg);
        }

        return redirect()->back()->with('success', $msg);
    }

    public function bulkPrint(Request $request, GhnService $ghnService)
    {
        $orderIds = $request->input('order_ids', []);
        
        if (empty($orderIds)) {
            return redirect()->back()->with('error', 'Vui lòng chọn ít nhất 1 đơn hàng để in.');
        }

        $orders = Order::with(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size'])
                       ->whereIn('id', $orderIds)
                       ->get();

        // Resolve addresses for all orders
        foreach ($orders as $order) {
            $provinceName = '';
            $districtName = '';
            $wardName = '';
            
            if ($order->province_id) {
                $p = collect($ghnService->getProvinces())->firstWhere('ProvinceID', $order->province_id);
                $provinceName = $p ? $p['ProvinceName'] : '';
            }
            if ($order->district_id) {
                $d = collect($ghnService->getDistricts($order->province_id))->firstWhere('DistrictID', $order->district_id);
                $districtName = $d ? $d['DistrictName'] : '';
            }
            if ($order->ward_code) {
                $w = collect($ghnService->getWards($order->district_id))->firstWhere('WardCode', $order->ward_code);
                $wardName = $w ? $w['WardName'] : '';
            }
            $order->ghnAddressStr = collect([$wardName, $districtName, $provinceName])->filter()->implode(', ');
        }

        return view('admin.orders.bulk_print', compact('orders'));
    }
}

<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreAddressRequest;
use App\Http\Requests\Client\UpdateAddressRequest;
use App\Http\Requests\Client\UpdatePasswordRequest;
use App\Http\Requests\Client\UpdateProfileRequest;
use App\Models\Order;
use App\Models\UserAddress;
use App\Services\GhnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Hiển thị trang Hồ sơ cá nhân
     */
    public function index()
    {
        $user = auth()->user();
        return view('client.profile.index', compact('user'));
    }

    /**
     * Cập nhật thông tin cá nhân
     */
    public function update(UpdateProfileRequest $request)
    {
        $user = auth()->user();
        $validated = $request->validated();

        // Xử lý upload avatar
        if ($request->hasFile('avatar')) {
            // Xóa ảnh cũ nếu có
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        $user->update($validated);

        return back()->with('success', 'Thông tin cá nhân đã được cập nhật thành công.');
    }

    /**
     * Hiển thị trang Đổi mật khẩu
     */
    public function password()
    {
        return view('client.profile.password');
    }

    /**
     * Cập nhật mật khẩu
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $validated = $request->validated();

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Mật khẩu đã được thay đổi thành công.');
    }

    /**
     * Quản lý Sổ địa chỉ
     */
    public function addresses(GhnService $ghnService)
    {
        $addresses = auth()->user()->addresses()->orderByDesc('is_default')->get();
        $provinces = $ghnService->getProvinces();
        return view('client.profile.addresses', compact('addresses', 'provinces'));
    }

    public function storeAddress(StoreAddressRequest $request)
    {
        $validated = $request->validated();
        $user = auth()->user();

        // Nếu là địa chỉ đầu tiên hoặc được đánh dấu là mặc định
        if ($user->addresses()->count() === 0 || $request->has('is_default')) {
            $user->addresses()->update(['is_default' => false]);
            $validated['is_default'] = true;
        }

        $user->addresses()->create($validated);

        return back()->with('success', 'Thêm địa chỉ mới thành công.');
    }

    public function updateAddress(UpdateAddressRequest $request, UserAddress $address)
    {
        if ($address->user_id !== auth()->id()) abort(403);

        $address->update($request->validated());

        return back()->with('success', 'Cập nhật địa chỉ thành công.');
    }

    public function destroyAddress(UserAddress $address)
    {
        if ($address->user_id !== auth()->id()) abort(403);
        $address->delete();
        return back()->with('success', 'Đã xóa địa chỉ.');
    }

    public function setDefaultAddress(UserAddress $address)
    {
        if ($address->user_id !== auth()->id()) abort(403);
        
        auth()->user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()->with('success', 'Đã đặt làm địa chỉ mặc định.');
    }

    /**
     * Quản lý Đơn hàng
     */
    public function orders(Request $request)
    {
        $query = auth()->user()->orders();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $orders = $query->with('items')->orderBy('created_at', 'desc')->paginate(10);
        
        return view('client.profile.orders', compact('orders'));
    }

    public function showOrder(Order $order)
    {
        $isOwner = auth()->check() && $order->user_id === auth()->id();
        $isSessionGuest = session('last_order_id') == $order->id;

        if (!$isOwner && !$isSessionGuest && $order->user_id) {
            abort(403);
        }
        
        $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']);
        return view('client.profile.order_show', compact('order'));
    }

    /**
     * Hủy đơn hàng khi đang ở trạng thái 'pending' (Chờ xác nhận)
     */
    public function cancelOrder(Request $request, Order $order, \App\Services\InventoryService $inventoryService)
    {
        $isOwner = auth()->check() && $order->user_id === auth()->id();
        $isSessionGuest = session('last_order_id') == $order->id;

        if (!$isOwner && !$isSessionGuest) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        // Chỉ cho phép hủy khi đơn hàng đang ở trạng thái Chờ xác nhận
        if ($order->status !== 'pending') {
            return redirect()->back()->with('error', 'Chỉ có thể hủy đơn hàng khi đơn hàng đang ở trạng thái "Chờ xác nhận".');
        }

        $reason = $request->input('cancel_reason', 'Khách hàng yêu cầu hủy đơn');

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($order, $reason, $inventoryService) {
                $noteUpdate = $order->note ? ($order->note . " | [Lý do hủy: {$reason}]") : "[Lý do hủy: {$reason}]";

                $order->update([
                    'status' => 'cancelled',
                    'note' => $noteUpdate,
                ]);

                // Hoàn lại tồn kho cho các biến thể trong đơn
                $inventoryService->restockForOrder($order, auth()->id());

                // Hoàn lại lượt sử dụng mã giảm giá nếu có
                if ($order->coupon_id) {
                    \App\Models\Coupon::where('id', $order->coupon_id)->decrement('used_count');
                }
            });

            return redirect()->back()->with('success', "Đơn hàng #ORD-{$order->id} đã được hủy thành công. Tồn kho sản phẩm đã được hoàn lại.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Cancel Order Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi hủy đơn hàng: ' . $e->getMessage());
        }
    }
}

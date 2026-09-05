<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

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
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Mật khẩu đã được thay đổi thành công.');
    }

    /**
     * Quản lý Sổ địa chỉ
     */
    public function addresses(\App\Services\GhnService $ghnService)
    {
        $addresses = auth()->user()->addresses()->orderByDesc('is_default')->get();
        $provinces = $ghnService->getProvinces();
        return view('client.profile.addresses', compact('addresses', 'provinces'));
    }

    public function storeAddress(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'province_id' => 'required|string',
            'district_id' => 'required|string',
            'ward_code' => 'required|string',
            'address' => 'required|string|max:255',
            'is_default' => 'nullable|boolean',
        ]);

        $user = auth()->user();

        // Nếu là địa chỉ đầu tiên hoặc được đánh dấu là mặc định
        if ($user->addresses()->count() === 0 || $request->has('is_default')) {
            $user->addresses()->update(['is_default' => false]);
            $validated['is_default'] = true;
        }

        $user->addresses()->create($validated);

        return back()->with('success', 'Thêm địa chỉ mới thành công.');
    }

    public function updateAddress(Request $request, \App\Models\UserAddress $address)
    {
        if ($address->user_id !== auth()->id()) abort(403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'province_id' => 'required|string',
            'district_id' => 'required|string',
            'ward_code' => 'required|string',
            'address' => 'required|string|max:255',
        ]);

        $address->update($validated);

        return back()->with('success', 'Cập nhật địa chỉ thành công.');
    }

    public function destroyAddress(\App\Models\UserAddress $address)
    {
        if ($address->user_id !== auth()->id()) abort(403);
        $address->delete();
        return back()->with('success', 'Đã xóa địa chỉ.');
    }

    public function setDefaultAddress(\App\Models\UserAddress $address)
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

    public function showOrder(\App\Models\Order $order)
    {
        if ($order->user_id !== auth()->id()) abort(403);
        
        $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']);
        return view('client.profile.order_show', compact('order'));
    }
}

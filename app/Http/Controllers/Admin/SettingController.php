<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingRequest;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $productFrame = Setting::get('product_frame', 'images/White Brown Abstract Border Frame Blank Document A4.png');
        return view('admin.settings.index', compact('productFrame'));
    }

    public function update(UpdateSettingRequest $request)
    {
        if ($request->hasFile('product_frame')) {
            // Store the new image in storage/app/public/settings
            $path = $request->file('product_frame')->store('settings', 'public');
            Setting::set('product_frame', 'storage/' . $path);
        }

        return redirect()->route('admin.settings.index')->with('success', 'Cài đặt đã được cập nhật thành công!');
    }
}

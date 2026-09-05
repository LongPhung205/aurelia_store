<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Size;
use App\Http\Requests\Admin\StoreSizeRequest;
use App\Http\Requests\Admin\UpdateSizeRequest;

class SizeController extends Controller
{
    public function index()
    {
        $sizes = Size::paginate(10);
        return view('admin.sizes.index', compact('sizes'));
    }

    public function create()
    {
        return view('admin.sizes.create');
    }

    public function store(StoreSizeRequest $request)
    {
        Size::create($request->validated());
        return redirect()->route('admin.attributes.index', ['tab' => 'sizes'])->with('success', 'Kích cỡ đã được thêm.');
    }

    public function show(Size $size)
    {
        return view('admin.sizes.show', compact('size'));
    }

    public function edit(Size $size)
    {
        return view('admin.sizes.edit', compact('size'));
    }

    public function update(UpdateSizeRequest $request, Size $size)
    {
        $size->update($request->validated());
        return redirect()->route('admin.attributes.index', ['tab' => 'sizes'])->with('success', 'Kích cỡ đã được cập nhật.');
    }

    public function destroy(Size $size)
    {
        $size->delete();
        return redirect()->route('admin.attributes.index', ['tab' => 'sizes'])->with('success', 'Kích cỡ đã được xóa.');
    }
}


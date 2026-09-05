<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Color;
use App\Http\Requests\Admin\StoreColorRequest;
use App\Http\Requests\Admin\UpdateColorRequest;

class ColorController extends Controller
{
    public function index()
    {
        $colors = Color::paginate(10);
        return view('admin.colors.index', compact('colors'));
    }

    public function create()
    {
        return view('admin.colors.create');
    }

    public function store(StoreColorRequest $request)
    {
        Color::create($request->validated());
        return redirect()->route('admin.attributes.index', ['tab' => 'colors'])->with('success', 'Màu sắc đã được thêm.');
    }

    public function show(Color $color)
    {
        return view('admin.colors.show', compact('color'));
    }

    public function edit(Color $color)
    {
        return view('admin.colors.edit', compact('color'));
    }

    public function update(UpdateColorRequest $request, Color $color)
    {
        $color->update($request->validated());
        return redirect()->route('admin.attributes.index', ['tab' => 'colors'])->with('success', 'Màu sắc đã được cập nhật.');
    }

    public function destroy(Color $color)
    {
        $color->delete();
        return redirect()->route('admin.attributes.index', ['tab' => 'colors'])->with('success', 'Màu sắc đã được xóa.');
    }
}


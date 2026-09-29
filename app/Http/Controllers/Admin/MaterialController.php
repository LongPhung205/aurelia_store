<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMaterialRequest;
use App\Http\Requests\Admin\UpdateMaterialRequest;
use App\Models\Material;

class MaterialController extends Controller
{
    public function store(StoreMaterialRequest $request)
    {
        Material::create($request->validated());
        return redirect()->route('admin.attributes.index', ['tab' => 'materials'])->with('success', 'Chất liệu đã được thêm.');
    }

    public function update(UpdateMaterialRequest $request, Material $material)
    {
        $material->update($request->validated());
        return redirect()->route('admin.attributes.index', ['tab' => 'materials'])->with('success', 'Chất liệu đã được cập nhật.');
    }

    public function destroy(Material $material)
    {
        $material->delete();
        return redirect()->route('admin.attributes.index', ['tab' => 'materials'])->with('success', 'Chất liệu đã được xóa.');
    }
}

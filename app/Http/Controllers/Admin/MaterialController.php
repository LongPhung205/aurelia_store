<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:materials,name',
            'description' => 'nullable|string',
        ]);
        
        Material::create($validated);
        return redirect()->route('admin.attributes.index', ['tab' => 'materials'])->with('success', 'Chất liệu đã được thêm.');
    }

    public function update(Request $request, Material $material)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:materials,name,' . $material->id,
            'description' => 'nullable|string',
        ]);
        
        $material->update($validated);
        return redirect()->route('admin.attributes.index', ['tab' => 'materials'])->with('success', 'Chất liệu đã được cập nhật.');
    }

    public function destroy(Material $material)
    {
        $material->delete();
        return redirect()->route('admin.attributes.index', ['tab' => 'materials'])->with('success', 'Chất liệu đã được xóa.');
    }
}

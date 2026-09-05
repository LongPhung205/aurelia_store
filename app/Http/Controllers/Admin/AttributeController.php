<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Models\Size;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    public function index(Request $request)
    {
        $colors = Color::latest()->paginate(10, ['*'], 'color_page');
        $sizes = Size::latest()->paginate(10, ['*'], 'size_page');
        $materials = \App\Models\Material::latest()->paginate(10, ['*'], 'material_page');
        
        $activeTab = $request->get('tab', session('tab', 'colors'));
        
        return view('admin.attributes.index', compact('colors', 'sizes', 'materials', 'activeTab'));
    }
}

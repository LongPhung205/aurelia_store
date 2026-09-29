<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Category;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::whereNull('parent_id')
            ->with(['children.children.children.children'])
            ->paginate(10);

        $categoryTree = Category::getTreeList();

        return view('admin.categories.index', compact('categories', 'categoryTree'));
    }

    public function create()
    {
        return redirect()->route('admin.categories.index');
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        $data['parent_id'] = !empty($data['parent_id']) ? $data['parent_id'] : null;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }
        
        Category::create($data);
        return redirect()->route('admin.categories.index')->with('success', 'Danh mục đã được tạo thành công.');
    }

    public function show(Category $category)
    {
        return redirect()->route('admin.categories.index');
    }

    public function edit(Category $category)
    {
        return redirect()->route('admin.categories.index');
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $data = $request->validated();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        $data['parent_id'] = !empty($data['parent_id']) ? $data['parent_id'] : null;

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }
        
        $category->update($data);
        return redirect()->route('admin.categories.index')->with('success', 'Danh mục đã được cập nhật thành công.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Danh mục đã được xóa thành công.');
    }
}


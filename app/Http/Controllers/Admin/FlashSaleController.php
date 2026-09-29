<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddFlashSaleItemRequest;
use App\Http\Requests\Admin\StoreFlashSaleRequest;
use App\Http\Requests\Admin\UpdateFlashSaleRequest;
use App\Models\FlashSale;
use App\Models\FlashSaleItem;
use App\Models\Product;

class FlashSaleController extends Controller
{
    public function index()
    {
        $flashSales = FlashSale::latest()->paginate(10);
        return view('admin.flash_sales.index', compact('flashSales'));
    }

    public function create()
    {
        return view('admin.flash_sales.create');
    }

    public function store(StoreFlashSaleRequest $request)
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->has('is_active');

        FlashSale::create($validated);
        return redirect()->route('admin.flash_sales.index')->with('success', 'Đã tạo chương trình Flash Sale.');
    }

    public function edit(FlashSale $flash_sale)
    {
        return view('admin.flash_sales.edit', compact('flash_sale'));
    }

    public function update(UpdateFlashSaleRequest $request, FlashSale $flash_sale)
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->has('is_active');

        $flash_sale->update($validated);
        return redirect()->route('admin.flash_sales.index')->with('success', 'Đã cập nhật chương trình Flash Sale.');
    }

    public function destroy(FlashSale $flash_sale)
    {
        $flash_sale->delete();
        return redirect()->route('admin.flash_sales.index')->with('success', 'Đã xóa Flash Sale.');
    }

    // -- Quản lý sản phẩm tham gia --
    public function manageItems(FlashSale $flash_sale)
    {
        $items = $flash_sale->items()->with('product.primaryImage')->get();
        // Lấy tất cả sản phẩm để chọn
        $products = Product::with(['primaryImage', 'variants'])->get();
        return view('admin.flash_sales.manage_items', compact('flash_sale', 'items', 'products'));
    }

    public function addItem(AddFlashSaleItemRequest $request, FlashSale $flash_sale)
    {
        $validated = $request->validated();
        $addedCount = 0;

        foreach ($validated['selected_products'] as $productId) {
            $data = $validated['products'][$productId] ?? [];
            $price = $data['flash_sale_price'] ?? 0;
            $qty = $data['quantity'] ?? 1;

            // Kiểm tra trùng lặp
            if (!$flash_sale->items()->where('product_id', $productId)->exists()) {
                $flash_sale->items()->create([
                    'product_id' => $productId,
                    'flash_sale_price' => $price,
                    'quantity' => $qty,
                ]);
                $addedCount++;
            }
        }

        return back()->with('success', "Đã thêm $addedCount sản phẩm vào Flash Sale.");
    }

    public function removeItem(FlashSale $flash_sale, FlashSaleItem $item)
    {
        if ($item->flash_sale_id == $flash_sale->id) {
            $item->delete();
        }
        return back()->with('success', 'Đã xóa sản phẩm khỏi Flash Sale.');
    }
}

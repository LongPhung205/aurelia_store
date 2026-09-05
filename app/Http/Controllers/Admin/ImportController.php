<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Import;
use App\Models\ImportDetail;
use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Models\InventoryHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ImportController extends Controller
{
    public function index(Request $request)
    {
        $query = Import::with(['supplier', 'user'])->orderBy('id', 'desc');

        // Lọc theo khoảng thời gian
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo nhà cung cấp
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Tìm kiếm tổng quát (Mã phiếu, Tên/SĐT NCC, Tên NV)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $imports = $query->paginate(15)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get();

        // Thống kê nhanh
        $stats = [
            'total_imports' => Import::count(),
            'monthly_cost' => Import::where('status', 'completed')
                                    ->whereYear('completed_at', now()->year)
                                    ->whereMonth('completed_at', now()->month)
                                    ->sum('total_amount'),
            'active_suppliers' => Supplier::where('status', true)->count(),
        ];

        return view('admin.imports.index', compact('imports', 'suppliers', 'stats'));
    }

    public function create()
    {
        $suppliers = Supplier::where('status', true)->get();
        // Lấy danh sách sản phẩm thay vì từng biến thể riêng lẻ để làm giao diện Matrix Grid
        $products = \App\Models\Product::with(['images', 'variants.color', 'variants.size'])
            ->where('status', 1)
            ->get();
        return view('admin.imports.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|string', // Accept string to allow creating new supplier
            'note' => 'nullable|string',
            'variants' => 'required|array|min:1',
            'variants.*' => 'exists:product_variants,id',
            'quantities' => 'required|array',
            'quantities.*' => 'required|integer|min:1',
            'prices' => 'required|array',
            'prices.*' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $supplierId = $request->supplier_id;
            
            // Nếu supplier_id không phải là số (tức là tên nhà cung cấp mới được gõ vào)
            if (!is_numeric($supplierId)) {
                $newSupplier = Supplier::create([
                    'name' => $supplierId,
                    'status' => true
                ]);
                $supplierId = $newSupplier->id;
            } else {
                // Đảm bảo supplier id tồn tại
                $exists = Supplier::where('id', $supplierId)->exists();
                if (!$exists) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'supplier_id' => 'Nhà cung cấp không hợp lệ.'
                    ]);
                }
            }

            $import = Import::create([
                'code' => 'IMP-' . date('Ymd') . '-' . strtoupper(uniqid()),
                'supplier_id' => $supplierId,
                'user_id' => Auth::id(),
                'note' => $request->note,
                'status' => 'pending',
                'total_amount' => 0,
            ]);

            $totalAmount = 0;
            foreach ($request->variants as $index => $variantId) {
                $qty = $request->quantities[$index];
                $price = $request->prices[$index];
                $subtotal = $qty * $price;

                ImportDetail::create([
                    'import_id' => $import->id,
                    'product_variant_id' => $variantId,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'subtotal' => $subtotal,
                ]);

                $totalAmount += $subtotal;
            }

            $import->update(['total_amount' => $totalAmount]);
        });

        return redirect()->route('admin.imports.index')->with('success', 'Đã lưu phiếu nhập kho (bản nháp).');
    }

    public function show(Import $import)
    {
        $import->load(['supplier', 'user', 'details.variant.product.images', 'details.variant.color', 'details.variant.size']);
        
        $groupedDetails = $import->details->groupBy(function($detail) {
            return $detail->variant->product_id ?? 0;
        });

        return view('admin.imports.show', compact('import', 'groupedDetails'));
    }

    public function update(Request $request, Import $import)
    {
        // Chốt phiếu nhập (Complete)
        if ($request->action === 'complete' && $import->status === 'pending') {
            DB::transaction(function () use ($import) {
                $import->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

                foreach ($import->details as $detail) {
                    $variant = $detail->variant;
                    $oldStock = $variant->stock_quantity;
                    $oldCost = $variant->cost_price;
                    
                    // Tính giá vốn bình quân gia quyền (Weighted Average Cost)
                    $totalOldValue = $oldStock * $oldCost;
                    $totalImportValue = $detail->quantity * $detail->unit_price;
                    $newStock = $oldStock + $detail->quantity;
                    
                    $newCostPrice = $newStock > 0 ? ($totalOldValue + $totalImportValue) / $newStock : $detail->unit_price;

                    // Cập nhật tồn kho và giá vốn
                    $variant->update([
                        'stock_quantity' => $newStock,
                        'cost_price' => $newCostPrice,
                    ]);

                    // Ghi thẻ kho (Inventory History)
                    InventoryHistory::create([
                        'product_variant_id' => $variant->id,
                        'reference_type' => get_class($import),
                        'reference_id' => $import->id,
                        'type' => 'import',
                        'quantity_changed' => $detail->quantity,
                        'stock_before' => $oldStock,
                        'stock_after' => $newStock,
                        'user_id' => Auth::id(),
                        'note' => 'Nhập kho từ phiếu ' . $import->code,
                    ]);
                }
            });

            return redirect()->route('admin.imports.index')->with('success', 'Đã chốt phiếu nhập, cập nhật tồn kho và giá vốn thành công.');
        }

        // Hủy phiếu nhập
        if ($request->action === 'cancel' && $import->status === 'pending') {
            $import->update(['status' => 'cancelled']);
            return redirect()->route('admin.imports.index')->with('success', 'Đã hủy phiếu nhập.');
        }

        return redirect()->back();
    }
}

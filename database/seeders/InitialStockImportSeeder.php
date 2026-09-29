<?php

namespace Database\Seeders;

use App\Models\Import;
use App\Models\ImportDetail;
use App\Models\InventoryHistory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InitialStockImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Tạo danh sách các nhà cung cấp nếu chưa có
            $suppliersData = [
                [
                    'name' => 'Tổng Kho May Mặc Hà Nội',
                    'phone' => '0987654321',
                    'email' => 'khohanoi@maymachanoi.vn',
                    'address' => 'KCN Sài Đồng, Long Biên, Hà Nội',
                    'status' => true,
                ],
                [
                    'name' => 'Công Ty Cổ Phần Thời Trang Lamer',
                    'phone' => '0912345678',
                    'email' => 'contact@lamerfashion.vn',
                    'address' => 'Tòa nhà Lamer, Cầu Giấy, Hà Nội',
                    'status' => true,
                ],
                [
                    'name' => 'Xưởng May Thiết Kế Cao Cấp Sài Gòn',
                    'phone' => '0908765432',
                    'email' => 'xuongmaysaigon@fashion.com',
                    'address' => 'KCN Tân Bình, TP. Hồ Chí Minh',
                    'status' => true,
                ],
                [
                    'name' => 'Nhà Cung Cấp Vải & Thời Trang Á Châu',
                    'phone' => '0934567890',
                    'email' => 'supply@achaufashion.vn',
                    'address' => 'Khu chế xuất Linh Trung, Thủ Đức, TP. Hồ Chí Minh',
                    'status' => true,
                ],
                [
                    'name' => 'Tổng Công Ty May Xuất Khẩu Việt Tiến Partner',
                    'phone' => '0945678123',
                    'email' => 'viettien.partner@vinatex.com',
                    'address' => 'Quận 10, TP. Hồ Chí Minh',
                    'status' => true,
                ],
            ];

            $suppliers = collect();
            foreach ($suppliersData as $sup) {
                $supplier = Supplier::firstOrCreate(
                    ['name' => $sup['name']],
                    $sup
                );
                $suppliers->push($supplier);
            }

            // 2. Tìm người thực hiện nhập kho (Admin)
            $adminUser = User::where('role', 'admin')->first() ?? User::first();
            $adminUserId = $adminUser ? $adminUser->id : 1;

            // 3. Lấy tất cả sản phẩm và biến thể
            $products = Product::with('variants')->orderBy('id')->get();
            $totalProducts = $products->count();

            if ($totalProducts === 0) {
                $this->command->warn('Không có sản phẩm nào trong cơ sở dữ liệu!');
                return;
            }

            // Chia sản phẩm thành 5 nhóm tương ứng với 5 nhà cung cấp
            $chunkSize = (int) ceil($totalProducts / $suppliers->count());
            $productChunks = $products->chunk($chunkSize);

            $importIndex = 1;
            $totalVariantsImported = 0;
            $grandTotalAmount = 0;

            foreach ($productChunks as $chunkIndex => $chunkProducts) {
                $supplier = $suppliers[$chunkIndex % $suppliers->count()];
                $timestamp = now()->subHours(($suppliers->count() - $chunkIndex) * 2);
                $importCode = 'IMP-' . now()->format('Ymd') . '-' . str_pad($importIndex, 4, '0', STR_PAD_LEFT);

                // Tạo phiếu nhập kho ở trạng thái completed
                $import = Import::create([
                    'code' => $importCode,
                    'supplier_id' => $supplier->id,
                    'user_id' => $adminUserId,
                    'total_amount' => 0,
                    'note' => 'Nhập kho khởi tạo tồn kho 100 sản phẩm/biến thể - ' . $supplier->name,
                    'status' => 'completed',
                    'completed_at' => $timestamp,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);

                $importTotal = 0;

                foreach ($chunkProducts as $product) {
                    foreach ($product->variants as $variant) {
                        // Tính giá nhập: nếu chưa có giá vốn, tính 50% giá bán lẻ làm tròn đến hàng nghìn
                        $unitPrice = $variant->cost_price > 0
                            ? (float) $variant->cost_price
                            : max(50000, round(($variant->price * 0.5) / 1000) * 1000);

                        $quantity = 100;
                        $subtotal = $quantity * $unitPrice;
                        $importTotal += $subtotal;

                        // 1. Tạo chi tiết phiếu nhập
                        ImportDetail::create([
                            'import_id' => $import->id,
                            'product_variant_id' => $variant->id,
                            'quantity' => $quantity,
                            'unit_price' => $unitPrice,
                            'subtotal' => $subtotal,
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ]);

                        // 2. Cập nhật tồn kho và giá vốn của biến thể
                        $oldStock = (int) ($variant->stock_quantity ?? 0);
                        $newStock = $oldStock + $quantity;

                        $variant->update([
                            'cost_price' => $unitPrice,
                            'stock_quantity' => $newStock,
                        ]);

                        // 3. Ghi thẻ kho (InventoryHistory)
                        InventoryHistory::create([
                            'product_variant_id' => $variant->id,
                            'reference_type' => Import::class,
                            'reference_id' => $import->id,
                            'type' => 'import',
                            'quantity_changed' => $quantity,
                            'stock_before' => $oldStock,
                            'stock_after' => $newStock,
                            'user_id' => $adminUserId,
                            'note' => 'Nhập kho từ phiếu ' . $import->code,
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ]);

                        $totalVariantsImported++;
                    }
                }

                // Cập nhật tổng tiền phiếu nhập
                $import->update(['total_amount' => $importTotal]);
                $grandTotalAmount += $importTotal;

                if ($this->command) {
                    $this->command->info("Đã tạo phiếu nhập: {$importCode} - NCC: {$supplier->name} - Số SP: " . $chunkProducts->count() . " - Tổng tiền: " . number_format($importTotal, 0, ',', '.') . " đ");
                }

                $importIndex++;
            }

            if ($this->command) {
                $this->command->info("--------------------------------------------------");
                $this->command->info("HOÀN TẤT NHẬP KHO TOÀN BỘ SẢN PHẨM:");
                $this->command->info("- Số phiếu nhập: " . ($importIndex - 1));
                $this->command->info("- Tổng số biến thể đã nhập: {$totalVariantsImported}");
                $this->command->info("- Số lượng nhập mỗi biến thể: 100");
                $this->command->info("- Tổng số lượng sản phẩm nhập kho: " . number_format($totalVariantsImported * 100, 0, ',', '.') . " chiếc");
                $this->command->info("- Tổng giá trị nhập kho: " . number_format($grandTotalAmount, 0, ',', '.') . " đ");
            }
        });
    }
}

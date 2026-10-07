# Phân Hệ Tài Chính & Đối Soát Giao Dịch Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Xây dựng hai trang quản trị chuyên sâu: **Thống kê tài chính (`/admin/finance`)** và **Danh sách giao dịch (`/admin/transactions`)**; hỗ trợ tìm kiếm, lọc đa tiêu chí, phân trang, tự động sinh giao dịch cho đơn COD và đối soát thanh toán chuẩn mực (COD, MoMo, PayOS) đồng bộ 2 chiều với đơn hàng.

**Architecture:** Mở rộng bảng `payment_transactions` để quản lý tập trung mọi nguồn tiền; xây dựng `Admin\TransactionController` phụ trách tra cứu và Modal đối soát 2 chiều với `Order`; xây dựng `Admin\FinanceController` phụ trách tính toán các chỉ số KPI tài chính (doanh thu thực thu, tiền chờ thu, chi phí nhập hàng, lợi nhuận gộp) và cung cấp dữ liệu cho biểu đồ Chart.js; thiết kế giao diện theo chuẩn **Fixed Table Layout** và tích hợp vào sidebar Admin.

**Tech Stack:** Laravel 11, MySQL, TailwindCSS, Alpine.js, Chart.js, SweetAlert2.

## Global Constraints
- Tất cả route quản trị phải nằm dưới tiền tố `admin/`, middleware `['auth', 'role:admin']`, tên route `admin.finance.*` và `admin.transactions.*`.
- Trang danh sách giao dịch phải tuân thủ chuẩn **Fixed Table Layout** (chỉ cuộn phần thân bảng dữ liệu, thanh tìm kiếm/lọc và thanh phân trang luôn cố định).
- Tuyệt đối không dùng `window.alert()` hoặc `confirm()`; sử dụng hệ thống **SweetAlert2** và Toast notification đã chuẩn hóa trong layout Admin.
- Đảm bảo tương thích hoàn toàn với chế độ Sáng / Tối (Dark mode) của hệ thống Tailwind hiện có.

---

### Task 1: Database Migration & Model Updates for Payment Transactions

**Files:**
- Create: `database/migrations/2026_09_24_091500_add_reconciliation_fields_to_payment_transactions_table.php`
- Modify: `app/Models/PaymentTransaction.php`
- Test: `tests/Feature/Admin/PaymentTransactionModelTest.php`

**Interfaces:**
- Produces: Cột `admin_id`, `note`, `reconciled_at` trên bảng `payment_transactions`, quan hệ `reconciledBy()` trên model `PaymentTransaction`.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Admin/PaymentTransactionModelTest.php
namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentTransactionModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_transaction_supports_reconciliation_fields()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = Order::factory()->create();

        $transaction = PaymentTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => 'COD-ORD-' . $order->id,
            'amount' => 500000,
            'payment_method' => 'cod',
            'status' => 'success',
            'admin_id' => $admin->id,
            'note' => 'Bưu tá nộp tiền đợt 1',
            'reconciled_at' => now(),
        ]);

        $this->assertDatabaseHas('payment_transactions', [
            'id' => $transaction->id,
            'admin_id' => $admin->id,
            'payment_method' => 'cod',
            'note' => 'Bưu tá nộp tiền đợt 1',
        ]);
        $this->assertEquals($admin->id, $transaction->reconciledBy->id);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Admin/PaymentTransactionModelTest.php`
Expected: FAIL (Columns `admin_id`, `note`, `reconciled_at` not found).

- [ ] **Step 3: Create Migration and Update Model**

Create migration `database/migrations/2026_09_24_091500_add_reconciliation_fields_to_payment_transactions_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->after('status')->constrained('users')->onDelete('set null');
            $table->text('note')->nullable()->after('admin_id')->comment('Ghi chú đối soát');
            $table->timestamp('reconciled_at')->nullable()->after('note')->comment('Thời gian đối soát');
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn(['admin_id', 'note', 'reconciled_at']);
        });
    }
};
```

Update `app/Models/PaymentTransaction.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'transaction_id',
        'amount',
        'payment_method',
        'status',
        'response_data',
        'admin_id',
        'note',
        'reconciled_at',
    ];

    protected $casts = [
        'response_data' => 'array',
        'reconciled_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function reconciledBy()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
```

- [ ] **Step 4: Run migration and verify test passes**

Run:
```bash
php artisan migrate
php artisan test tests/Feature/Admin/PaymentTransactionModelTest.php
```
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_09_24_091500_add_reconciliation_fields_to_payment_transactions_table.php app/Models/PaymentTransaction.php tests/Feature/Admin/PaymentTransactionModelTest.php
git commit -m "feat(finance): add reconciliation fields to payment_transactions"
```

---

### Task 2: Automatic COD Transaction Generation & Backfill Command

**Files:**
- Create: `app/Console/Commands/SyncCodTransactionsCommand.php`
- Modify: `app/Http/Controllers/Client/CheckoutController.php`
- Test: `tests/Feature/Admin/SyncCodTransactionsTest.php`

**Interfaces:**
- Consumes: `PaymentTransaction::create()`, `Order`.
- Produces: `php artisan app:sync-cod-transactions` command.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Admin/SyncCodTransactionsTest.php
namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SyncCodTransactionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_command_creates_transactions_for_legacy_cod_orders()
    {
        $order = Order::factory()->create([
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'total_amount' => 750000,
        ]);

        $this->assertEquals(0, $order->transactions()->count());

        $this->artisan('app:sync-cod-transactions')
            ->expectsOutputToContain('Đã đồng bộ giao dịch cho các đơn hàng COD')
            ->assertExitCode(0);

        $this->assertEquals(1, $order->fresh()->transactions()->count());
        $transaction = $order->transactions()->first();
        $this->assertEquals('cod', $transaction->payment_method);
        $this->assertEquals(750000, (float) $transaction->amount);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Admin/SyncCodTransactionsTest.php`
Expected: FAIL (Command does not exist).

- [ ] **Step 3: Implement Command and update CheckoutController**

Create `app/Console/Commands/SyncCodTransactionsCommand.php`:
```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\PaymentTransaction;

class SyncCodTransactionsCommand extends Command
{
    protected $signature = 'app:sync-cod-transactions';
    protected $description = 'Tự động sinh PaymentTransaction cho các đơn hàng COD chưa có bản ghi giao dịch';

    public function handle(): int
    {
        $orders = Order::where('payment_method', 'cod')
            ->whereDoesntHave('transactions')
            ->get();

        $count = 0;
        foreach ($orders as $order) {
            PaymentTransaction::create([
                'order_id' => $order->id,
                'transaction_id' => 'COD-ORD-' . $order->id,
                'amount' => $order->total_amount,
                'payment_method' => 'cod',
                'status' => $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'failed' ? 'failed' : 'pending'),
                'note' => 'Đồng bộ tự động từ hệ thống',
            ]);
            $count++;
        }

        $this->info("Đã đồng bộ giao dịch cho các đơn hàng COD: {$count} đơn.");
        return 0;
    }
}
```

Update `app/Http/Controllers/Client/CheckoutController.php` (around line 237 after `DB::commit()`):
```php
            // Auto generate transaction for COD order
            if ($order->payment_method === 'cod') {
                PaymentTransaction::create([
                    'order_id'       => $order->id,
                    'transaction_id' => 'COD-ORD-' . $order->id,
                    'amount'         => $order->total_amount,
                    'payment_method' => 'cod',
                    'status'         => 'pending',
                ]);
            }
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/Admin/SyncCodTransactionsTest.php`
Expected: PASS.

- [ ] **Step 5: Run command to backfill existing data**

Run: `php artisan app:sync-cod-transactions`
Expected: Successfully synced all existing COD orders.

- [ ] **Step 6: Commit**

```bash
git add app/Console/Commands/SyncCodTransactionsCommand.php app/Http/Controllers/Client/CheckoutController.php tests/Feature/Admin/SyncCodTransactionsTest.php
git commit -m "feat(finance): add automatic COD transaction creation and backfill command"
```

---

### Task 3: Transaction List & Reconciliation Backend (Controller, Request & Routes)

**Files:**
- Create: `app/Http/Requests/Admin/UpdateTransactionRequest.php`
- Create: `app/Http/Controllers/Admin/TransactionController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Admin/TransactionControllerTest.php`

**Interfaces:**
- Produces:
  - `GET /admin/transactions` (`route('admin.transactions.index')`)
  - `PATCH /admin/transactions/{transaction}` (`route('admin.transactions.update')`)

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Admin/TransactionControllerTest.php
namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_transaction_list()
    {
        $order = Order::factory()->create();
        PaymentTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => 'COD-ORD-' . $order->id,
            'amount' => 300000,
            'payment_method' => 'cod',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.transactions.index'));
        $response->assertStatus(200);
        $response->assertSee('COD-ORD-' . $order->id);
    }

    public function test_admin_can_reconcile_transaction_and_syncs_order()
    {
        $order = Order::factory()->create(['payment_status' => 'pending']);
        $transaction = PaymentTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => 'COD-ORD-' . $order->id,
            'amount' => 450000,
            'payment_method' => 'cod',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->patch(route('admin.transactions.update', $transaction), [
            'status' => 'success',
            'note' => 'Bưu tá đã nộp đủ tiền mặt',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payment_transactions', [
            'id' => $transaction->id,
            'status' => 'success',
            'admin_id' => $this->admin->id,
            'note' => 'Bưu tá đã nộp đủ tiền mặt',
        ]);
        $this->assertEquals('paid', $order->fresh()->payment_status);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Admin/TransactionControllerTest.php`
Expected: FAIL (Route/Controller does not exist).

- [ ] **Step 3: Create Form Request and Controller**

Create `app/Http/Requests/Admin/UpdateTransactionRequest.php`:
```php
<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:pending,success,failed'],
            'note'   => ['nullable', 'string', 'max:1000'],
        ];
    }
}
```

Create `app/Http/Controllers/Admin/TransactionController.php`:
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Http\Requests\Admin\UpdateTransactionRequest;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = PaymentTransaction::with(['order.user', 'reconciledBy'])
            ->latest('id');

        // Search by transaction_id, order_id, customer name or phone
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhere('order_id', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($oq) use ($search) {
                      $oq->where('customer_name', 'like', "%{$search}%")
                         ->orWhere('customer_phone', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by payment method
        if ($method = $request->input('payment_method')) {
            if ($method !== 'all') {
                $query->where('payment_method', $method);
            }
        }

        // Filter by status
        if ($status = $request->input('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        // Filter by date range
        if ($fromDate = $request->input('from_date')) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        if ($toDate = $request->input('to_date')) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $transactions = $query->paginate(15)->withQueryString();

        // Calculate counts for quick badges
        $stats = [
            'total_count'   => PaymentTransaction::count(),
            'success_count' => PaymentTransaction::where('status', 'success')->count(),
            'pending_count' => PaymentTransaction::where('status', 'pending')->count(),
            'failed_count'  => PaymentTransaction::where('status', 'failed')->count(),
            'total_amount'  => PaymentTransaction::where('status', 'success')->sum('amount'),
        ];

        return view('admin.transactions.index', compact('transactions', 'stats'));
    }

    public function update(UpdateTransactionRequest $request, PaymentTransaction $transaction)
    {
        $validated = $request->validated();

        $transaction->update([
            'status'        => $validated['status'],
            'note'          => $validated['note'],
            'admin_id'      => auth()->id(),
            'reconciled_at' => $validated['status'] === 'success' ? now() : $transaction->reconciled_at,
        ]);

        // Sync payment status with Order
        if ($transaction->order) {
            $orderStatus = match ($validated['status']) {
                'success' => 'paid',
                'failed'  => 'failed',
                default   => 'pending',
            };
            $transaction->order->update(['payment_status' => $orderStatus]);
        }

        return redirect()->back()->with('success', "Cập nhật đối soát giao dịch #{$transaction->transaction_id} thành công!");
    }
}
```

Update `routes/web.php` in admin prefix:
```php
    // Finance & Transactions
    Route::get('transactions', [Admin\TransactionController::class, 'index'])->name('transactions.index');
    Route::patch('transactions/{transaction}', [Admin\TransactionController::class, 'update'])->name('transactions.update');
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/Admin/TransactionControllerTest.php`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Requests/Admin/UpdateTransactionRequest.php app/Http/Controllers/Admin/TransactionController.php routes/web.php tests/Feature/Admin/TransactionControllerTest.php
git commit -m "feat(finance): add TransactionController and reconciliation API"
```

---

### Task 4: Transaction List & Reconciliation Modal UI (Fixed Table Layout)

**Files:**
- Create: `resources/views/admin/transactions/index.blade.php`

**Interfaces:**
- Consumes: `$transactions`, `$stats`, `route('admin.transactions.index')`, `route('admin.transactions.update', $item)`.
- Produces: Full responsive Fixed Table Layout page with search, filters, pagination, and reconciliation modal.

- [ ] **Step 1: Create `resources/views/admin/transactions/index.blade.php`**

```blade
@extends('admin.layouts.admin')

@section('title', 'Quản lý Giao dịch & Đối soát')
@section('page_title', 'Danh sách Giao dịch & Đối soát')

@section('content')
<div class="space-y-4" x-data="{
    showModal: false,
    selectedTx: null,
    formAction: '',
    openReconcile(tx) {
        this.selectedTx = tx;
        this.formAction = '/admin/transactions/' + tx.id;
        this.showModal = true;
    }
}">

    <!-- Top Stats Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tổng đã thu</p>
                <h4 class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($stats['total_amount']) }}đ</h4>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                <i class="bi bi-cash-stack"></i>
            </div>
        </div>

        <div class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Thành công</p>
                <h4 class="text-xl font-bold text-slate-800 dark:text-white mt-1">{{ number_format($stats['success_count']) }}</h4>
            </div>
            <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg">
                <i class="bi bi-check-circle-fill"></i>
            </div>
        </div>

        <div class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Chờ thu tiền / COD</p>
                <h4 class="text-xl font-bold text-amber-500 mt-1">{{ number_format($stats['pending_count']) }}</h4>
            </div>
            <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-500 flex items-center justify-center text-lg">
                <i class="bi bi-hourglass-split"></i>
            </div>
        </div>

        <div class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Thất bại / Hủy</p>
                <h4 class="text-xl font-bold text-rose-500 mt-1">{{ number_format($stats['failed_count']) }}</h4>
            </div>
            <div class="w-10 h-10 rounded-lg bg-rose-50 dark:bg-rose-900/30 text-rose-500 flex items-center justify-center text-lg">
                <i class="bi bi-x-circle-fill"></i>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <x-admin.card>
        <form method="GET" action="{{ route('admin.transactions.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Tìm kiếm</label>
                <div class="relative">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Mã GD, Đơn, Tên, SĐT..." class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:ring-primary-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Cổng thanh toán</label>
                <select name="payment_method" class="w-full py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:ring-primary-500">
                    <option value="all">Tất cả phương thức</option>
                    <option value="cod" {{ request('payment_method') == 'cod' ? 'selected' : '' }}>COD (Tiền mặt)</option>
                    <option value="payos" {{ request('payment_method') == 'payos' ? 'selected' : '' }}>PayOS (Ngân hàng)</option>
                    <option value="momo" {{ request('payment_method') == 'momo' ? 'selected' : '' }}>MoMo (Ví điện tử)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Trạng thái</label>
                <select name="status" class="w-full py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:ring-primary-500">
                    <option value="all">Tất cả trạng thái</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ thanh toán</option>
                    <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Thành công</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Thất bại / Hủy</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Từ ngày - Đến ngày</label>
                <div class="flex items-center gap-1">
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-1/2 py-1.5 px-2 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white">
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-1/2 py-1.5 px-2 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white">
                </div>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-1.5 px-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-xs font-medium transition-colors flex items-center justify-center gap-1 shadow-sm">
                    <i class="bi bi-funnel"></i> Lọc
                </button>
                <a href="{{ route('admin.transactions.index') }}" class="py-1.5 px-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 rounded-lg text-xs font-medium transition-colors" title="Đặt lại bộ lọc">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </x-admin.card>

    <!-- Fixed Table Layout Data Card -->
    <x-admin.card noPadding="true" class="overflow-hidden border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col h-[calc(100vh-22rem)] min-h-[420px]">
        <div class="overflow-y-auto flex-1">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/80 sticky top-0 z-10 border-b border-slate-200 dark:border-slate-700 shadow-[0_1px_2px_rgba(0,0,0,0.05)]">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">Mã GD / Đơn hàng</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">Khách hàng</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">Cổng thanh toán</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">Số tiền</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">Trạng thái</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">Thời gian & Đối soát</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($transactions as $tx)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-4 py-3">
                            <span class="font-mono font-medium text-slate-800 dark:text-slate-200 block truncate max-w-[160px]">{{ $tx->transaction_id ?? '-' }}</span>
                            @if($tx->order)
                            <a href="{{ route('admin.orders.show', $tx->order_id) }}" class="text-primary-600 dark:text-primary-400 hover:underline font-semibold text-[11px] inline-flex items-center gap-1 mt-0.5">
                                <i class="bi bi-receipt"></i> #ORD-{{ $tx->order_id }}
                            </a>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            <span class="font-medium text-slate-800 dark:text-white block">{{ $tx->order->customer_name ?? 'Khách lẻ' }}</span>
                            <span class="text-slate-500 text-[11px] block">{{ $tx->order->customer_phone ?? '-' }}</span>
                        </td>

                        <td class="px-4 py-3">
                            @if($tx->payment_method === 'payos')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                    <i class="bi bi-bank"></i> PayOS (QR)
                                </span>
                            @elseif($tx->payment_method === 'momo')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-medium bg-pink-100 text-pink-700 dark:bg-pink-900/40 dark:text-pink-300">
                                    <i class="bi bi-wallet2"></i> MoMo
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                    <i class="bi bi-cash"></i> Tiền mặt (COD)
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">
                            {{ number_format($tx->amount) }}đ
                        </td>

                        <td class="px-4 py-3">
                            @if($tx->status === 'success')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    <i class="bi bi-check-circle-fill"></i> Thành công
                                </span>
                            @elseif($tx->status === 'pending')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                    <i class="bi bi-clock-history"></i> Chờ thanh toán
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                                    <i class="bi bi-x-circle-fill"></i> Thất bại / Hủy
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-slate-500 text-[11px]">
                            <div>{{ $tx->created_at->format('d/m/Y H:i') }}</div>
                            @if($tx->reconciled_at)
                                <div class="text-emerald-600 dark:text-emerald-400 mt-0.5">
                                    <i class="bi bi-person-check"></i> {{ $tx->reconciledBy->name ?? 'Admin' }} ({{ $tx->reconciled_at->format('d/m H:i') }})
                                </div>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <button type="button" @click="openReconcile({{ json_encode($tx) }})" class="px-2.5 py-1 text-xs font-medium rounded-lg text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/30 hover:bg-primary-100 dark:hover:bg-primary-900/50 transition-colors" title="Đối soát / Cập nhật">
                                    <i class="bi bi-pencil-square mr-1"></i> Đối soát
                                </button>
                                @if($tx->order_id)
                                <a href="{{ route('admin.orders.show', $tx->order_id) }}" class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors" title="Xem chi tiết đơn hàng">
                                    <i class="bi bi-eye text-base"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                            <i class="bi bi-inbox text-3xl mb-2 text-slate-400 block"></i>
                            Không tìm thấy giao dịch nào phù hợp với điều kiện lọc.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
        <div class="p-3 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shrink-0">
            {{ $transactions->links('pagination::tailwind') }}
        </div>
        @endif
    </x-admin.card>

    <!-- Reconciliation Modal -->
    <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showModal = false"></div>
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="relative bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 text-left shadow-2xl border border-slate-100 dark:border-slate-700 transform transition-all">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700 mb-4">
                    <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <i class="bi bi-shield-check text-primary-600"></i> Đối soát thanh toán
                    </h3>
                    <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i class="bi bi-x-lg text-lg"></i>
                    </button>
                </div>

                <form :action="formAction" method="POST" class="form-confirm"
                      data-confirm-title="Xác nhận đối soát?"
                      data-confirm-text="Trạng thái giao dịch và đơn hàng liên kết sẽ được cập nhật đồng bộ. Bạn có chắc chắn không?"
                      data-confirm-icon="question">
                    @csrf
                    @method('PATCH')

                    <div class="mb-4 p-3 bg-slate-50 dark:bg-slate-900 rounded-xl space-y-1 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Mã giao dịch:</span>
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200" x-text="selectedTx?.transaction_id"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Đơn hàng:</span>
                            <span class="font-semibold text-primary-600" x-text="'#ORD-' + selectedTx?.order_id"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Số tiền:</span>
                            <span class="font-bold text-emerald-600" x-text="new Intl.NumberFormat('vi-VN').format(selectedTx?.amount || 0) + 'đ'"></span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase">
                            Trạng thái thanh toán mới <span class="text-rose-500">*</span>
                        </label>
                        <select name="status" class="w-full text-sm rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white p-2.5 focus:ring-primary-500">
                            <option value="success" :selected="selectedTx?.status === 'success'">🟢 Thành công (Đã nhận tiền)</option>
                            <option value="pending" :selected="selectedTx?.status === 'pending'">🟡 Chờ thanh toán</option>
                            <option value="failed" :selected="selectedTx?.status === 'failed'">🔴 Thất bại / Hủy</option>
                        </select>
                    </div>

                    <div class="mb-6">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase">
                            Ghi chú / Mã tham chiếu đối soát
                        </label>
                        <textarea name="note" rows="3" class="w-full text-sm rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white p-2.5 focus:ring-primary-500" placeholder="Ví dụ: Bưu tá Nguyễn Văn A đã nộp tiền COD ngày 24/09..." x-text="selectedTx?.note || ''"></textarea>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-sm text-slate-600 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 rounded-lg transition-colors">
                            Hủy bỏ
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors shadow-sm">
                            Lưu đối soát
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 2: Test view compilation**

Run: `php artisan view:clear`
Expected: View cache cleared successfully.

- [ ] **Step 3: Commit**

```bash
git add resources/views/admin/transactions/index.blade.php
git commit -m "feat(finance): add transactions list and reconciliation UI"
```

---

### Task 5: Financial Analytics Backend (KPIs, Date Filters & Chart Data Aggregation)

**Files:**
- Create: `app/Http/Controllers/Admin/FinanceController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Admin/FinanceControllerTest.php`

**Interfaces:**
- Produces: `GET /admin/finance` (`route('admin.finance.index')`), supplying `$kpi`, `$chartData`, `$methodSummary`.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Admin/FinanceControllerTest.php
namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FinanceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_access_finance_overview()
    {
        $order = Order::factory()->create();
        PaymentTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => 'PAYOS-1234',
            'amount' => 1200000,
            'payment_method' => 'payos',
            'status' => 'success',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.finance.index'));
        $response->assertStatus(200);
        $response->assertViewHas('kpi');
        $response->assertViewHas('chartData');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Admin/FinanceControllerTest.php`
Expected: FAIL (Route/Controller does not exist).

- [ ] **Step 3: Implement `FinanceController`**

Create `app/Http/Controllers/Admin/FinanceController.php`:
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Models\Import;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $range = $request->input('range', 'this_month');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // Resolve date boundaries
        if (!$fromDate || !$toDate) {
            match ($range) {
                'today' => [
                    $fromDate = Carbon::today()->startOfDay()->toDateString(),
                    $toDate = Carbon::today()->endOfDay()->toDateString(),
                ],
                'last_7_days' => [
                    $fromDate = Carbon::now()->subDays(6)->startOfDay()->toDateString(),
                    $toDate = Carbon::now()->endOfDay()->toDateString(),
                ],
                'this_year' => [
                    $fromDate = Carbon::now()->startOfYear()->toDateString(),
                    $toDate = Carbon::now()->endOfYear()->toDateString(),
                ],
                default => [ // this_month
                    $fromDate = Carbon::now()->startOfMonth()->toDateString(),
                    $toDate = Carbon::now()->endOfMonth()->toDateString(),
                ],
            };
        }

        // 1. KPI Metrics
        $collectedRevenue = (float) PaymentTransaction::where('status', 'success')
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->sum('amount');

        $pendingRevenue = (float) PaymentTransaction::where('status', 'pending')
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->sum('amount');

        $costOfImports = (float) Import::where('status', 'completed')
            ->whereDate('import_date', '>=', $fromDate)
            ->whereDate('import_date', '<=', $toDate)
            ->sum('total_cost');

        $grossProfit = $collectedRevenue - $costOfImports;
        $profitMargin = $collectedRevenue > 0 ? round(($grossProfit / $collectedRevenue) * 100, 1) : 0;

        $kpi = [
            'collected_revenue' => $collectedRevenue,
            'pending_revenue'   => $pendingRevenue,
            'cost_of_imports'   => $costOfImports,
            'gross_profit'      => $grossProfit,
            'profit_margin'     => $profitMargin,
        ];

        // 2. Timeline Revenue Chart Data (group by date)
        $dailyRevenues = PaymentTransaction::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total')
            )
            ->where('status', 'success')
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $chartLabels = [];
        $chartValues = [];
        $period = Carbon::parse($fromDate)->daysUntil(Carbon::parse($toDate));
        $mappedRevenues = $dailyRevenues->pluck('total', 'date')->all();

        foreach ($period as $date) {
            $dateStr = $date->toDateString();
            $chartLabels[] = $date->format('d/m');
            $chartValues[] = (float) ($mappedRevenues[$dateStr] ?? 0);
        }

        // 3. Payment Methods Breakdown
        $methods = ['cod' => 'Tiền mặt (COD)', 'payos' => 'PayOS (QR)', 'momo' => 'Ví MoMo'];
        $methodStats = PaymentTransaction::select(
                'payment_method',
                DB::raw('COUNT(*) as total_tx'),
                DB::raw('SUM(CASE WHEN status = "success" THEN amount ELSE 0 END) as collected_amount'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as pending_amount')
            )
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        $donutLabels = [];
        $donutValues = [];
        $methodSummary = [];

        foreach ($methods as $key => $label) {
            $stat = $methodStats->get($key);
            $collected = (float) ($stat ? $stat->collected_amount : 0);
            $pending = (float) ($stat ? $stat->pending_amount : 0);
            $count = (int) ($stat ? $stat->total_tx : 0);

            $donutLabels[] = $label;
            $donutValues[] = $collected;

            $methodSummary[] = [
                'key' => $key,
                'name' => $label,
                'count' => $count,
                'collected' => $collected,
                'pending' => $pending,
            ];
        }

        $chartData = [
            'timeline_labels' => $chartLabels,
            'timeline_values' => $chartValues,
            'donut_labels'    => $donutLabels,
            'donut_values'    => $donutValues,
        ];

        return view('admin.finance.index', compact('kpi', 'chartData', 'methodSummary', 'fromDate', 'toDate', 'range'));
    }
}
```

Update `routes/web.php` under admin prefix:
```php
    Route::get('finance', [Admin\FinanceController::class, 'index'])->name('finance.index');
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/Admin/FinanceControllerTest.php`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Admin/FinanceController.php routes/web.php tests/Feature/Admin/FinanceControllerTest.php
git commit -m "feat(finance): add FinanceController with KPI metrics and chart aggregation"
```

---

### Task 6: Financial Analytics UI (KPI Cards, Chart.js Visualizations & Summary Table)

**Files:**
- Create: `resources/views/admin/finance/index.blade.php`

**Interfaces:**
- Consumes: `$kpi`, `$chartData`, `$methodSummary`, `$fromDate`, `$toDate`, `$range`.
- Produces: Interactive analytics dashboard with Chart.js charts and metrics.

- [ ] **Step 1: Create `resources/views/admin/finance/index.blade.php`**

```blade
@extends('admin.layouts.admin')

@section('title', 'Thống kê Tài chính')
@section('page_title', 'Thống kê Tài chính & Dòng tiền')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)';

    // 1. Revenue Timeline Chart
    const ctxTimeline = document.getElementById('timelineChart')?.getContext('2d');
    if (ctxTimeline) {
        new Chart(ctxTimeline, {
            type: 'line',
            data: {
                labels: @json($chartData['timeline_labels']),
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: @json($chartData['timeline_values']),
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ' ' + new Intl.NumberFormat('vi-VN').format(ctx.raw) + ' VNĐ'
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: gridColor },
                        ticks: { color: textColor, font: { size: 11 } }
                    },
                    y: {
                        grid: { color: gridColor },
                        ticks: {
                            color: textColor,
                            font: { size: 11 },
                            callback: (val) => new Intl.NumberFormat('vi-VN', { notation: 'compact' }).format(val) + 'đ'
                        }
                    }
                }
            }
        });
    }

    // 2. Payment Methods Donut Chart
    const ctxDonut = document.getElementById('donutChart')?.getContext('2d');
    if (ctxDonut) {
        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: @json($chartData['donut_labels']),
                datasets: [{
                    data: @json($chartData['donut_values']),
                    backgroundColor: ['#f59e0b', '#3b82f6', '#ec4899'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: textColor, boxWidth: 12, font: { size: 11 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ' ' + ctx.label + ': ' + new Intl.NumberFormat('vi-VN').format(ctx.raw) + ' VNĐ'
                        }
                    }
                },
                cutout: '70%',
            }
        });
    }
});
</script>
@endpush

@section('content')
<div class="space-y-6">

    <!-- Date Range Filter Bar -->
    <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Quick Preset Tabs -->
        <div class="flex items-center gap-1.5 p-1 bg-slate-100 dark:bg-slate-900 rounded-xl overflow-x-auto w-full md:w-auto">
            <a href="{{ route('admin.finance.index', ['range' => 'today']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $range === 'today' ? 'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">Hôm nay</a>
            <a href="{{ route('admin.finance.index', ['range' => 'last_7_days']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $range === 'last_7_days' ? 'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">7 ngày qua</a>
            <a href="{{ route('admin.finance.index', ['range' => 'this_month']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $range === 'this_month' ? 'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">Tháng này</a>
            <a href="{{ route('admin.finance.index', ['range' => 'this_year']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $range === 'this_year' ? 'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">Năm nay</a>
        </div>

        <!-- Custom Date Range Form -->
        <form method="GET" action="{{ route('admin.finance.index') }}" class="flex items-center gap-2 w-full md:w-auto">
            <input type="date" name="from_date" value="{{ $fromDate }}" class="text-xs py-1.5 px-2.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white">
            <span class="text-slate-400 text-xs">đến</span>
            <input type="date" name="to_date" value="{{ $toDate }}" class="text-xs py-1.5 px-2.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white">
            <button type="submit" class="py-1.5 px-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-xs font-medium transition-colors shadow-sm">
                Áp dụng
            </button>
        </form>
    </div>

    <!-- 4 Main KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Revenue Collected -->
        <div class="p-5 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Doanh thu thực thu</span>
                <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 text-base"><i class="bi bi-wallet2"></i></span>
            </div>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-2">{{ number_format($kpi['collected_revenue']) }}đ</h3>
            <p class="text-xs text-slate-500 mt-1">Đã vào tài khoản / nhận tiền COD</p>
        </div>

        <!-- Pending In-Transit Revenue -->
        <div class="p-5 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Doanh thu chờ thu (COD)</span>
                <span class="p-2 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-500 text-base"><i class="bi bi-truck"></i></span>
            </div>
            <h3 class="text-2xl font-bold text-amber-500 mt-2">{{ number_format($kpi['pending_revenue']) }}đ</h3>
            <p class="text-xs text-slate-500 mt-1">Đơn COD đang giao / chờ đối soát</p>
        </div>

        <!-- Cost of Goods -->
        <div class="p-5 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Chi phí vốn nhập kho</span>
                <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 text-base"><i class="bi bi-box-seam"></i></span>
            </div>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-2">{{ number_format($kpi['cost_of_imports']) }}đ</h3>
            <p class="text-xs text-slate-500 mt-1">Tổng tiền các phiếu nhập hoàn tất</p>
        </div>

        <!-- Estimated Gross Profit -->
        <div class="p-5 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Lợi nhuận gộp ước tính</span>
                <span class="p-2 rounded-xl bg-purple-50 dark:bg-purple-900/30 text-purple-600 text-base"><i class="bi bi-graph-up-arrow"></i></span>
            </div>
            <h3 class="text-2xl font-bold {{ $kpi['gross_profit'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600' }} mt-2">
                {{ number_format($kpi['gross_profit']) }}đ
            </h3>
            <p class="text-xs text-slate-500 mt-1">Tỷ suất lợi nhuận: <span class="font-bold text-purple-600">{{ $kpi['profit_margin'] }}%</span></p>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Revenue Trend Line Chart -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="bi bi-bar-chart-fill text-primary-600"></i> Xu hướng Doanh thu theo ngày
                </h4>
                <span class="text-xs text-slate-400">{{ Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($toDate)->format('d/m/Y') }}</span>
            </div>
            <div class="h-72">
                <canvas id="timelineChart"></canvas>
            </div>
        </div>

        <!-- Payment Methods Donut Chart -->
        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="bi bi-pie-chart-fill text-pink-500"></i> Tỷ trọng Cổng thanh toán
                </h4>
            </div>
            <div class="h-64 flex-1 flex items-center justify-center">
                <canvas id="donutChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Payment Methods Breakdown Table -->
    <x-admin.card title="Dòng tiền chi tiết theo Phương thức thanh toán" icon="bi bi-credit-card-2-front">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-700 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Cổng thanh toán</th>
                        <th class="px-4 py-3 font-semibold text-center">Tổng số giao dịch</th>
                        <th class="px-4 py-3 font-semibold">Doanh thu thực thu</th>
                        <th class="px-4 py-3 font-semibold">Doanh thu chờ thu</th>
                        <th class="px-4 py-3 font-semibold text-right">Tác vụ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @foreach($methodSummary as $m)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                        <td class="px-4 py-3 font-semibold text-slate-800 dark:text-white">
                            @if($m['key'] === 'payos')
                                <span class="inline-flex items-center gap-1.5"><i class="bi bi-bank text-blue-600"></i> {{ $m['name'] }}</span>
                            @elseif($m['key'] === 'momo')
                                <span class="inline-flex items-center gap-1.5"><i class="bi bi-wallet2 text-pink-600"></i> {{ $m['name'] }}</span>
                            @else
                                <span class="inline-flex items-center gap-1.5"><i class="bi bi-cash text-amber-500"></i> {{ $m['name'] }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-slate-600 dark:text-slate-400 font-medium">
                            {{ number_format($m['count']) }}
                        </td>
                        <td class="px-4 py-3 font-bold text-emerald-600 dark:text-emerald-400">
                            {{ number_format($m['collected']) }}đ
                        </td>
                        <td class="px-4 py-3 text-amber-500 font-semibold">
                            {{ number_format($m['pending']) }}đ
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.transactions.index', ['payment_method' => $m['key']]) }}" class="text-primary-600 dark:text-primary-400 hover:underline font-medium">
                                Xem giao dịch <i class="bi bi-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-admin.card>
</div>
@endsection
```

- [ ] **Step 2: Test view compilation**

Run: `php artisan view:clear`
Expected: View cache cleared successfully.

- [ ] **Step 3: Commit**

```bash
git add resources/views/admin/finance/index.blade.php
git commit -m "feat(finance): add financial analytics dashboard UI with Chart.js"
```

---

### Task 7: Sidebar Integration & End-to-End Verification

**Files:**
- Modify: `resources/views/admin/layouts/admin.blade.php`
- Test: `tests/Feature/Admin/FinanceNavigationTest.php`

**Interfaces:**
- Produces: Integrated sidebar navigation with Finance & Transactions links and active states.

- [ ] **Step 1: Write navigation test**

```php
// tests/Feature/Admin/FinanceNavigationTest.php
namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FinanceNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_contains_finance_and_transaction_links()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee(route('admin.finance.index'));
        $response->assertSee(route('admin.transactions.index'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Admin/FinanceNavigationTest.php`
Expected: FAIL (Links not yet in layout).

- [ ] **Step 3: Update `admin/layouts/admin.blade.php`**

Add Finance & Transactions section to sidebar (around line 380 after Inventory or Orders section):
```blade
                <!-- Finance & Cashflow Section -->
                <div class="mt-4">
                    <button type="button" class="flex items-center justify-between w-full px-3 py-2 text-sm font-semibold text-slate-800 dark:text-slate-200 transition-colors group" aria-controls="dropdown-finance" data-collapse-toggle="dropdown-finance">
                        <span>Tài chính & Dòng tiền</span>
                        <i class="bi bi-chevron-down text-[10px] text-slate-400 transition-transform duration-200 {{ request()->routeIs('admin.finance.*') || request()->routeIs('admin.transactions.*') ? 'rotate-180' : 'group-data-[collapse-open]:rotate-180' }}"></i>
                    </button>
                    <ul id="dropdown-finance" class="{{ request()->routeIs('admin.finance.*') || request()->routeIs('admin.transactions.*') ? '' : 'hidden' }} space-y-1 py-1 mt-1">
                        <li>
                            <a href="{{ route('admin.finance.index') }}" class="flex items-center gap-3 px-3 py-2 pl-9 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.finance.*') ? 'text-primary-700 dark:text-primary-400 font-medium bg-primary-50/50 dark:bg-primary-900/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-200/50 dark:hover:bg-slate-700/50' }}">
                                <i class="bi bi-bar-chart-line text-base {{ request()->routeIs('admin.finance.*') ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400' }}"></i>
                                <span>Thống kê tài chính</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.transactions.index') }}" class="flex items-center gap-3 px-3 py-2 pl-9 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.transactions.*') ? 'text-primary-700 dark:text-primary-400 font-medium bg-primary-50/50 dark:bg-primary-900/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-200/50 dark:hover:bg-slate-700/50' }}">
                                <i class="bi bi-credit-card text-base {{ request()->routeIs('admin.transactions.*') ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400' }}"></i>
                                <span>Danh sách giao dịch</span>
                            </a>
                        </li>
                    </ul>
                </div>
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/Admin/FinanceNavigationTest.php`
Expected: PASS.

- [ ] **Step 5: Run Full Verification**

Run all tests:
```bash
php artisan test --filter=Admin
```
Run view cache verification:
```bash
php artisan view:cache
php artisan view:clear
```
Expected: All tests pass, 0 blade errors.

- [ ] **Step 6: Commit**

```bash
git add resources/views/admin/layouts/admin.blade.php tests/Feature/Admin/FinanceNavigationTest.php
git commit -m "feat(finance): add Finance & Transactions to admin sidebar"
```

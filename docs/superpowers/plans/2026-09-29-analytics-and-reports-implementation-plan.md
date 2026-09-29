# Analytics & Reports Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [x]`) syntax for tracking.

**Goal:** Build a business intelligence Analytics & Reports module (`/admin/analytics`) for Aurelia Store featuring flexible period-over-period comparisons, KPI growth rates, dual-axis revenue & order trend charts, product ranking & slow-moving alerts, and streamed UTF-8 BOM Excel/CSV reports.

**Architecture:** A dedicated `AnalyticsController` handles request routing and date resolution, delegating complex aggregation logic, period arithmetic, and export generation to an isolated `AnalyticsService`. The presentation layer utilizes a Blade view integrated with ApexCharts with automatic Light/Dark mode syncing.

**Tech Stack:** Laravel 12, PHP 8.2+, MySQL (Aiven SSL), Tailwind CSS, Flowbite, ApexCharts CDN, PHPUnit 11.

## Global Constraints

- Do not install new composer packages; use native PHP `StreamedResponse` with UTF-8 BOM (`\xEF\xBB\xBF`) for Excel/CSV compatibility.
- Ensure all admin routes are protected by `['auth', 'role:admin']`.
- All monetary values must be formatted according to Vietnamese Dong convention (`number_format($val, 0, ',', '.') . ' đ'`).
- Ensure ApexCharts dynamically adapts when toggling Dark/Light mode in the admin panel.

---

### Task 1: Analytics Routes, Base Controller & Permission Guard

**Files:**
- Create: `app/Http/Controllers/Admin/AnalyticsController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Admin/AnalyticsTest.php`

**Interfaces:**
- Produces: `route('admin.analytics.index')` returning `view('admin.analytics.index')`, protected by `['auth', 'role:admin']`.

- [x] **Step 1: Write the failing test for authorization and route response**

Create `tests/Feature/Admin/AnalyticsTest.php`:
```php
<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_analytics()
    {
        $response = $this->get('/admin/analytics');
        $response->assertRedirect('/login');
    }

    public function test_regular_users_cannot_access_analytics()
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($user)->get('/admin/analytics');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_analytics_index()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get('/admin/analytics');
        $response->assertStatus(200);
        $response->assertViewIs('admin.analytics.index');
    }
}
```

- [x] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Admin/AnalyticsTest.php`  
Expected: FAIL (Route or Controller not found).

- [x] **Step 3: Define routes and create minimal controller and view**

In `routes/web.php` inside `Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(...)`:
```php
    // Analytics & Reports
    Route::get('analytics', [Admin\AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('analytics/export', [Admin\AnalyticsController::class, 'export'])->name('analytics.export');
```

Create `app/Http/Controllers/Admin/AnalyticsController.php`:
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.analytics.index');
    }

    public function export(Request $request)
    {
        return response()->json(['status' => 'ok']);
    }
}
```

Create placeholder `resources/views/admin/analytics/index.blade.php`:
```blade
@extends('admin.layouts.admin')
@section('title', 'Phân tích & Báo cáo')
@section('page_title', 'Phân tích & Báo cáo')
@section('content')
<div class="p-4 sm:ml-64 mt-14">
    <h1 class="text-xl font-bold">Phân tích & Báo cáo</h1>
</div>
@endsection
```

- [x] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/Admin/AnalyticsTest.php`  
Expected: PASS (All 3 tests pass).

- [x] **Step 5: Commit**

```bash
git add app/Http/Controllers/Admin/AnalyticsController.php routes/web.php resources/views/admin/analytics/index.blade.php tests/Feature/Admin/AnalyticsTest.php
git commit -m "feat(analytics): add route, controller, placeholder view and auth tests"
```

---

### Task 2: AnalyticsService - Period Calculation & Growth Arithmetic

**Files:**
- Create: `app/Services/AnalyticsService.php`
- Test: `tests/Unit/Services/AnalyticsServiceTest.php`

**Interfaces:**
- Produces: `AnalyticsService::resolvePeriods(string $range, ?string $from, ?string $to): array` returning:
  `['current' => ['start' => Carbon, 'end' => Carbon], 'previous' => ['start' => Carbon, 'end' => Carbon], 'days' => int]`
- Produces: `AnalyticsService::calculateGrowth(float $current, float $previous): float`

- [x] **Step 1: Write unit tests for period calculation and growth rate**

Create `tests/Unit/Services/AnalyticsServiceTest.php`:
```php
<?php

namespace Tests\Unit\Services;

use App\Services\AnalyticsService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class AnalyticsServiceTest extends TestCase
{
    private AnalyticsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AnalyticsService();
    }

    public function test_calculate_growth_positive()
    {
        $growth = $this->service->calculateGrowth(120, 100);
        $this->assertEquals(20.0, $growth);
    }

    public function test_calculate_growth_negative()
    {
        $growth = $this->service->calculateGrowth(75, 100);
        $this->assertEquals(-25.0, $growth);
    }

    public function test_calculate_growth_from_zero()
    {
        $growth = $this->service->calculateGrowth(100, 0);
        $this->assertEquals(100.0, $growth);

        $growthZero = $this->service->calculateGrowth(0, 0);
        $this->assertEquals(0.0, $growthZero);
    }

    public function test_resolve_periods_last_7_days()
    {
        Carbon::setTestNow('2026-09-29 12:00:00');
        $periods = $this->service->resolvePeriods('last_7_days', null, null);

        $this->assertEquals('2026-09-23', $periods['current']['start']->toDateString());
        $this->assertEquals('2026-09-29', $periods['current']['end']->toDateString());
        $this->assertEquals(7, $periods['days']);

        // Equal duration previous period
        $this->assertEquals('2026-09-16', $periods['previous']['start']->toDateString());
        $this->assertEquals('2026-09-22', $periods['previous']['end']->toDateString());
    }
}
```

- [x] **Step 2: Run unit test to verify it fails**

Run: `php artisan test tests/Unit/Services/AnalyticsServiceTest.php`  
Expected: FAIL (Class `App\Services\AnalyticsService` does not exist).

- [x] **Step 3: Implement period and growth calculation in AnalyticsService**

Create `app/Services/AnalyticsService.php`:
```php
<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function resolvePeriods(string $range, ?string $fromDate, ?string $toDate): array
    {
        $now = Carbon::now();

        if ($range === 'custom' && $fromDate && $toDate) {
            $currentStart = Carbon::parse($fromDate)->startOfDay();
            $currentEnd = Carbon::parse($toDate)->endOfDay();
        } else {
            match ($range) {
                'today' => [
                    $currentStart = $now->copy()->startOfDay(),
                    $currentEnd = $now->copy()->endOfDay(),
                ],
                'last_30_days' => [
                    $currentStart = $now->copy()->subDays(29)->startOfDay(),
                    $currentEnd = $now->copy()->endOfDay(),
                ],
                'this_month' => [
                    $currentStart = $now->copy()->startOfMonth(),
                    $currentEnd = $now->copy()->endOfDay(),
                ],
                'this_year' => [
                    $currentStart = $now->copy()->startOfYear(),
                    $currentEnd = $now->copy()->endOfDay(),
                ],
                default => [ // last_7_days
                    $range = 'last_7_days',
                    $currentStart = $now->copy()->subDays(6)->startOfDay(),
                    $currentEnd = $now->copy()->endOfDay(),
                ],
            };
        }

        $durationDays = (int) $currentStart->copy()->startOfDay()->diffInDays($currentEnd->copy()->startOfDay()) + 1;
        $prevEnd = $currentStart->copy()->subDay()->endOfDay();
        $prevStart = $prevEnd->copy()->subDays($durationDays - 1)->startOfDay();

        return [
            'range' => $range,
            'days' => $durationDays,
            'current' => [
                'start' => $currentStart,
                'end' => $currentEnd,
            ],
            'previous' => [
                'start' => $prevStart,
                'end' => $prevEnd,
            ],
        ];
    }

    public function calculateGrowth(float $current, float $previous): float
    {
        if ($previous == 0.0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
```

- [x] **Step 4: Run unit test to verify it passes**

Run: `php artisan test tests/Unit/Services/AnalyticsServiceTest.php`  
Expected: PASS.

- [x] **Step 5: Commit**

```bash
git add app/Services/AnalyticsService.php tests/Unit/Services/AnalyticsServiceTest.php
git commit -m "feat(analytics): implement period resolution and growth rate arithmetic in AnalyticsService"
```

---

### Task 3: AnalyticsService - Aggregations (KPIs, Sales Trends, Product Ranking & Customer Cohorts)

**Files:**
- Modify: `app/Services/AnalyticsService.php`
- Modify: `app/Http/Controllers/Admin/AnalyticsController.php`
- Modify: `tests/Feature/Admin/AnalyticsTest.php`

**Interfaces:**
- Produces: `AnalyticsService::getAnalyticsData(string $range, ?string $from, ?string $to): array` returning:
  - `periods`: date boundaries and range name
  - `kpis`: revenue, orders, cancelled, AOV, customers (current, previous, growth %)
  - `trendChart`: daily timeline dates, revenue values, order values
  - `categoryChart`: labels and series for revenue share
  - `customerChart`: new vs returning percentages
  - `topProducts`: collection of top 10 items
  - `slowProducts`: collection of dead stock items
  - `categoryStats`: detailed category breakdown collection

- [x] **Step 1: Write Feature test asserting analytics data structure and calculations**

Add to `tests/Feature/Admin/AnalyticsTest.php`:
```php
    public function test_analytics_returns_correct_kpis_and_aggregations()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'user', 'created_at' => now()]);

        $order = \App\Models\Order::create([
            'user_id' => $customer->id,
            'order_code' => 'ORD-TEST-1',
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
            'customer_phone' => '0987654321',
            'shipping_address' => 'Hanoi, Vietnam',
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => 'cod',
            'total_amount' => 500000,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/analytics?range=last_7_days');
        $response->assertStatus(200);
        $response->assertViewHas('data');

        $data = $response->viewData('data');
        $this->assertEquals(500000, $data['kpis']['revenue']['current']);
        $this->assertEquals(1, $data['kpis']['orders']['current']);
        $this->assertEquals(500000, $data['kpis']['aov']['current']);
        $this->assertNotEmpty($data['trendChart']['labels']);
    }
```

- [x] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Admin/AnalyticsTest.php`  
Expected: FAIL (`assertViewHas('data')` fails).

- [x] **Step 3: Implement data aggregation methods in AnalyticsService**

In `app/Services/AnalyticsService.php`, add methods:
- `getAnalyticsData(string $range, ?string $from, ?string $to)`
- `calculateKpis(array $periods)`
- `getDailyTrends(array $currentPeriod)`
- `getCategoryDistribution(array $currentPeriod)`
- `getCustomerCohortStats(array $currentPeriod)`
- `getTopAndSlowProducts(array $currentPeriod)`

(Full query implementations using Eloquent models `Order`, `OrderItem`, `Product`, `ProductVariant`, `User`, `Category`).

- [x] **Step 4: Update AnalyticsController to pass service data to view**

In `app/Http/Controllers/Admin/AnalyticsController.php`:
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function index(Request $request)
    {
        $range = $request->input('range', 'last_7_days');
        $from = $request->input('from_date');
        $to = $request->input('to_date');

        $data = $this->analyticsService->getAnalyticsData($range, $from, $to);

        return view('admin.analytics.index', compact('data'));
    }
}
```

- [x] **Step 5: Run tests to verify they pass**

Run: `php artisan test tests/Feature/Admin/AnalyticsTest.php`  
Expected: PASS.

- [x] **Step 6: Commit**

```bash
git add app/Services/AnalyticsService.php app/Http/Controllers/Admin/AnalyticsController.php tests/Feature/Admin/AnalyticsTest.php
git commit -m "feat(analytics): implement data aggregation pipeline for KPIs, trends, products and cohorts"
```

---

### Task 4: Analytics View & ApexCharts Integration with Dark/Light Mode

**Files:**
- Modify: `resources/views/admin/analytics/index.blade.php`
- Modify: `resources/views/admin/layouts/admin.blade.php` (attach route to sidebar line 305)

**Interfaces:**
- Produces: Interactive analytical UI with 4 KPI cards, dual-axis ApexCharts, 2 distribution charts, and 3 data tabs.
- Produces: Sidebar link pointing to `route('admin.analytics.index')` with active state styling.

- [x] **Step 1: Update admin sidebar in admin.blade.php**

In `resources/views/admin/layouts/admin.blade.php` around line 300:
Update the Analytics sidebar link from `href="#"` to:
```blade
<li>
    <a href="{{ route('admin.analytics.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition-all duration-200 group {{ request()->routeIs('admin.analytics.*') ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-slate-200 font-normal' }}">
        <div class="flex items-center gap-3 text-sm">
            <i class="bi bi-bar-chart text-base {{ request()->routeIs('admin.analytics.*') ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-500' }}"></i>
            <span>Analytics</span>
        </div>
    </a>
</li>
```

- [x] **Step 2: Construct full Analytics Blade view**

In `resources/views/admin/analytics/index.blade.php`:
- Filter toolbar with presets (`today`, `last_7_days`, `last_30_days`, `this_month`, `this_year`) and custom date inputs.
- 4 KPI growth cards with percentage badges (`+X.X%` / `-X.X%`) and prior period baseline comparison.
- Dual-axis mixed chart: Bar for Revenue (Trục trái) + Smooth Line for Orders count (Trục phải).
- Category Donut Chart and Customer Cohort Donut Chart.
- 3 Tabs: Top 10 Bán Chạy, ⚠️ Sản Phẩm Bán Chậm, 📁 Hiệu Suất Danh Mục.
- ApexCharts theme listener reacting to `document.documentElement.classList.contains('dark')`.

- [x] **Step 3: Test rendering and route verification**

Run: `php artisan test tests/Feature/Admin/AnalyticsTest.php`  
Expected: PASS.

- [x] **Step 4: Commit**

```bash
git add resources/views/admin/analytics/index.blade.php resources/views/admin/layouts/admin.blade.php
git commit -m "feat(analytics): build analytics dashboard UI with ApexCharts and sidebar integration"
```

---

### Task 5: Export Engine - Streamed UTF-8 BOM CSV/Excel Report

**Files:**
- Modify: `app/Services/AnalyticsService.php`
- Modify: `app/Http/Controllers/Admin/AnalyticsController.php`
- Modify: `tests/Feature/Admin/AnalyticsTest.php`

**Interfaces:**
- Produces: `AnalyticsService::streamCsvReport(array $data): StreamedResponse`
- Produces: `route('admin.analytics.export')` triggering immediate download of `bao_cao_analytics_aurelia_[from]_[to].csv`.

- [x] **Step 1: Write test for CSV export endpoint**

Add to `tests/Feature/Admin/AnalyticsTest.php`:
```php
    public function test_admin_can_export_analytics_csv()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/analytics/export?range=last_7_days');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        
        $content = $response->streamedContent();
        // Check for UTF-8 BOM bytes (\xEF\xBB\xBF)
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('BÁO CÁO PHÂN TÍCH KINH DOANH', $content);
        $this->assertStringContainsString('Doanh thu thuần', $content);
    }
```

- [x] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Admin/AnalyticsTest.php`  
Expected: FAIL (JSON was returned instead of CSV stream).

- [x] **Step 3: Implement streamCsvReport in AnalyticsService and AnalyticsController**

In `app/Services/AnalyticsService.php`:
```php
    public function streamCsvReport(array $data): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'bao_cao_analytics_aurelia_' . $data['periods']['current']['start']->format('Ymd') . '_' . $data['periods']['current']['end']->format('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($data) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM
            fputs($handle, "\xEF\xBB\xBF");

            // 1. Report Header
            fputcsv($handle, ['AURELIA STORE - BÁO CÁO PHÂN TÍCH HOẠT ĐỘNG KINH DOANH']);
            fputcsv($handle, ['Khoảng thời gian:', $data['periods']['current']['start']->format('d/m/Y') . ' - ' . $data['periods']['current']['end']->format('d/m/Y')]);
            fputcsv($handle, ['Kỳ so sánh liền trước:', $data['periods']['previous']['start']->format('d/m/Y') . ' - ' . $data['periods']['previous']['end']->format('d/m/Y')]);
            fputcsv($handle, ['Thời điểm xuất:', now()->format('d/m/Y H:i:s')]);
            fputcsv($handle, []);

            // 2. KPIs Summary
            fputcsv($handle, ['=== 1. TỔNG HỢP CHỈ SỐ KINH DOANH (KPIs) ===']);
            fputcsv($handle, ['Chỉ số', 'Kỳ hiện tại', 'Kỳ trước', 'Tăng trưởng (%)']);
            fputcsv($handle, ['Doanh thu thuần (VNĐ)', $data['kpis']['revenue']['current'], $data['kpis']['revenue']['previous'], $data['kpis']['revenue']['growth'] . '%']);
            fputcsv($handle, ['Số đơn hoàn tất', $data['kpis']['orders']['current'], $data['kpis']['orders']['previous'], $data['kpis']['orders']['growth'] . '%']);
            fputcsv($handle, ['Giá trị đơn trung bình (AOV)', $data['kpis']['aov']['current'], $data['kpis']['aov']['previous'], $data['kpis']['aov']['growth'] . '%']);
            fputcsv($handle, ['Khách hàng mới', $data['kpis']['customers']['new_current'], $data['kpis']['customers']['new_previous'], $data['kpis']['customers']['new_growth'] . '%']);
            fputcsv($handle, []);

            // 3. Daily Breakdown
            fputcsv($handle, ['=== 2. CHI TIẾT DOANH THU THEO NGÀY ===']);
            fputcsv($handle, ['Ngày', 'Doanh thu (VNĐ)', 'Số đơn']);
            foreach ($data['trendChart']['labels'] as $idx => $label) {
                fputcsv($handle, [$label, $data['trendChart']['revenue'][$idx] ?? 0, $data['trendChart']['orders'][$idx] ?? 0]);
            }
            fputcsv($handle, []);

            // 4. Top 10 Products
            fputcsv($handle, ['=== 3. TOP 10 SẢN PHẨM BÁN CHẠY ===']);
            fputcsv($handle, ['Mã SP', 'Tên sản phẩm', 'Danh mục', 'Số lượng bán', 'Doanh thu đóng góp (VNĐ)', 'Tồn kho']);
            foreach ($data['topProducts'] as $prod) {
                fputcsv($handle, [$prod->sku ?? 'N/A', $prod->name, $prod->category_name ?? 'N/A', $prod->total_sold, $prod->total_revenue, $prod->total_stock]);
            }

            fclose($handle);
        }, 200, $headers);
    }
```

In `app/Http/Controllers/Admin/AnalyticsController.php`:
```php
    public function export(Request $request)
    {
        $range = $request->input('range', 'last_7_days');
        $from = $request->input('from_date');
        $to = $request->input('to_date');

        $data = $this->analyticsService->getAnalyticsData($range, $from, $to);

        return $this->analyticsService->streamCsvReport($data);
    }
```

- [x] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/Admin/AnalyticsTest.php`  
Expected: PASS.

- [x] **Step 5: Commit**

```bash
git add app/Services/AnalyticsService.php app/Http/Controllers/Admin/AnalyticsController.php tests/Feature/Admin/AnalyticsTest.php
git commit -m "feat(analytics): implement streamed UTF-8 BOM CSV export for analytics reports"
```

---

### Task 6: Final Verification & Push to Production

**Files:**
- Verification: Run all test suites
- Git: Push to `origin main` to trigger Render deployment

- [x] **Step 1: Run complete test suite**

Run: `php artisan test`  
Expected: PASS (All tests pass without regression).

- [x] **Step 2: Push changes to GitHub**

```bash
git push origin main
```
Expected: Successfully pushed to `origin main`, triggering automated Render deployment.

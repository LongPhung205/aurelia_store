# Implementation Plan: Analytics Polish, Actionable Dashboard Cockpit & Storefront Apriori Integration

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Transform the store's analytics and operations experience by (A) perfecting `/admin/analytics` with data science tooltips, lifetime badges, and anti-slop empty states; (B) refactoring the main `/admin` dashboard into a real-time actionable operations cockpit; and (C) bringing Apriori market basket intelligence to the storefront with a high-converting "Frequently Bought Together" combo widget.

**Architecture:** 
- `RfmAnalyticsService` & `MarketBasketMiningService` provide structured metrics and recommendation pairs.
- `DashboardController` is streamlined to eliminate overlap with `AnalyticsController`, focusing on daily operational urgency (pending orders, critical low stock, instant action shortcuts).
- `ProductController` (Client) integrates `MarketBasketMiningService::getFrequentlyBoughtTogether()` and serves an interactive Alpine.js combo builder directly on `/products/{slug}`.
- Visuals adhere to `design-taste-frontend` and `create-admin-ui` standards: tabular figures, subtle frosted glass cards, dynamic light/dark mode adaptation, and zero broken image placeholders.

**Tech Stack:** Laravel 12, PHP 8.2+, MySQL, Tailwind CSS, Alpine.js, ApexCharts, PHPUnit 11.

---

## Global Constraints

- Never use third-party composer packages for data mining or RFM math; keep service logic pure PHP.
- All monetary amounts must format in Vietnamese Dong (`number_format(..., 0, ',', '.') . ' đ'`).
- Image URLs must always resolve with `asset('storage/' . ltrim($path, '/'))` (if relative) and include `onerror` fallback SVG handlers.
- Ensure strict MySQL 8.0 `ONLY_FULL_GROUP_BY` and SQLite testing compatibility.
- Ensure all admin endpoints require `['auth', 'role:admin']`.

---

### Task 1: Phân hệ A - Tối ưu UI/UX, Tooltip Khoa học Dữ liệu & Tính Thống Nhất tại `/admin/analytics`

**Files:**
- Modify: `resources/views/admin/analytics/index.blade.php`
- Test: `tests/Feature/Admin/AnalyticsAdvancedTest.php`

**Interfaces:**
- Produces: Dynamic filter header (shows date range selector only on `?tab=sales`; shows Lifetime Dataset info banner on `?tab=rfm` and `?tab=basket`).
- Produces: Rich interactive Tooltips for RFM segments (Champions, Loyal, At Risk, Lost) and Apriori metrics (Support, Confidence, Lift ratio).
- Produces: Premium empty states with vector badge illustrations for empty rules and zero customer cohorts.
- Produces: Tabular figures styling (`tabular-nums font-mono`) for precision analytics readability.

- [x] **Step 1: Write Feature test asserting tooltips and dynamic header rendering**

In `tests/Feature/Admin/AnalyticsAdvancedTest.php`, add test cases asserting:
1. `?tab=rfm` contains lifetime dataset notice and RFM tooltip triggers.
2. `?tab=basket` contains Apriori Lift explanation tooltips and combo triggers.
3. Empty state renders gracefully with illustrated placeholder when no data exists.

- [x] **Step 2: Run test to observe baseline state**

Run: `php artisan test tests/Feature/Admin/AnalyticsAdvancedTest.php`

- [x] **Step 3: Implement UI enhancements in `resources/views/admin/analytics/index.blade.php`**

1. **Context-aware Header**: Wrap date filter form with `@if($activeTab === 'sales') ... @else ... @endif`. On RFM and Basket tabs, display a sleek info banner:
   ```html
   <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-indigo-50/80 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/60 text-xs font-medium">
       <i class="bi bi-cpu text-sm"></i>
       <span>Dữ liệu được phân tích trên toàn bộ lịch sử giao dịch tích lũy (Lifetime Dataset)</span>
   </div>
   ```
2. **Interactive Tooltips**:
   - Add hover tooltips for **Support** (*"Tỷ lệ đơn hàng chứa cả 2 sản phẩm"*), **Confidence** (*"Xác suất mua kèm B khi đã chọn A"*), and **Lift** (*"Mức độ tăng cơ hội mua chéo (> 1.0 nghĩa là tương quan thuận rõ rệt)"*).
   - Add hover tooltips on RFM Cohort cards explaining the strategic meaning and recommended marketing action.
3. **Taste & Typography**:
   - Apply `tabular-nums tracking-tight` to currency amounts and metric numbers.
   - Refine card borders to `border-slate-200/80 dark:border-slate-700/80` with subtle shadows.
4. **Premium Empty States**:
   - When `$basketData['rules']` is empty, replace plain text with a modern centered card containing a shopping bag icon, progress info, and tips for generating cross-sell data.

- [x] **Step 4: Run tests to verify all tests pass**

Run: `php artisan test tests/Feature/Admin/AnalyticsAdvancedTest.php`  
Expected: PASS.

- [x] **Step 5: Commit Task 1**

```bash
git add resources/views/admin/analytics/index.blade.php tests/Feature/Admin/AnalyticsAdvancedTest.php
git commit -m "feat(analytics): add data science tooltips, lifetime dataset badge, and polished empty states"
```

---

### Task 2: Phân hệ B - Tái cấu trúc Dashboard Admin (`/admin`) thành "Actionable Operations Cockpit"

**Files:**
- Modify: `app/Http/Controllers/Admin/DashboardController.php`
- Modify: `resources/views/admin/dashboard/index.blade.php`
- Create/Modify: `tests/Feature/Admin/DashboardTest.php`

**Interfaces:**
- Produces: `DashboardController@index` with Today's Operational Pulse (`today_revenue`, `today_orders`, `pending_fulfillment_count`, `critical_low_stock_count`).
- Produces: Urgent Action Center (`pendingOrders` with customer phone, address, and 1-click status update / GHN dispatch; `criticalStockProducts` with stock $\le 5$ and 1-click "Tạo phiếu nhập hàng").
- Produces: Direct CTA link to `/admin/analytics` with teaser metrics.

- [x] **Step 1: Write Feature test for Dashboard operational cockpit metrics**

Create/Update `tests/Feature/Admin/DashboardTest.php`:
Assert that the admin dashboard returns 200, contains today's metrics, pending orders list, and critical stock products.

- [x] **Step 2: Update `DashboardController.php` to calculate operational cockpit data**

Refactor `app/Http/Controllers/Admin/DashboardController.php`:
1. Calculate today's pulse: `todayRevenue`, `todayOrdersCount`, `pendingOrdersCount` (`status = 'pending'`), `criticalStockCount` (`stock_quantity <= 5`).
2. Fetch top 5 pending orders with eager-loaded user for immediate fulfillment.
3. Fetch top 5 critical low stock product variants (`stock_quantity <= 5`) with product and primary image.
4. Keep the 7-day mini trend sparkline for quick glance, but eliminate the redundant 30-day heavy chart and top seller duplication.

- [x] **Step 3: Redesign `resources/views/admin/dashboard/index.blade.php` with Taste Skill**

1. **Header Cockpit**: Quick status greeting, real-time date clock, and button linking to `/admin/analytics`.
2. **4 Bento Metric Cards**:
   - Doanh thu hôm nay (so sánh với hôm qua).
   - Đơn hàng cần xử lý ngay (`pending` - badge cảnh báo màu hổ phách).
   - Sản phẩm nguy cấp sắp hết kho (`stock <= 5` - badge cảnh báo màu đỏ hồng).
   - Khách hàng mới đăng ký trong tuần.
3. **Urgent Action Center (2 Cột Bento)**:
   - Cột trái (Đơn hàng cần đóng gói & xuất kho): Bảng đơn hàng `pending` kèm nút "Chi tiết / Xác nhận GHN".
   - Cột phải (Cảnh báo kho hàng khẩn cấp): Danh sách sản phẩm sắp hết kho kèm nút "Nhập hàng".
4. **Analytics Teaser Banner**: Khối banner sang trọng mời gọi khám phá Tab RFM và Tab Apriori với 1 click.

- [x] **Step 4: Run dashboard tests to verify they pass**

Run: `php artisan test tests/Feature/Admin/DashboardTest.php`  
Expected: PASS.

- [x] **Step 5: Commit Task 2**

```bash
git add app/Http/Controllers/Admin/DashboardController.php resources/views/admin/dashboard/index.blade.php tests/Feature/Admin/DashboardTest.php
git commit -m "feat(dashboard): transform admin dashboard into actionable operations cockpit"
```

---

### Task 3: Phân hệ C (Backend) - Apriori Frequently Bought Together Service & Controller Integration

**Files:**
- Modify: `app/Services/MarketBasketMiningService.php`
- Modify: `app/Http/Controllers/Client/ProductController.php`
- Test: `tests/Unit/Services/MarketBasketMiningServiceTest.php`
- Test: `tests/Feature/Client/ProductDetailFrequentlyBoughtTest.php`

**Interfaces:**
- Produces: `MarketBasketMiningService::getFrequentlyBoughtTogether(int $productId, int $limit = 1): ?array`
  - Returns `antecedent` ($p_1$), `consequent` ($p_2$), `total_original_price`, `bundle_discount_percent` (e.g. 5%), `bundle_price`, `savings_amount`, `lift`, and `confidence`.
- Produces: `ProductController@show` passing `$frequentlyBoughtTogether` into `client.products.show`.

- [x] **Step 1: Write Unit test for `getFrequentlyBoughtTogether` in `MarketBasketMiningServiceTest.php`**

Assert that given product A and an association rule $\{A\} \Rightarrow \{B\}$, calling `getFrequentlyBoughtTogether($pA->id)` returns the paired product B with bundle calculations and formatted images.

- [x] **Step 2: Implement `getFrequentlyBoughtTogether` in `MarketBasketMiningService.php`**

```php
public function getFrequentlyBoughtTogether(int $productId, int $limit = 1): ?array
{
    // Search mined rules where antecedent_id = $productId OR consequent_id = $productId
    // Pick the pairing with the highest Lift and Confidence
    // Calculate bundle price with 5% promotional discount
}
```

- [x] **Step 3: Update `app/Http/Controllers/Client/ProductController.php`**

Inject `MarketBasketMiningService $basketService` into `ProductController@show`, compute `$frequentlyBoughtTogether = $this->basketService->getFrequentlyBoughtTogether($product->id)`, and pass it to the view.

- [x] **Step 4: Write Feature test asserting product detail page returns 200 and includes combo data**

Create `tests/Feature/Client/ProductDetailFrequentlyBoughtTest.php` asserting that when an association rule exists, the product detail page renders the combo section.

- [x] **Step 5: Run tests to verify they pass**

Run: `php artisan test tests/Unit/Services/MarketBasketMiningServiceTest.php tests/Feature/Client/ProductDetailFrequentlyBoughtTest.php`  
Expected: PASS.

- [x] **Step 6: Commit Task 3**

```bash
git add app/Services/MarketBasketMiningService.php app/Http/Controllers/Client/ProductController.php tests/Unit/Services/MarketBasketMiningServiceTest.php tests/Feature/Client/ProductDetailFrequentlyBoughtTest.php
git commit -m "feat(storefront): integrate Apriori frequently bought together service into product detail controller"
```

---

### Task 4: Phân hệ C (Frontend) - Storefront "Thường Được Mua Cùng Nhau" (Frequently Bought Together) UI with Taste

**Files:**
- Modify: `resources/views/client/products/show.blade.php`
- Modify: `app/Http/Controllers/Client/CartController.php` (support batch adding combo or add-combo route)
- Test: `tests/Feature/Client/ProductDetailFrequentlyBoughtTest.php`

**Interfaces:**
- Produces: "Thường Được Mua Cùng Nhau" widget rendered directly below product specs/options on `/products/{slug}`.
- Produces: 2 product cards connected by a `+` badge, interactive checkboxes, dynamic price calculation, and 1-click "Thêm Cả Combo Vào Giỏ" button.
- Produces: Alpine.js handler that adds selected items to the cart and triggers cart badge counter update with toast notification.

- [x] **Step 1: Implement Batch/Combo Add to Cart in `CartController.php`**

Add endpoint `CartController@addCombo` (accepts array of `{variant_id, quantity}`) so customer can add both items in a single atomic HTTP request.

- [x] **Step 2: Design and embed Frequently Bought Together component in `resources/views/client/products/show.blade.php`**

Implement the widget following `design-taste-frontend` rules:
- Soft rounded card with subtle rose/brand accent border (`border-rose-100 dark:border-rose-950/40 bg-white shadow-sm`).
- Layout: Product A (Current item) `+` Product B (Apriori Recommended Combo item) `=` Combined Pricing Card.
- Include variant selector dropdown for Product B (e.g. Size/Color) if it has multiple variants.
- Real-time Alpine.js price tally: If customer unchecks Product B, price reverts to single product.
- CTA Button: `"Thêm Cả 2 Vào Giỏ (Tiết kiệm 5%)"` with loading spinner indicator.

- [x] **Step 3: Run client product detail and cart tests**

Run: `php artisan test tests/Feature/Client/ProductDetailFrequentlyBoughtTest.php`  
Expected: PASS.

- [x] **Step 4: Commit Task 4**

```bash
git add resources/views/client/products/show.blade.php app/Http/Controllers/Client/CartController.php routes/web.php
git commit -m "feat(storefront): build interactive frequently bought together combo widget on product detail page"
```

---

### Task 5: Final Verification, Comprehensive Regression & Push to Production

**Files:**
- Verification: Complete test suite (`php artisan test`)
- Verification: Browser visual smoke check on `/admin/analytics`, `/admin`, and `/products/{slug}`
- Git: Push to `origin main`

- [x] **Step 1: Run complete test suite**

Run: `php artisan test`  
Expected: All tests pass (0 failures, 0 errors).

- [x] **Step 2: Push changes to GitHub**

```bash
git push origin main
```
Expected: Pushed to `origin main`, triggering automated Render deployment.

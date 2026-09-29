# Design Specification: Analytics & Reports Module (`/admin/analytics`)

- **Author:** AI Senior Architect & Antigravity IDE
- **Date:** 2026-09-29
- **Status:** Approved
- **Target Release:** v1.2.0

---

## 1. Executive Summary & Vision

### 1.1. Context & Business Problem
Aurelia Store currently provides two operational admin views:
1. **Admin Dashboard (`/admin`):** Answers *"What is happening right now?"* (Current open orders, daily snapshot, operational shortcuts, low stock alerts).
2. **Finance Dashboard (`/admin/finance`):** Answers *"What is our cash flow and payment reconciliation?"* (Collected cash, pending PayOS/Momo/COD settlements).

The navigation sidebar has an **Analytics** item pointing to a dead link (`#`). There is no centralized module answering strategic business questions:
- *"How are our sales and customer acquisition changing over time?"*
- *"Why did revenue increase or drop compared to the previous period?"*
- *"Which products are driving profit versus which inventory is sitting idle and incurring holding costs?"*
- *"What is our customer retention rate?"*

### 1.2. Solution Overview
The **Analytics & Reports** module (`/admin/analytics`) will be an analytical business intelligence center featuring:
- **Flexible Time Horizons & Period-over-Period Comparison:** 7 days, 30 days, Month-to-date, Year-to-date, and Custom Date Range, automatically benchmarked against the immediately preceding period of equal duration.
- **Strategic KPI Growth Cards:** Net Revenue, Completed Orders & Cancellation Rate, Average Order Value (AOV), New vs. Returning Customers with `%` growth indicators.
- **Interactive Visualizations (ApexCharts):** Dual-axis daily revenue & volume trend, Category contribution donut, and Customer cohort radial distribution with full Dark/Light mode synchronization.
- **Actionable Inventory Intelligence:** Top 10 Best Sellers and Slow-Moving Products table.
- **Export Engine:** One-click UTF-8 BOM CSV/Excel export formatted for immediate presentation and analysis.

---

## 2. Architecture & Data Flow

```
                      ┌────────────────────────────┐
                      │    HTTP Request (/admin)   │
                      └──────────────┬─────────────┘
                                     │
                                     ▼
                      ┌────────────────────────────┐
                      │    AnalyticsController     │
                      │  (Validate & Resolve Date) │
                      └──────────────┬─────────────┘
                                     │
                                     ▼
                      ┌────────────────────────────┐
                      │      AnalyticsService      │
                      │   - resolvePeriods()       │
                      │   - getKpisWithGrowth()    │
                      │   - getDailyTrends()       │
                      │   - getCategoryShare()     │
                      │   - getCustomerCohorts()   │
                      │   - getProductRankings()   │
                      └──────┬──────────────┬──────┘
                             │              │
              ┌──────────────┘              └──────────────┐
              ▼                                            ▼
┌───────────────────────────┐                ┌───────────────────────────┐
│     Blade View Index      │                │    Streamed CSV Export    │
│ (ApexCharts + Data Tabs)  │                │  (UTF-8 BOM Excel Format) │
└───────────────────────────┘                └───────────────────────────┘
```

### 2.1. File Structure & Boundaries
- **Route:** `routes/web.php` under prefix `admin` with middleware `['auth', 'role:admin']`:
  - `GET /admin/analytics` -> `Admin\AnalyticsController@index` (`admin.analytics.index`)
  - `GET /admin/analytics/export` -> `Admin\AnalyticsController@export` (`admin.analytics.export`)
- **Controller:** `app/Http/Controllers/Admin/AnalyticsController.php`
- **Service:** `app/Services/AnalyticsService.php` (Encapsulates all SQL aggregation queries, date arithmetic, and percentage math)
- **View:** `resources/views/admin/analytics/index.blade.php`
- **Sidebar Integration:** `resources/views/admin/layouts/admin.blade.php` (Update line 305 to active route link)

---

## 3. Data Aggregation & Mathematical Formulations

### 3.1. Period Arithmetic (Equal-Duration Prior Comparison)
Given a current period bounded by `[$startDate, $endDate]`:
1. Calculate period duration in days:
   $$\text{Duration (days)} = D = \text{endDate} - \text{startDate} + 1$$
2. Derive previous comparison period:
   $$\text{prevEndDate} = \text{startDate} - 1 \text{ day}$$
   $$\text{prevStartDate} = \text{prevEndDate} - (D - 1) \text{ days}$$

Example:
- Current: `2026-09-01` to `2026-09-30` (30 days)
- Previous: `2026-08-02` to `2026-08-31` (30 days)

### 3.2. Growth Percentage Formula
$$\text{Growth \%} = \begin{cases} 
\frac{\text{Current} - \text{Previous}}{\text{Previous}} \times 100 & \text{if Previous} > 0 \\
100.0\% & \text{if Previous} = 0 \text{ and Current} > 0 \\
0.0\% & \text{if Previous} = 0 \text{ and Current} = 0
\end{cases}$$

### 3.3. Key Metric Definitions

| Metric | Source Table & Condition | Formula |
| :--- | :--- | :--- |
| **Net Revenue** | `orders` where (`payment_status = 'paid'` OR `status = 'completed'`) AND `status != 'cancelled'` | `SUM(total_amount)` |
| **Completed Orders** | `orders` satisfying revenue conditions above | `COUNT(id)` |
| **Cancellation Rate** | `orders` where `status = 'cancelled'` | `(Cancelled / Total Orders) * 100` |
| **Average Order Value (AOV)** | Calculated from above | `Net Revenue / Completed Orders` |
| **New Customers** | `users` where `role = 'user'` and `created_at` in period | `COUNT(id)` |
| **Returning Customers** | `orders` in period whose `user_id` has $\ge 1$ order before current order | `COUNT(DISTINCT user_id)` |
| **Customer Retention Rate**| Fraction of ordering users who are returning | `(Returning / Total Active Buyers) * 100` |

### 3.4. Product Performance Metrics
1. **Top Selling Products (Top 10):**
   - Query: `order_items` joined with eligible `orders`.
   - Group by: `order_items.product_id`.
   - Aggregations: `SUM(order_items.quantity) as total_sold`, `SUM(order_items.total) as total_revenue`.
   - Order: `total_revenue DESC`, limit 10.
   - Eager load: `product.primaryVariant`, `product.images`, `product.categories`.
2. **Slow Moving Products (Dead Stock Alert):**
   - Query: Products with total variant inventory `SUM(product_variants.stock_quantity) > 0`.
   - Left join with `order_items` sold within the current period.
   - Condition: Sold quantity in period is `0` or `< 3`.
   - Sorted by: Current `stock_quantity DESC` (highest locked capital first).

---

## 4. UI/UX Specification

### 4.1. Filter Toolbar
- **Preset Buttons:** `Hôm nay` (today), `7 ngày qua` (last_7_days), `30 ngày qua` (last_30_days), `Tháng này` (this_month), `Năm nay` (this_year).
- **Date Inputs:** From date (`from_date`), To date (`to_date`), Submit button (`Lọc dữ liệu`).
- **Export CTA:** Green outline button `Xuất Báo Cáo Excel` with download icon.

### 4.2. KPI Metric Cards (4 Cards Grid)
1. **Doanh Thu Thuần:** Currency formatted (`X.XXX.XXX đ`), growth badge, baseline subtext.
2. **Đơn Hàng Thành Công:** Order count, growth badge, cancellation rate badge (`Hủy: X.X%`).
3. **Giá Trị Đơn Trung Bình (AOV):** Currency per order (`XXX.XXX đ/đơn`), growth badge.
4. **Khách Hàng & Giữ Chân:** New customers count, Returning customer rate (`X%`).

### 4.3. Charts (ApexCharts)
- **Chart 1: Revenue & Order Trend (Mixed Dual Axis):**
  - Left Y-Axis: Revenue in VNĐ (Formatted as Millions/Thousands).
  - Right Y-Axis: Orders Count (Integer).
  - Series 1: Bar chart for daily revenue.
  - Series 2: Smooth line with data points for order count.
- **Chart 2: Category Revenue Breakdown (Donut Chart):**
  - Distribution of sales by primary category.
- **Chart 3: Customer Acquisition vs Retention (RadialBar / Donut):**
  - % New customers vs % Returning repeat customers.
- **Theme Synchronization:**
  - Observes `document.documentElement.classList.contains('dark')` and dispatches `updateOptions({ theme: { mode: 'dark'|'light' } })`.

### 4.4. Multi-Tab Product & Category Table
- **Tab 1: ⭐ Top 10 Bán Chạy:** Thumbnail, SKU, Product Title, Category, Sold Qty, Revenue, Current Stock.
- **Tab 2: ⚠️ Sản phẩm Bán Chậm (Cảnh báo tồn kho):** SKU, Product Title, In-Stock Qty, Sold in Period, Status recommendation tag (Chạy Flash Sale / Giảm giá).
- **Tab 3: 📁 Hiệu Suất Danh Mục:** Category Name, Total Products, Items Sold, Total Revenue, % Revenue Share.

---

## 5. Export Report Specification (Excel / CSV UTF-8)

- **Endpoint:** `GET /admin/analytics/export?range=...&from_date=...&to_date=...`
- **Output:** Streamed response, `Content-Type: text/csv; charset=UTF-8`, `Content-Disposition: attachment; filename="bao_cao_analytics_aurelia_[from]_[to].csv"`.
- **Encoding:** File prepended with UTF-8 BOM (`\xEF\xBB\xBF`) ensuring Microsoft Excel opens Vietnamese text without encoding errors.
- **Report Sections:**
  1. Title & Filter metadata header.
  2. KPI Overview & Period Comparison Table.
  3. Daily Revenue & Order Breakdown Table.
  4. Top 10 Performing Products Table.
  5. Slow-Moving Inventory Alert Table.

---

## 6. Testing & Quality Assurance Plan

1. **Feature Tests (`tests/Feature/Admin/AnalyticsTest.php`):**
   - Guest cannot access `/admin/analytics` (redirects to login).
   - Regular user (`role: user`) receives 403 Forbidden.
   - Admin user receives 200 OK and sees analytics dashboard.
   - Filter parameter tests (`range=last_7_days`, `range=this_month`, `range=custom`).
   - Export endpoint returns 200, valid CSV headers, UTF-8 BOM, and matching rows.
2. **Visual Verification:**
   - Verify ApexCharts render cleanly in both Light and Dark mode.
   - Verify sidebar "Analytics" link is highlighted when visiting `/admin/analytics`.

# Design Specification: Advanced Analytics - RFM Customer Segmentation & Market Basket Analysis

- **Author:** AI Senior Architect & Antigravity IDE
- **Date:** 2026-09-29
- **Status:** Approved
- **Target Release:** v1.3.0
- **Scope:** `/admin/analytics` Sub-tabs: RFM Segmentation & Behavior + Market Basket Analysis (Apriori)

---

## 1. Executive Summary & Vision

### 1.1. Context & Business Need
Aurelia Store currently provides baseline analytical metrics on `/admin/analytics` (Sales Trends, Period-over-Period comparisons, and Top/Slow Moving Inventory). To truly differentiate Analytics from operational Dashboards and provide actionable business intelligence, the platform needs algorithmic capabilities:
1. **Customer RFM Segmentation:** Moving beyond aggregate customer counts to behavioral clustering (Recency, Frequency, Monetary value).
2. **Product Purchase Behavior:** Linking customer segments to their preferred product categories to drive personalized marketing and retention campaigns.
3. **Market Basket Analysis (Apriori Algorithm):** Discovering statistical association rules between co-purchased items ($\{A\} \Rightarrow \{B\}$) based on Support, Confidence, and Lift to power cross-selling and bundling strategies.

### 1.2. Architecture Overview
To maintain a clean and responsive user experience without cluttering the screen, `/admin/analytics` is organized into **3 Dedicated Sub-tabs**:
- **Tab 1: Doanh Thu & KPIs (Sales & Operational Trends):** Existing operational analytics and period comparisons.
- **Tab 2: Phân Khúc Khách Hàng (RFM) & Hành Vi (Customer Intelligence):** RFM scoring engine, 6 behavioral cohorts, and category affinity analysis.
- **Tab 3: Khai Phá Giỏ Hàng (Market Basket Analysis - Apriori):** Association rule mining, co-purchase frequencies, and bundle recommendations.

```
                      ┌──────────────────────────────────────────────┐
                      │    HTTP Request (/admin/analytics?tab=...)   │
                      └──────────────────────┬───────────────────────┘
                                             │
                                             ▼
                      ┌──────────────────────────────────────────────┐
                      │             AnalyticsController              │
                      │  (Delegates to AnalyticsService Sub-engines) │
                      └──────┬───────────────────────┬───────────────┘
                             │                       │
                             ▼                       ▼
              ┌────────────────────────┐   ┌───────────────────────────┐
              │  RfmAnalyticsService   │   │ MarketBasketMiningService │
              │  - Calculate R-F-M     │   │ - Filter Multi-item Carts │
              │  - Classify 6 Cohorts  │   │ - Co-purchase Matrix      │
              │  - Category Affinities │   │ - Support, Confidence, Lift
              └──────────────┬─────────┘   └───────────────┬───────────┘
                             │                             │
                             └──────────────┬──────────────┘
                                            ▼
                             ┌─────────────────────────────┐
                             │      Blade View Index       │
                             │   (3-Tab Interactive UI)    │
                             └─────────────────────────────┘
```

---

## 2. Algorithmic Formulations

### 2.1. RFM Customer Segmentation Engine

#### 2.1.1. Raw Metric Derivations
For each customer with completed purchases (`payment_status = 'paid'` or `status = 'completed'`, `status != 'cancelled'`):
1. **Recency ($R$):** Days elapsed since the customer's most recent completed order:
   $$R_{\text{days}} = \text{now()} - \max(\text{order\_date})$$
2. **Frequency ($F$):** Total count of completed orders placed by the customer:
   $$F_{\text{count}} = \sum \text{completed orders}$$
3. **Monetary ($M$):** Cumulative spending across all completed orders:
   $$M_{\text{sum}} = \sum \text{total\_amount}$$

#### 2.1.2. Scoring Quintiles (Scale 1 to 5)
Based on e-commerce benchmarks tailored for fashion retail:

| Score | Recency ($R$) | Frequency ($F$) | Monetary ($M$) |
| :---: | :--- | :--- | :--- |
| **5** | $\le 15$ days | $\ge 8$ orders | $\ge 5,000,000$ đ |
| **4** | $16 - 30$ days | $5 - 7$ orders | $2,500,000 - 4,999,999$ đ |
| **3** | $31 - 60$ days | $3 - 4$ orders | $1,000,000 - 2,499,999$ đ |
| **2** | $61 - 90$ days | $2$ orders | $500,000 - 999,999$ đ |
| **1** | $> 90$ days | $1$ order | $< 500,000$ đ |

#### 2.1.3. Segment Classification Rules

| Segment | Criteria ($R, F, M$) | Business Description | Recommended Strategic Action |
| :--- | :--- | :--- | :--- |
| **Champions (VIP)** | $R \ge 4 \land F \ge 4 \land M \ge 4$ | Bought recently, buys often, spends heavily | VIP concierge, exclusive previews, birthday gift |
| **Loyal Customers** | $R \ge 3 \land F \ge 3$ | Regular buyers with steady purchase cadence | Loyalty points, reward vouchers, early access |
| **Potential Loyalists** | $R \ge 4 \land F \in \{1, 2\} \land M \ge 2$ | Recent buyers with above-average basket size | Personalized cross-sell, recommend matching items |
| **New Customers** | $R \ge 4 \land F = 1$ | Fresh buyers placed their first order recently | Onboarding care, styling tips, 2nd order discount |
| **At Risk** | $R \le 2 \land F \ge 3 \land M \ge 3$ | Big spenders/frequent buyers who went inactive | Win-back campaign, deep discount voucher, check-in |
| **Lost Customers** | $R = 1 \land F = 1$ | Low spenders who bought once long ago and left | Passive seasonal clearance retargeting |

---

### 2.2. Product Purchase Behavior (Segment ↔ Category Affinity)

For each RFM customer cohort:
1. Aggregate all order items purchased by members of the cohort:
   $$\text{CategoryRevenue}(S, C) = \sum_{u \in S} \sum_{i \in \text{Orders}(u) \cap C} i.\text{total}$$
2. Determine the **Favorite Category** ($C_{\text{top}}$) where $\text{CategoryRevenue}$ is maximized.
3. Compute category contribution percentages to reveal behavioral distinctiveness (e.g. Champions prefer *Đầm dạ hội & Công sở*, whereas New Customers enter via *Chân váy & Áo sơ mi*).

---

### 2.3. Market Basket Analysis (Apriori Algorithm)

#### 2.3.1. Problem Definition
Given a set of completed shopping transactions $T = \{t_1, t_2, \dots, t_N\}$ where each transaction $t_k$ contains unique product IDs $\{p_1, p_2, \dots, p_m\}$ with $|t_k| \ge 2$:
We discover association rules of the form $\{A\} \Rightarrow \{B\}$ where $A \neq B$.

#### 2.3.2. Statistical Metrics
1. **Support:** Probability that a random transaction contains both items $A$ and $B$:
   $$\text{Support}(A \cup B) = \frac{\text{Count}(A \land B)}{|T|}$$
2. **Confidence:** Conditional probability that item $B$ is purchased given that item $A$ is purchased:
   $$\text{Confidence}(A \Rightarrow B) = P(B \mid A) = \frac{\text{Support}(A \cup B)}{\text{Support}(A)} = \frac{\text{Count}(A \land B)}{\text{Count}(A)}$$
3. **Lift:** Ratio of observed joint probability to expected probability under statistical independence:
   $$\text{Lift}(A \Rightarrow B) = \frac{\text{Confidence}(A \Rightarrow B)}{\text{Support}(B)} = \frac{P(A \cap B)}{P(A) \cdot P(B)}$$
   - $\text{Lift} > 1$: Positive correlation (Items $A$ and $B$ strongly complement each other).
   - $\text{Lift} = 1$: Independent occurrences (No actionable relationship).
   - $\text{Lift} < 1$: Substitute products (Buying $A$ reduces likelihood of buying $B$).

#### 2.3.3. Association Filtering Thresholds
- Minimum itemset co-occurrence: $\ge 2$ orders (accommodating medium-scale catalogs).
- Minimum Confidence: $\ge 20\%$.
- Minimum Lift: $> 1.05$ (guaranteeing genuine positive lift).

---

## 3. UI/UX Specification

### 3.1. Sub-Tab Navigation
Located right below the page title on `/admin/analytics`:
- **Tab 1: Doanh Thu & KPIs** (`active-tab` indicator)
- **Tab 2: Phân Khúc Khách Hàng (RFM)** (Badge showing total analyzed customers)
- **Tab 3: Khai Phá Giỏ Hàng (Apriori)** (Badge showing discovered association rules)

### 3.2. Tab 2 UI (RFM & Behavior)
- **Cohort Summary Cards (6 Segments):** Grid of cards displaying segment name, member count, % of total customer base, total revenue contribution, and strategic action badge.
- **RFM Distribution Donut Chart:** Visual breakdown of customer shares across segments.
- **Segment ↔ Category Matrix Table:** Segment Name, Member Count, Total Revenue, Favorite Category, Top Selling Product in Segment, Action CTA button.
- **Customer Cohort Drill-Down Table:** Searchable list of customers with their Recency (days ago), Frequency (orders), Monetary (lifetime spending), Assigned Segment badge, and Contact shortcut.

### 3.3. Tab 3 UI (Market Basket Analysis)
- **Discovery Overview Banner:** Total analyzed baskets with $\ge 2$ items, total discovered co-purchase pairings.
- **Top Cross-Sell Rules Grid / Cards:** Prominently display top 3 highest-lift associations with visual arrow `[Product A] ──(Lift: 2.8x)──> [Product B]`.
- **Interactive Rules Table:**
  - Sản phẩm chính (Antecedent $A$ - thumbnail, name, SKU)
  - Sản phẩm mua kèm (Consequent $B$ - thumbnail, name, SKU)
  - Số lần mua chung (Co-occurrence count)
  - Support (%) with progress bar
  - Confidence (%) with badge
  - Lift (ratio multiplier e.g. `2.45x`)
  - Hành động: Nút **"Tạo Khuyến Mãi Combo"** (Liên kết trực tiếp tới `/admin/flash-sales`).

---

## 4. Implementation Structure

### 4.1. Service Layer
- **`app/Services/RfmAnalyticsService.php`:**
  - `calculateCustomerRfmScores()`: Aggregates order data per customer and calculates $R, F, M$ scores and segments.
  - `getSegmentCategoryAffinities()`: Computes category preferences and top products for each segment.
  - `getRfmSummary()`: Aggregated statistics for cards, chart series, and segment tables.
- **`app/Services/MarketBasketMiningService.php`:**
  - `extractTransactions()`: Gathers cart item arrays for all orders with $\ge 2$ items.
  - `mineAssociationRules(float $minSupport, float $minConfidence, float $minLift)`: Computes pairwise co-occurrences, supports, confidences, and lifts.
- **`app/Services/AnalyticsService.php`:**
  - Coordinates high-level data requests across Tab 1, Tab 2, and Tab 3.

### 4.2. Controller & Routing
- `AnalyticsController@index`: Accepts optional query param `tab` (`kpi`, `rfm`, `basket`) and loads the appropriate service payloads.

---

## 5. Testing & Verification Plan

1. **RFM Unit & Feature Tests (`tests/Unit/Services/RfmAnalyticsServiceTest.php`):**
   - Test scoring boundaries ($R, F, M$ 1-5).
   - Test customer segment assignment logic (Champions vs Lost).
   - Test category affinity aggregation.
2. **Market Basket Mining Tests (`tests/Unit/Services/MarketBasketMiningServiceTest.php`):**
   - Test Support, Confidence, and Lift calculations against known transaction matrices.
   - Verify non-complementary pairings are filtered when $\text{Lift} \le 1.0$.
3. **Controller & Blade Integration (`tests/Feature/Admin/AnalyticsAdvancedTest.php`):**
   - Verify `/admin/analytics?tab=rfm` returns 200 and loads RFM data.
   - Verify `/admin/analytics?tab=basket` returns 200 and loads Apriori rules.
   - Verify graceful rendering when dataset has zero multi-item orders.

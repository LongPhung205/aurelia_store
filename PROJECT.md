# Project: Aurelia Store - PayOS Webhook Optimization & Test Automation

## Architecture
- Framework: Laravel 11
- Payment Gateway: PayOS
- Core Components:
  - Webhook Endpoint: `POST /payos/webhook` (exempt from CSRF in `bootstrap/app.php`)
  - Webhook Controller: `app/Http/Controllers/Client/PayOSController.php`
  - Checkout Controller: `app/Http/Controllers/Client/CheckoutController.php`
  - Background Poller: `app/Console/Commands/ReleaseUnpaidOrders.php`
  - Transaction Ledger: `app/Models/PaymentTransaction.php`
  - Configuration: `config/services.php`, `phpunit.xml`
  - Test Suites: `tests/Feature/PayOSWebhookTest.php`, `tests/Feature/PayOSWebhookConcurrencyTest.php`, `tests/Feature/PayOSWebhookSecurityChallengeTest.php`, `tests/Feature/ReleaseUnpaidOrdersTest.php`

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|------|-------|-------------|--------|
| 1 | Exploration & Root Cause Analysis | Investigate PayOS Webhook, identify root causes of pending order hang, review signature verification, idempotency logic, and test setup | none | DONE |
| 2 | Implementation & Fix | Fix pending order issue, decouple PayOS orderCode from shipping_order_code, register config/services.php, fix status 'processing', fix HTTP response codes, add idempotency with row locking, prevent double inventory deduction | M1 | DONE |
| 3 | Automated Tests (PHPUnit) | Create comprehensive Unit/Feature test suite in tests/Feature/PayOSWebhookTest.php (success, invalid/missing signature, duplicate webhook idempotency) | M2 | DONE |
| 4 | Verification & Forensic Audit | Review, challenge, and forensic integrity audit ensuring zero cheating, clean code, hardened edge cases, and 100% passing tests | M3 | DONE |

## Interface Contracts
### PayOS Webhook ↔ Aurelia Store Application
- Route: `POST /payos/webhook` (exempt from CSRF in `bootstrap/app.php`)
- Payload validation: Requires array payload in `data` (scalar/malformed payloads return HTTP 400).
- Cryptographic verification: Verified using official PayOS SDK (`verifyPaymentWebhookData`) with `config('services.payos.checksum_key')`. Invalid signatures return HTTP 400.
- Order resolution: Queries `PaymentTransaction::where('transaction_id', $orderCode)` with fallback to `Order::find($orderCode)` and legacy `Order::where('shipping_order_code', $orderCode)`. Missing order returns HTTP 404.
- Amount verification: Validates `$verifiedData['amount'] >= $order->total_amount` under database row lock (`lockForUpdate()`). Underpayment returns HTTP 400.
- Idempotency: If `$order->payment_status === 'paid'`, commits and immediately returns HTTP 200 `{"error": 0, "message": "Order already processed", "data": null}` without side effects.
- State transitions: Strictly requires verified `code === '00'`. Sets `payment_status = 'paid'`, `status = 'processing'`, `is_inventory_deducted = true`, and marks `PaymentTransaction` as `success`. Failed payment codes update transaction to `failed` and leave order in `pending`.
- Error resilience: Database transaction exceptions roll back and return HTTP 500 to signal PayOS to retry.

## Code Layout
- Controllers: `app/Http/Controllers/Client/PayOSController.php`, `app/Http/Controllers/Client/CheckoutController.php`
- Commands: `app/Console/Commands/ReleaseUnpaidOrders.php`
- Configuration: `config/services.php`, `phpunit.xml`
- Models: `app/Models/Order.php`, `app/Models/PaymentTransaction.php`, `app/Models/User.php`
- Tests: `tests/Feature/PayOSWebhookTest.php`, `tests/Feature/PayOSWebhookSecurityChallengeTest.php`, `tests/Feature/PayOSWebhookConcurrencyTest.php`, `tests/Feature/ReleaseUnpaidOrdersTest.php`

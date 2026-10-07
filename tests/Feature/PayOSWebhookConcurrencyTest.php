<?php

namespace Tests\Feature;

use App\Http\Controllers\Client\PayOSController;
use App\Models\Color;
use App\Models\InventoryHistory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PayOS\Utils\PayOSSignatureUtils;
use Tests\TestCase;

class PayOSWebhookConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected string $checksumKey = 'test-checksum-key-1234567890abcdef';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.payos.client_id' => 'test-client-id',
            'services.payos.api_key' => 'test-api-key',
            'services.payos.checksum_key' => $this->checksumKey,
        ]);
    }

    /**
     * Helper to generate a valid PayOS webhook payload.
     */
    protected function generateWebhookPayload(array $data, ?string $key = null, string $code = '00', string $desc = 'success'): array
    {
        $checksumKey = $key ?? $this->checksumKey;
        $signature = PayOSSignatureUtils::createSignatureFromObj($checksumKey, $data);

        return [
            'code' => $code,
            'desc' => $desc,
            'data' => $data,
            'signature' => $signature,
        ];
    }

    /**
     * Helper to setup a realistic product with variant and initial stock.
     */
    protected function createProductWithVariant(int $initialStock = 100): ProductVariant
    {
        $product = Product::create([
            'name' => 'Aurelia Luxury Dress',
            'slug' => 'aurelia-luxury-dress-'.uniqid(),
            'short_description' => 'Test dress short desc',
            'description' => 'Test dress full description',
            'status' => 'active',
        ]);

        $color = Color::create(['name' => 'Red', 'hex_code' => '#FF0000']);
        $size = Size::create(['name' => 'M']);

        return ProductVariant::create([
            'product_id' => $product->id,
            'color_id' => $color->id,
            'size_id' => $size->id,
            'sku' => 'SKU-'.uniqid(),
            'price' => 200000,
            'stock_quantity' => $initialStock,
            'is_active' => true,
        ]);
    }

    /**
     * Helper to create an order simulating checkout reservation.
     */
    protected function createOrderWithItem(ProductVariant $variant, int $quantity = 2): array
    {
        $user = User::factory()->create();

        // 1. Simulate checkout: decrement stock directly as done in CheckoutController
        $variant->decrement('stock_quantity', $quantity);

        // 2. Create order with is_inventory_deducted = false initially
        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'Le Thi B',
            'customer_phone' => '0987654321',
            'address' => '789 Tran Phu, Da Nang',
            'province_id' => 2,
            'district_id' => 10,
            'ward_code' => '20001',
            'subtotal' => $variant->price * $quantity,
            'shipping_fee' => 30000,
            'discount' => 0,
            'total_amount' => ($variant->price * $quantity) + 30000,
            'payment_method' => 'payos',
            'payment_status' => 'pending',
            'status' => 'pending',
            'is_inventory_deducted' => false,
        ]);

        // 3. Create OrderItem
        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'product_name' => $variant->product->name,
            'variant_attributes' => 'Red - M',
            'quantity' => $quantity,
            'price' => $variant->price,
            'total' => $variant->price * $quantity,
        ]);

        // 4. Create initial PaymentTransaction as done in PayOSController::create
        $orderCode = intval($order->id.rand(1000, 9999));
        $transaction = PaymentTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => (string) $orderCode,
            'amount' => $order->total_amount,
            'payment_method' => 'payos',
            'status' => 'pending',
        ]);

        return [$order, $item, $transaction, $orderCode];
    }

    /**
     * Challenge 1.1: 5 Duplicate Webhooks Sent for the Same Paid Order.
     * Invariants to verify:
     * - Webhook #1 returns 200 with 'Payment processed successfully'
     * - Webhooks #2..#5 return 200 with 'Order already processed'
     * - Stock quantity remains unchanged across all 5 calls (NO duplicate deductions)
     * - `is_inventory_deducted` remains strictly true
     * - `payment_status` is strictly 'paid'
     * - `status` is strictly 'processing'
     * - `payment_transactions` count for order remains exactly 1 (no duplicate rows)
     */
    public function test_5_duplicate_webhooks_maintain_strict_idempotency_and_stock_invariants(): void
    {
        $initialStock = 100;
        $orderQuantity = 2;
        $expectedReservedStock = $initialStock - $orderQuantity; // 98

        $variant = $this->createProductWithVariant($initialStock);
        [$order, $item, $transaction, $orderCode] = $this->createOrderWithItem($variant, $orderQuantity);

        // Verify pre-condition: stock reserved at checkout, order pending, flag false
        $this->assertEquals($expectedReservedStock, $variant->fresh()->stock_quantity);
        $this->assertFalse((bool) $order->fresh()->is_inventory_deducted);
        $this->assertEquals('pending', $order->fresh()->payment_status);
        $this->assertEquals('pending', $order->fresh()->status);
        $this->assertEquals(1, PaymentTransaction::where('order_id', $order->id)->count());

        $payloadData = [
            'orderCode' => $orderCode,
            'amount' => intval($order->total_amount),
            'code' => '00',
            'desc' => 'success',
            'reference' => 'TXN_REF_001',
        ];
        $payload = $this->generateWebhookPayload($payloadData);

        // Delivery 1: First Webhook Arrival
        $response1 = $this->postJson('/payos/webhook', $payload);
        $response1->assertStatus(200);
        $response1->assertJson([
            'error' => 0,
            'message' => 'Payment processed successfully',
            'data' => null,
        ]);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('processing', $order->status);
        $this->assertTrue((bool) $order->is_inventory_deducted);
        $this->assertEquals($expectedReservedStock, $variant->fresh()->stock_quantity);
        $this->assertEquals(1, PaymentTransaction::where('order_id', $order->id)->count());
        $this->assertEquals('success', $transaction->fresh()->status);

        // Deliveries 2 through 5: Consecutive Duplicate Webhooks
        for ($i = 2; $i <= 5; $i++) {
            $duplicateResponse = $this->postJson('/payos/webhook', $payload);

            $duplicateResponse->assertStatus(200);
            $duplicateResponse->assertJson([
                'error' => 0,
                'message' => 'Order already processed',
                'data' => null,
            ]);

            // Invariant assertions on every duplicate delivery
            $order->refresh();
            $this->assertEquals('paid', $order->payment_status, "Order payment_status corrupted on delivery #{$i}");
            $this->assertEquals('processing', $order->status, "Order status corrupted on delivery #{$i}");
            $this->assertTrue((bool) $order->is_inventory_deducted, "is_inventory_deducted corrupted on delivery #{$i}");

            // Stock invariant: must never decrement below the initial reservation
            $this->assertEquals(
                $expectedReservedStock,
                $variant->fresh()->stock_quantity,
                "Variant stock was incorrectly decremented on delivery #{$i}"
            );

            // Table integrity: payment_transactions must not accumulate duplicate entries
            $this->assertEquals(
                1,
                PaymentTransaction::where('order_id', $order->id)->count(),
                "payment_transactions count multiplied on delivery #{$i}"
            );
        }
    }

    /**
     * Challenge 1.2: Subsequent Fulfillment (deductForOrder) Never Decrements Stock Again.
     * When admin processes order from 'processing' to 'shipping' or 'completed',
     * `InventoryService::deductForOrder` is executed.
     * With `is_inventory_deducted = true`, it must short-circuit and not decrement stock.
     */
    public function test_subsequent_fulfillment_deduct_for_order_does_not_double_deduct_stock(): void
    {
        $initialStock = 50;
        $orderQuantity = 3;
        $expectedStock = $initialStock - $orderQuantity; // 47

        $variant = $this->createProductWithVariant($initialStock);
        [$order, $item, $transaction, $orderCode] = $this->createOrderWithItem($variant, $orderQuantity);

        // Deliver webhook to confirm payment and mark is_inventory_deducted = true
        $payload = $this->generateWebhookPayload([
            'orderCode' => $orderCode,
            'amount' => intval($order->total_amount),
            'code' => '00',
            'desc' => 'success',
        ]);
        $this->postJson('/payos/webhook', $payload)->assertStatus(200);

        $order->refresh();
        $this->assertTrue((bool) $order->is_inventory_deducted);
        $this->assertEquals($expectedStock, $variant->fresh()->stock_quantity);

        // Record initial history count
        $initialHistoriesCount = InventoryHistory::where('reference_id', $order->id)->count();

        // Admin fulfillment trigger
        $inventoryService = app(InventoryService::class);
        $result = $inventoryService->deductForOrder($order);

        $this->assertTrue($result);
        $this->assertEquals($expectedStock, $variant->fresh()->stock_quantity);
        $this->assertEquals($initialHistoriesCount, InventoryHistory::where('reference_id', $order->id)->count());
    }

    /**
     * Challenge 1.3: Duplicate Webhook Delivery After Order Status Advanced to 'shipping'.
     * If admin moves order to 'shipping', a subsequent duplicate webhook must NOT
     * overwrite the status back to 'processing'.
     */
    public function test_duplicate_webhook_does_not_revert_shipping_status_to_processing(): void
    {
        $variant = $this->createProductWithVariant(30);
        [$order, $item, $transaction, $orderCode] = $this->createOrderWithItem($variant, 1);

        $payload = $this->generateWebhookPayload([
            'orderCode' => $orderCode,
            'amount' => intval($order->total_amount),
            'code' => '00',
            'desc' => 'success',
        ]);

        // First webhook succeeds -> status becomes 'processing'
        $this->postJson('/payos/webhook', $payload)->assertStatus(200);
        $order->refresh();
        $this->assertEquals('processing', $order->status);

        // Admin advances order to 'shipping'
        $order->update(['status' => 'shipping']);
        $this->assertEquals('shipping', $order->fresh()->status);

        // Late duplicate webhook arrives
        $response = $this->postJson('/payos/webhook', $payload);
        $response->assertStatus(200);
        $response->assertJson([
            'error' => 0,
            'message' => 'Order already processed',
        ]);

        // Status must remain 'shipping', NOT reverted back to 'processing'
        $this->assertEquals('shipping', $order->fresh()->status);
        $this->assertEquals('paid', $order->fresh()->payment_status);
        $this->assertTrue((bool) $order->fresh()->is_inventory_deducted);
    }

    /**
     * Challenge 1.4: Late Failed Webhook After Successful Payment Cannot Revert State.
     * If payment was already settled as 'paid', a delayed out-of-order '01' webhook
     * must be intercepted by the idempotency guard and not revert the order to pending/failed.
     */
    public function test_late_failed_webhook_after_paid_does_not_revert_order_or_transaction(): void
    {
        $variant = $this->createProductWithVariant(30);
        [$order, $item, $transaction, $orderCode] = $this->createOrderWithItem($variant, 1);

        // 1. Success webhook arrives first
        $successPayload = $this->generateWebhookPayload([
            'orderCode' => $orderCode,
            'amount' => intval($order->total_amount),
            'code' => '00',
            'desc' => 'success',
        ]);
        $this->postJson('/payos/webhook', $successPayload)->assertStatus(200);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('processing', $order->status);
        $this->assertEquals('success', $transaction->fresh()->status);

        // 2. Out-of-order failed webhook arrives
        $failedPayload = $this->generateWebhookPayload(
            [
                'orderCode' => $orderCode,
                'amount' => intval($order->total_amount),
                'code' => '01',
                'desc' => 'Giao dich that bai',
            ],
            null,
            '01',
            'Giao dich that bai'
        );

        $response = $this->postJson('/payos/webhook', $failedPayload);
        $response->assertStatus(200);
        $response->assertJson([
            'error' => 0,
            'message' => 'Order already processed',
        ]);

        // State remains strictly 'paid' and 'success'
        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('processing', $order->status);
        $this->assertTrue((bool) $order->is_inventory_deducted);
        $this->assertEquals('success', $transaction->fresh()->status);
    }

    /**
     * Challenge 1.5: Payment Transactions Table Strict Uniqueness Under Repeated Calls.
     * Verifies that no matter how many duplicate webhooks are sent, exactly 1 row
     * exists for this order in `payment_transactions`.
     */
    public function test_payment_transactions_row_count_strictly_one_across_multiple_calls(): void
    {
        $variant = $this->createProductWithVariant(20);
        [$order, $item, $transaction, $orderCode] = $this->createOrderWithItem($variant, 1);

        $payload = $this->generateWebhookPayload([
            'orderCode' => $orderCode,
            'amount' => intval($order->total_amount),
            'code' => '00',
            'desc' => 'success',
        ]);

        for ($k = 1; $k <= 5; $k++) {
            $this->postJson('/payos/webhook', $payload);
            $count = PaymentTransaction::where('order_id', $order->id)->count();
            $this->assertEquals(1, $count, "Transaction rows count was {$count} on iteration {$k}");
        }

        $allTransactions = PaymentTransaction::where('order_id', $order->id)->get();
        $this->assertCount(1, $allTransactions);
        $this->assertEquals('success', $allTransactions->first()->status);
        $this->assertEquals((string) $orderCode, $allTransactions->first()->transaction_id);
    }
}

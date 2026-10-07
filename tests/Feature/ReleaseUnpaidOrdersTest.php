<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Color;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PayOS\PayOS;
use Tests\TestCase;

class ReleaseUnpaidOrdersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.payos.client_id' => 'test-client-id',
            'services.payos.api_key' => 'test-api-key',
            'services.payos.checksum_key' => 'test-checksum-key-1234567890abcdef',
        ]);
    }

    protected function createProductVariantWithStock(int $initialStock = 10): ProductVariant
    {
        $category = Category::create(['name' => 'Dress', 'slug' => 'dress-'.uniqid()]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Aurelia Silk Dress',
            'slug' => 'aurelia-silk-dress-'.uniqid(),
            'status' => 'active',
        ]);
        $color = Color::create(['name' => 'Navy '.uniqid(), 'hex_code' => '#000080']);
        $size = Size::create(['name' => 'M '.uniqid()]);

        return ProductVariant::create([
            'product_id' => $product->id,
            'color_id' => $color->id,
            'size_id' => $size->id,
            'sku' => 'SKU-'.uniqid(),
            'price' => 200000,
            'stock_quantity' => $initialStock,
        ]);
    }

    public function test_release_unpaid_orders_updates_paid_order_to_processing_and_deducted(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'Customer Paid',
            'customer_phone' => '0987654321',
            'address' => '123 Test St',
            'province_id' => 1,
            'district_id' => 1,
            'ward_code' => '10001',
            'subtotal' => 200000,
            'shipping_fee' => 30000,
            'discount' => 0,
            'total_amount' => 230000,
            'payment_method' => 'payos',
            'payment_status' => 'pending',
            'status' => 'pending',
            'is_inventory_deducted' => false,
        ]);

        $order->timestamps = false;
        $order->created_at = now()->subMinutes(35);
        $order->save();
        $order->timestamps = true;

        $orderCode = 888999111;
        $transaction = PaymentTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => (string) $orderCode,
            'amount' => $order->total_amount,
            'payment_method' => 'payos',
            'status' => 'pending',
        ]);

        // Mock PayOS getPaymentLinkInformation
        $mockPayOS = $this->createMock(PayOS::class);
        $mockPayOS->expects($this->once())
            ->method('getPaymentLinkInformation')
            ->with($orderCode)
            ->willReturn([
                'status' => 'PAID',
                'orderCode' => $orderCode,
                'amount' => 230000,
            ]);

        $this->app->instance(PayOS::class, $mockPayOS);

        $this->artisan('orders:release-unpaid')
            ->expectsOutput("Processing order #{$order->id} (Method: payos)")
            ->expectsOutput("Order #{$order->id} was actually paid. Updating status and skipping cancellation.")
            ->assertExitCode(0);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('processing', $order->status);
        $this->assertTrue((bool) $order->is_inventory_deducted);

        $transaction->refresh();
        $this->assertEquals('success', $transaction->status);
    }

    public function test_release_unpaid_orders_cancels_unpaid_order_and_restores_inventory(): void
    {
        $variant = $this->createProductVariantWithStock(10);
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'Customer Unpaid',
            'customer_phone' => '0987654321',
            'address' => '456 Test St',
            'province_id' => 1,
            'district_id' => 1,
            'ward_code' => '10001',
            'subtotal' => 200000,
            'shipping_fee' => 30000,
            'discount' => 0,
            'total_amount' => 230000,
            'payment_method' => 'payos',
            'payment_status' => 'pending',
            'status' => 'pending',
            'is_inventory_deducted' => false,
        ]);

        $order->timestamps = false;
        $order->created_at = now()->subMinutes(40);
        $order->save();
        $order->timestamps = true;

        OrderItem::create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'product_name' => 'Aurelia Silk Dress',
            'variant_attributes' => 'Navy - M',
            'quantity' => 3,
            'price' => 200000,
            'total' => 600000,
        ]);

        $orderCode = 777111222;
        PaymentTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => (string) $orderCode,
            'amount' => $order->total_amount,
            'payment_method' => 'payos',
            'status' => 'pending',
        ]);

        $mockPayOS = $this->createMock(PayOS::class);
        $mockPayOS->expects($this->once())
            ->method('getPaymentLinkInformation')
            ->with($orderCode)
            ->willReturn([
                'status' => 'CANCELLED',
                'orderCode' => $orderCode,
            ]);

        $this->app->instance(PayOS::class, $mockPayOS);

        $this->artisan('orders:release-unpaid')
            ->assertExitCode(0);

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
        $this->assertEquals('failed', $order->payment_status);

        // Stock restored by 3
        $variant->refresh();
        $this->assertEquals(13, $variant->stock_quantity);
    }

    public function test_release_unpaid_orders_legacy_order_with_shipping_order_code(): void
    {
        $user = User::factory()->create();
        $orderCode = 999333222;

        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'Customer Legacy',
            'customer_phone' => '0987654321',
            'address' => '789 Test St',
            'province_id' => 1,
            'district_id' => 1,
            'ward_code' => '10001',
            'subtotal' => 200000,
            'shipping_fee' => 30000,
            'discount' => 0,
            'total_amount' => 230000,
            'payment_method' => 'payos',
            'payment_status' => 'pending',
            'status' => 'pending',
            'is_inventory_deducted' => false,
            'shipping_order_code' => (string) $orderCode,
        ]);

        $order->timestamps = false;
        $order->created_at = now()->subMinutes(35);
        $order->save();
        $order->timestamps = true;

        $mockPayOS = $this->createMock(PayOS::class);
        $mockPayOS->expects($this->once())
            ->method('getPaymentLinkInformation')
            ->with($orderCode)
            ->willReturn([
                'status' => 'PAID',
                'orderCode' => $orderCode,
                'amount' => 230000,
            ]);

        $this->app->instance(PayOS::class, $mockPayOS);

        $this->artisan('orders:release-unpaid')
            ->assertExitCode(0);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('processing', $order->status);
        $this->assertTrue((bool) $order->is_inventory_deducted);
    }

    public function test_release_unpaid_orders_skips_cancellation_when_payos_api_throws_exception(): void
    {
        $variant = $this->createProductVariantWithStock(10);
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'Customer Network Err',
            'customer_phone' => '0987654321',
            'address' => '101 Network Ave',
            'province_id' => 1,
            'district_id' => 1,
            'ward_code' => '10001',
            'subtotal' => 200000,
            'shipping_fee' => 30000,
            'discount' => 0,
            'total_amount' => 230000,
            'payment_method' => 'payos',
            'payment_status' => 'pending',
            'status' => 'pending',
            'is_inventory_deducted' => true,
        ]);

        $order->timestamps = false;
        $order->created_at = now()->subMinutes(35);
        $order->save();
        $order->timestamps = true;

        OrderItem::create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'product_name' => 'Aurelia Silk Dress',
            'variant_attributes' => 'Navy - M',
            'quantity' => 2,
            'price' => 200000,
            'total' => 400000,
        ]);

        $orderCode = 555444333;
        PaymentTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => (string) $orderCode,
            'amount' => $order->total_amount,
            'payment_method' => 'payos',
            'status' => 'pending',
        ]);

        $mockPayOS = $this->createMock(PayOS::class);
        $mockPayOS->expects($this->once())
            ->method('getPaymentLinkInformation')
            ->with($orderCode)
            ->willThrowException(new \Exception('Connection timeout to PayOS server'));

        $this->app->instance(PayOS::class, $mockPayOS);

        $this->artisan('orders:release-unpaid')
            ->assertExitCode(0);

        // Crucial check: Order must NOT be cancelled when PayOS API check throws network/server exception!
        $order->refresh();
        $this->assertEquals('pending', $order->status);
        $this->assertEquals('pending', $order->payment_status);
        $this->assertTrue((bool) $order->is_inventory_deducted);

        // Stock must not be changed
        $variant->refresh();
        $this->assertEquals(10, $variant->stock_quantity);
    }

    public function test_release_unpaid_orders_skips_cancellation_when_order_concurrently_paid(): void
    {
        $variant = $this->createProductVariantWithStock(10);
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'Customer Race Condition',
            'customer_phone' => '0987654321',
            'address' => '202 Concurrency Rd',
            'province_id' => 1,
            'district_id' => 1,
            'ward_code' => '10001',
            'subtotal' => 200000,
            'shipping_fee' => 30000,
            'discount' => 0,
            'total_amount' => 230000,
            'payment_method' => 'payos',
            'payment_status' => 'pending',
            'status' => 'pending',
            'is_inventory_deducted' => true,
        ]);

        $order->timestamps = false;
        $order->created_at = now()->subMinutes(35);
        $order->save();
        $order->timestamps = true;

        OrderItem::create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'product_name' => 'Aurelia Silk Dress',
            'variant_attributes' => 'Navy - M',
            'quantity' => 2,
            'price' => 200000,
            'total' => 400000,
        ]);

        $orderCode = 666777888;
        PaymentTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => (string) $orderCode,
            'amount' => $order->total_amount,
            'payment_method' => 'payos',
            'status' => 'pending',
        ]);

        $mockPayOS = $this->createMock(PayOS::class);
        $mockPayOS->expects($this->once())
            ->method('getPaymentLinkInformation')
            ->with($orderCode)
            ->willReturnCallback(function () use ($order) {
                // Simulate race condition: webhook completed payment while command was querying PayOS
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'processing',
                ]);

                // Return non-PAID status from this call (e.g. stale or CANCELLED)
                return [
                    'status' => 'CANCELLED',
                    'orderCode' => 666777888,
                ];
            });

        $this->app->instance(PayOS::class, $mockPayOS);

        $this->artisan('orders:release-unpaid')
            ->assertExitCode(0);

        // Verification: Even though getPaymentLinkInformation returned CANCELLED,
        // the order row was locked and recognized as 'paid' so cancellation was safely skipped!
        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('processing', $order->status);
        $this->assertTrue((bool) $order->is_inventory_deducted);

        // Stock was not incremented
        $variant->refresh();
        $this->assertEquals(10, $variant->stock_quantity);
    }
}

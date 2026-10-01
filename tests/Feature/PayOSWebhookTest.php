<?php

namespace Tests\Feature;

use App\Http\Controllers\Client\PayOSController;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use PayOS\PayOS;
use PayOS\Utils\PayOSSignatureUtils;
use Tests\TestCase;

class PayOSWebhookTest extends TestCase
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
     * Helper to create a dummy pending order.
     */
    protected function createDummyOrder(array $attributes = []): Order
    {
        $user = User::factory()->create();

        return Order::create(array_merge([
            'user_id' => $user->id,
            'customer_name' => 'Nguyen Van A',
            'customer_phone' => '0912345678',
            'address' => '123 Le Loi, District 1, HCMC',
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
        ], $attributes));
    }

    public function test_payos_configuration_is_loaded_from_config_services(): void
    {
        $this->assertEquals('test-client-id', config('services.payos.client_id'));
        $this->assertEquals('test-api-key', config('services.payos.api_key'));
        $this->assertEquals($this->checksumKey, config('services.payos.checksum_key'));
    }

    /**
     * Criteria B: Security - Tampered checksum returns 400
     */
    public function test_webhook_with_invalid_tampered_signature_returns_400(): void
    {
        $payload = [
            'code' => '00',
            'desc' => 'success',
            'data' => [
                'orderCode' => 999999,
                'amount' => 230000,
                'code' => '00',
            ],
            'signature' => 'invalid-tampered-signature',
        ];

        $response = $this->postJson('/payos/webhook', $payload);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 1,
            'message' => 'Invalid signature',
        ]);
    }

    /**
     * Criteria B: Security - Missing signature key returns 400
     */
    public function test_webhook_with_missing_signature_key_returns_400(): void
    {
        $payload = [
            'code' => '00',
            'desc' => 'success',
            'data' => [
                'orderCode' => 999999,
                'amount' => 230000,
                'code' => '00',
            ],
        ];

        $response = $this->postJson('/payos/webhook', $payload);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 1,
            'message' => 'Invalid signature',
        ]);
    }

    /**
     * Criteria B: Security - Empty string signature returns 400
     */
    public function test_webhook_with_empty_signature_string_returns_400(): void
    {
        $payload = [
            'code' => '00',
            'desc' => 'success',
            'data' => [
                'orderCode' => 999999,
                'amount' => 230000,
                'code' => '00',
            ],
            'signature' => '',
        ];

        $response = $this->postJson('/payos/webhook', $payload);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 1,
            'message' => 'Invalid signature',
        ]);
    }

    /**
     * Criteria B: Security - Empty webhook payload returns 400
     */
    public function test_webhook_with_empty_payload_returns_400(): void
    {
        $response = $this->postJson('/payos/webhook', []);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 1,
            'message' => 'Invalid signature',
        ]);
    }

    /**
     * Criteria B: Security - Missing data key returns 400
     */
    public function test_webhook_with_missing_data_key_returns_400(): void
    {
        $payload = [
            'code' => '00',
            'desc' => 'success',
            'signature' => 'test-signature-no-data',
        ];

        $response = $this->postJson('/payos/webhook', $payload);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 1,
            'message' => 'Invalid signature',
        ]);
    }

    /**
     * Criteria B: Security - Null data payload returns 400
     */
    public function test_webhook_with_null_data_returns_400(): void
    {
        $payload = [
            'code' => '00',
            'desc' => 'success',
            'data' => null,
            'signature' => 'test-signature-null-data',
        ];

        $response = $this->postJson('/payos/webhook', $payload);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 1,
            'message' => 'Invalid signature',
        ]);
    }

    /**
     * Criteria B: Security - Empty data array returns 400
     */
    public function test_webhook_with_empty_data_array_returns_400(): void
    {
        $payload = [
            'code' => '00',
            'desc' => 'success',
            'data' => [],
            'signature' => 'test-signature-empty-data',
        ];

        $response = $this->postJson('/payos/webhook', $payload);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 1,
            'message' => 'Invalid signature',
        ]);
    }

    /**
     * Criteria D: Order not found returns 404
     */
    public function test_webhook_with_missing_order_returns_404(): void
    {
        $data = [
            'orderCode' => 999888777,
            'amount' => 230000,
            'code' => '00',
            'desc' => 'success',
        ];
        $payload = $this->generateWebhookPayload($data);

        $response = $this->postJson('/payos/webhook', $payload);

        $response->assertStatus(404);
        $response->assertJson([
            'error' => 1,
            'message' => 'Order not found',
        ]);
    }

    /**
     * Criteria: PayOS Sample Confirmation Webhook (orderCode 123) returns 200 OK
     */
    public function test_webhook_handles_payos_sample_confirmation_ping_with_200(): void
    {
        $data = [
            'orderCode' => 123,
            'amount' => 3000,
            'description' => 'VQRIO123',
            'code' => '00',
            'desc' => 'success',
        ];
        $payload = $this->generateWebhookPayload($data);

        $response = $this->postJson('/payos/webhook', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'error' => 0,
            'message' => 'Webhook URL verified successfully',
        ]);
    }

    /**
     * Criteria A: Successful payment (code == '00')
     * Webhook arrives with authentic signature -> order status transitions to 'processing',
     * payment_status to 'paid', is_inventory_deducted to true, payment_transactions updated to 'success',
     * and returns HTTP 200.
     */
    public function test_webhook_successfully_processes_payment_via_payment_transaction(): void
    {
        $order = $this->createDummyOrder([
            'status' => 'pending',
            'payment_status' => 'pending',
            'is_inventory_deducted' => false,
        ]);
        $orderCode = 123456789;

        $transaction = PaymentTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => (string) $orderCode,
            'amount' => $order->total_amount,
            'payment_method' => 'payos',
            'status' => 'pending',
        ]);

        $data = [
            'orderCode' => $orderCode,
            'amount' => intval($order->total_amount),
            'code' => '00',
            'desc' => 'success',
            'reference' => 'TF_TEST_12345',
        ];
        $payload = $this->generateWebhookPayload($data);

        $response = $this->postJson('/payos/webhook', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'error' => 0,
            'message' => 'Payment processed successfully',
            'data' => null,
        ]);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('processing', $order->status);
        $this->assertTrue((bool) $order->is_inventory_deducted);

        $transaction->refresh();
        $this->assertEquals('success', $transaction->status);
        $this->assertNotNull($transaction->response_data);
    }

    /**
     * Criteria C: Idempotency - 2 consecutive duplicate webhooks
     * First call succeeds; second call recognizes payment_status === 'paid' and returns 200 with 'Order already processed'
     * without re-deducting inventory or duplicating state.
     */
    public function test_webhook_duplicate_consecutive_requests_handled_idempotently(): void
    {
        $order = $this->createDummyOrder([
            'status' => 'pending',
            'payment_status' => 'pending',
            'is_inventory_deducted' => false,
        ]);
        $orderCode = 8877665544;

        $transaction = PaymentTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => (string) $orderCode,
            'amount' => $order->total_amount,
            'payment_method' => 'payos',
            'status' => 'pending',
        ]);

        $data = [
            'orderCode' => $orderCode,
            'amount' => intval($order->total_amount),
            'code' => '00',
            'desc' => 'success',
            'reference' => 'TF_CONSECUTIVE_01',
        ];
        $payload = $this->generateWebhookPayload($data);

        // First call: transitions to paid
        $firstResponse = $this->postJson('/payos/webhook', $payload);
        $firstResponse->assertStatus(200);
        $firstResponse->assertJson([
            'error' => 0,
            'message' => 'Payment processed successfully',
        ]);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('processing', $order->status);
        $this->assertTrue((bool) $order->is_inventory_deducted);

        $transaction->refresh();
        $this->assertEquals('success', $transaction->status);

        // Second call: duplicate request
        $secondResponse = $this->postJson('/payos/webhook', $payload);
        $secondResponse->assertStatus(200);
        $secondResponse->assertJson([
            'error' => 0,
            'message' => 'Order already processed',
            'data' => null,
        ]);

        // State remains strictly consistent
        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('processing', $order->status);
        $this->assertTrue((bool) $order->is_inventory_deducted);

        $transaction->refresh();
        $this->assertEquals('success', $transaction->status);
    }

    /**
     * Criteria C: Idempotency - Webhook called on pre-existing paid order
     */
    public function test_webhook_is_idempotent_when_called_on_already_paid_order(): void
    {
        $order = $this->createDummyOrder([
            'payment_status' => 'paid',
            'status' => 'processing',
            'is_inventory_deducted' => true,
        ]);
        $orderCode = 987654321;

        $transaction = PaymentTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => (string) $orderCode,
            'amount' => $order->total_amount,
            'payment_method' => 'payos',
            'status' => 'success',
        ]);

        $data = [
            'orderCode' => $orderCode,
            'amount' => intval($order->total_amount),
            'code' => '00',
            'desc' => 'success',
        ];
        $payload = $this->generateWebhookPayload($data);

        $response = $this->postJson('/payos/webhook', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'error' => 0,
            'message' => 'Order already processed',
            'data' => null,
        ]);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('processing', $order->status);
        $this->assertTrue((bool) $order->is_inventory_deducted);
    }

    /**
     * Criteria E: Failed payment code (code != '00')
     * Webhook arrives with error code -> transaction marked as 'failed', order remains pending and not deducted.
     */
    public function test_webhook_with_failed_payment_code_marks_transaction_failed_and_keeps_order_pending(): void
    {
        $order = $this->createDummyOrder([
            'status' => 'pending',
            'payment_status' => 'pending',
            'is_inventory_deducted' => false,
        ]);
        $orderCode = 3344556677;

        $transaction = PaymentTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => (string) $orderCode,
            'amount' => $order->total_amount,
            'payment_method' => 'payos',
            'status' => 'pending',
        ]);

        $data = [
            'orderCode' => $orderCode,
            'amount' => intval($order->total_amount),
            'code' => '01',
            'desc' => 'Giao dịch không thành công',
            'reference' => 'TF_FAILED_01',
        ];
        $payload = $this->generateWebhookPayload($data, null, '01', 'Giao dịch không thành công');

        $response = $this->postJson('/payos/webhook', $payload);

        $response->assertStatus(200);

        // Order remains pending and inventory is NOT deducted
        $order->refresh();
        $this->assertEquals('pending', $order->payment_status);
        $this->assertEquals('pending', $order->status);
        $this->assertFalse((bool) $order->is_inventory_deducted);

        // Transaction is marked as failed
        $transaction->refresh();
        $this->assertEquals('failed', $transaction->status);
        $this->assertNotNull($transaction->response_data);
    }

    public function test_webhook_fallback_lookup_via_shipping_order_code_for_legacy_orders(): void
    {
        $orderCode = 555666777;
        $order = $this->createDummyOrder([
            'shipping_order_code' => (string) $orderCode,
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);

        $data = [
            'orderCode' => $orderCode,
            'amount' => intval($order->total_amount),
            'code' => '00',
            'desc' => 'success',
        ];
        $payload = $this->generateWebhookPayload($data);

        $response = $this->postJson('/payos/webhook', $payload);

        $response->assertStatus(200);
        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('processing', $order->status);
        $this->assertTrue((bool) $order->is_inventory_deducted);
    }

    public function test_webhook_fallback_lookup_via_order_id(): void
    {
        $order = $this->createDummyOrder([
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);

        $data = [
            'orderCode' => $order->id,
            'amount' => intval($order->total_amount),
            'code' => '00',
            'desc' => 'success',
        ];
        $payload = $this->generateWebhookPayload($data);

        $response = $this->postJson('/payos/webhook', $payload);

        $response->assertStatus(200);
        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('processing', $order->status);
        $this->assertTrue((bool) $order->is_inventory_deducted);
    }

    public function test_payos_create_decouples_order_code_and_creates_payment_transaction(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'Test Customer',
            'customer_phone' => '0987654321',
            'address' => '456 Tran Hung Dao',
            'province_id' => 1,
            'district_id' => 1,
            'ward_code' => '10002',
            'subtotal' => 150000,
            'shipping_fee' => 20000,
            'discount' => 0,
            'total_amount' => 170000,
            'payment_method' => 'payos',
            'payment_status' => 'pending',
            'status' => 'pending',
            'is_inventory_deducted' => false,
            'shipping_order_code' => null,
        ]);

        // Mock PayOS createPaymentLink
        $mockPayOS = $this->createMock(PayOS::class);
        $mockPayOS->expects($this->once())
            ->method('createPaymentLink')
            ->willReturn(['checkoutUrl' => 'https://pay.payos.vn/web/test-checkout-url']);

        $controller = new PayOSController;
        // Use reflection to inject mocked payOS
        $reflector = new \ReflectionObject($controller);
        $property = $reflector->getProperty('payOS');
        $property->setAccessible(true);
        $property->setValue($controller, $mockPayOS);

        $request = Request::create("/payos/create/{$order->id}", 'GET');
        $response = $controller->create($request, $order->id);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('https://pay.payos.vn/web/test-checkout-url', $response->getTargetUrl());

        // Verify order.shipping_order_code is UNTOUCHED
        $order->refresh();
        $this->assertNull($order->shipping_order_code);

        // Verify PaymentTransaction was created
        $transaction = PaymentTransaction::where('order_id', $order->id)
            ->where('payment_method', 'payos')
            ->first();

        $this->assertNotNull($transaction);
        $this->assertEquals('pending', $transaction->status);
        $this->assertEquals($order->total_amount, $transaction->amount);
        $this->assertNotEmpty($transaction->transaction_id);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PayOS\Utils\PayOSSignatureUtils;
use Tests\TestCase;

class PayOSWebhookSecurityChallengeTest extends TestCase
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
     * Helper to generate a validly signed PayOS webhook payload.
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
            'customer_name' => 'Adversarial Test Customer',
            'customer_phone' => '0912345678',
            'address' => '123 Cyber Sec Ave, District 1, HCMC',
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

    // =========================================================================
    // CATEGORY 1: FORGED SIGNATURE ATTACKS
    // =========================================================================

    public function test_challenge_tampered_amount_fails_signature_verification(): void
    {
        $order = $this->createDummyOrder(['total_amount' => 500000]);
        $orderCode = 111222333;

        PaymentTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => (string) $orderCode,
            'amount' => 500000,
            'payment_method' => 'payos',
            'status' => 'pending',
        ]);

        // Legitimate data was for 500,000 VND
        $legitData = [
            'orderCode' => $orderCode,
            'amount' => 500000,
            'code' => '00',
        ];
        $legitSignature = PayOSSignatureUtils::createSignatureFromObj($this->checksumKey, $legitData);

        // Attacker intercepts and modifies amount to 1,000 VND but keeps original signature
        $tamperedPayload = [
            'code' => '00',
            'desc' => 'success',
            'data' => [
                'orderCode' => $orderCode,
                'amount' => 1000, // TAMPERED AMOUNT
                'code' => '00',
            ],
            'signature' => $legitSignature,
        ];

        $response = $this->postJson('/payos/webhook', $tamperedPayload);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 1,
            'message' => 'Invalid signature',
        ]);

        $order->refresh();
        $this->assertEquals('pending', $order->payment_status);
        $this->assertEquals('pending', $order->status);
        $this->assertFalse((bool) $order->is_inventory_deducted);
    }

    public function test_challenge_tampered_order_code_fails_signature_verification(): void
    {
        $orderA = $this->createDummyOrder(['total_amount' => 200000]);
        $orderB = $this->createDummyOrder(['total_amount' => 200000]);
        $orderCodeA = 1001;
        $orderCodeB = 1002;

        PaymentTransaction::create([
            'order_id' => $orderB->id,
            'transaction_id' => (string) $orderCodeB,
            'amount' => 200000,
            'payment_method' => 'payos',
            'status' => 'pending',
        ]);

        // Attacker has valid signature for Order A
        $dataA = [
            'orderCode' => $orderCodeA,
            'amount' => 200000,
            'code' => '00',
        ];
        $signatureA = PayOSSignatureUtils::createSignatureFromObj($this->checksumKey, $dataA);

        // Attacker tries to apply signature A to Order B
        $tamperedPayload = [
            'code' => '00',
            'desc' => 'success',
            'data' => [
                'orderCode' => $orderCodeB, // REPLACED WITH ORDER B
                'amount' => 200000,
                'code' => '00',
            ],
            'signature' => $signatureA,
        ];

        $response = $this->postJson('/payos/webhook', $tamperedPayload);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 1,
            'message' => 'Invalid signature',
        ]);

        $orderB->refresh();
        $this->assertEquals('pending', $orderB->payment_status);
        $this->assertEquals('pending', $orderB->status);
    }

    public function test_challenge_random_garbage_signature_rejected(): void
    {
        $order = $this->createDummyOrder();
        $orderCode = 777888999;

        PaymentTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => (string) $orderCode,
            'amount' => $order->total_amount,
            'payment_method' => 'payos',
            'status' => 'pending',
        ]);

        $payload = [
            'code' => '00',
            'desc' => 'success',
            'data' => [
                'orderCode' => $orderCode,
                'amount' => intval($order->total_amount),
                'code' => '00',
            ],
            'signature' => bin2hex(random_bytes(32)), // Completely random HMAC string
        ];

        $response = $this->postJson('/payos/webhook', $payload);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 1,
            'message' => 'Invalid signature',
        ]);

        $order->refresh();
        $this->assertEquals('pending', $order->payment_status);
    }

    public function test_challenge_signature_with_different_checksum_key_rejected(): void
    {
        $order = $this->createDummyOrder();
        $orderCode = 5544332211;

        PaymentTransaction::create([
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
        ];
        // Sign with an attacker's rogue checksum key
        $roguePayload = $this->generateWebhookPayload($data, 'rogue-attacker-checksum-key-666');

        $response = $this->postJson('/payos/webhook', $roguePayload);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 1,
            'message' => 'Invalid signature',
        ]);

        $order->refresh();
        $this->assertEquals('pending', $order->payment_status);
    }

    // =========================================================================
    // CATEGORY 2: MISSING SIGNATURE ATTACKS
    // =========================================================================

    public function test_challenge_missing_signature_field(): void
    {
        $payload = [
            'code' => '00',
            'desc' => 'success',
            'data' => [
                'orderCode' => 123456,
                'amount' => 100000,
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

    public function test_challenge_empty_signature_string(): void
    {
        $payload = [
            'code' => '00',
            'desc' => 'success',
            'data' => [
                'orderCode' => 123456,
                'amount' => 100000,
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

    public function test_challenge_null_signature_field(): void
    {
        $payload = [
            'code' => '00',
            'desc' => 'success',
            'data' => [
                'orderCode' => 123456,
                'amount' => 100000,
                'code' => '00',
            ],
            'signature' => null,
        ];

        $response = $this->postJson('/payos/webhook', $payload);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 1,
            'message' => 'Invalid signature',
        ]);
    }

    // =========================================================================
    // CATEGORY 3: MALFORMED PAYLOADS & DATA TYPES
    // =========================================================================

    public function test_challenge_empty_root_array(): void
    {
        $response = $this->postJson('/payos/webhook', []);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 1,
            'message' => 'Invalid signature',
        ]);
    }

    public function test_challenge_missing_data_key(): void
    {
        $payload = [
            'code' => '00',
            'desc' => 'success',
            'signature' => 'valid-format-dummy-signature-123456',
        ];

        $response = $this->postJson('/payos/webhook', $payload);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 1,
            'message' => 'Invalid signature',
        ]);
    }

    public function test_challenge_null_data_key(): void
    {
        $payload = [
            'code' => '00',
            'desc' => 'success',
            'data' => null,
            'signature' => 'valid-format-dummy-signature-123456',
        ];

        $response = $this->postJson('/payos/webhook', $payload);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 1,
            'message' => 'Invalid signature',
        ]);
    }

    public function test_challenge_empty_data_array(): void
    {
        $payload = [
            'code' => '00',
            'desc' => 'success',
            'data' => [],
            'signature' => 'valid-format-dummy-signature-123456',
        ];

        $response = $this->postJson('/payos/webhook', $payload);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 1,
            'message' => 'Invalid signature',
        ]);
    }

    public function test_challenge_scalar_string_data_payload(): void
    {
        // When data is a string instead of an array, does it crash with 500 (TypeError)?
        $payload = [
            'code' => '00',
            'desc' => 'success',
            'data' => 'malicious_string_instead_of_array',
            'signature' => 'random_signature_string',
        ];

        $response = $this->postJson('/payos/webhook', $payload);

        // Security requirement: Must NOT crash with unhandled 500
        $this->assertNotEquals(500, $response->getStatusCode(), 'Server crashed with HTTP 500 when data is a scalar string!');
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function test_challenge_scalar_int_data_payload(): void
    {
        // When data is an integer instead of an array
        $payload = [
            'code' => '00',
            'desc' => 'success',
            'data' => 999999,
            'signature' => 'random_signature_string',
        ];

        $response = $this->postJson('/payos/webhook', $payload);

        $this->assertNotEquals(500, $response->getStatusCode(), 'Server crashed with HTTP 500 when data is an integer!');
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function test_challenge_non_json_raw_string_payload(): void
    {
        // Attacker posts non-JSON raw body
        $response = $this->call(
            'POST',
            '/payos/webhook',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'NOT_VALID_JSON_AT_ALL{{{'
        );

        $this->assertNotEquals(500, $response->getStatusCode(), 'Server crashed with HTTP 500 on non-JSON raw body!');
        $this->assertContains($response->getStatusCode(), [400, 422]);
    }

    // =========================================================================
    // CATEGORY 4: NON-EXISTENT ORDERS
    // =========================================================================

    public function test_challenge_valid_signature_non_existent_order_returns_404(): void
    {
        $nonExistentOrderCode = 999999999999;

        $data = [
            'orderCode' => $nonExistentOrderCode,
            'amount' => 200000,
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

    public function test_challenge_valid_signature_missing_order_code_in_data_returns_404(): void
    {
        $data = [
            'amount' => 200000,
            'code' => '00',
            'desc' => 'success',
            // orderCode intentionally missing
        ];
        $payload = $this->generateWebhookPayload($data);

        $response = $this->postJson('/payos/webhook', $payload);

        $response->assertStatus(404);
        $response->assertJson([
            'error' => 1,
            'message' => 'Order not found',
        ]);
    }

    // =========================================================================
    // CATEGORY 5: FAILED PAYMENT CODES & STATE INTEGRITY
    // =========================================================================

    public function test_challenge_failed_code_01_does_not_mark_order_as_paid(): void
    {
        $order = $this->createDummyOrder();
        $orderCode = 88990011;

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
            'desc' => 'Giao dịch thất bại',
        ];
        $payload = $this->generateWebhookPayload($data, null, '01', 'Giao dịch thất bại');

        $response = $this->postJson('/payos/webhook', $payload);

        $response->assertStatus(200);

        // Crucial check: Order must NOT become paid, inventory must NOT be deducted
        $order->refresh();
        $this->assertEquals('pending', $order->payment_status);
        $this->assertEquals('pending', $order->status);
        $this->assertFalse((bool) $order->is_inventory_deducted);

        $transaction->refresh();
        $this->assertEquals('failed', $transaction->status);
    }

    public function test_challenge_failed_code_09_cancelled_does_not_mark_order_as_paid(): void
    {
        $order = $this->createDummyOrder();
        $orderCode = 77665544;

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
            'code' => '09',
            'desc' => 'Khách hàng hủy giao dịch',
        ];
        $payload = $this->generateWebhookPayload($data, null, '09', 'Khách hàng hủy giao dịch');

        $response = $this->postJson('/payos/webhook', $payload);

        $response->assertStatus(200);

        // Crucial check: Order must NOT become paid
        $order->refresh();
        $this->assertEquals('pending', $order->payment_status);
        $this->assertEquals('pending', $order->status);
        $this->assertFalse((bool) $order->is_inventory_deducted);

        $transaction->refresh();
        $this->assertEquals('failed', $transaction->status);
    }

    // =========================================================================
    // CATEGORY 6: ADVERSARIAL TAMPERING & STATUS BYPASS (CRITICAL FINDINGS)
    // =========================================================================

    /**
     * ADVERSARIAL ATTACK:
     * Failed payment code '01', signed authentic data, but attacker tampers the outer unsigned 'desc' to 'success'.
     * Condition in controller: if ($code === '00' || ($webhookData['desc'] ?? '') === 'success' || ($verifiedData['desc'] ?? '') === 'success')
     * If this allows unpaid order to become paid, IT IS A VULNERABILITY!
     */
    public function test_challenge_failed_code_with_tampered_outer_desc_success_must_not_mark_paid(): void
    {
        $order = $this->createDummyOrder();
        $orderCode = 6655443322;

        $transaction = PaymentTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => (string) $orderCode,
            'amount' => $order->total_amount,
            'payment_method' => 'payos',
            'status' => 'pending',
        ]);

        // Authentic failed transaction data signed by PayOS checksum key
        $data = [
            'orderCode' => $orderCode,
            'amount' => intval($order->total_amount),
            'code' => '01',
            'desc' => 'Payment failed',
        ];
        $signature = PayOSSignatureUtils::createSignatureFromObj($this->checksumKey, $data);

        // Attacker keeps valid signature of data, but injects "desc": "success" at root
        $tamperedPayload = [
            'code' => '01',
            'desc' => 'success', // TAMPERED ROOT DESC TO BYPASS CHECK
            'data' => $data,
            'signature' => $signature,
        ];

        $response = $this->postJson('/payos/webhook', $tamperedPayload);

        // Order MUST NOT be marked as paid!
        $order->refresh();
        $this->assertNotEquals('paid', $order->payment_status, 'CRITICAL VULNERABILITY CONFIRMED: Unsigned outer desc=success allowed failed payment to become paid!');
        $this->assertEquals('pending', $order->payment_status);
        $this->assertFalse((bool) $order->is_inventory_deducted);
    }

    /**
     * ADVERSARIAL ATTACK:
     * What if data['desc'] is 'success' while code is '01'?
     * (e.g., customer note or description contained 'success')
     */
    public function test_challenge_failed_code_with_desc_field_containing_success_must_not_mark_paid(): void
    {
        $order = $this->createDummyOrder();
        $orderCode = 4433221100;

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
            'code' => '01', // FAILED TRANSACTION
            'desc' => 'success', // Description contains 'success'
        ];
        $payload = $this->generateWebhookPayload($data, null, '01', 'success');

        $response = $this->postJson('/payos/webhook', $payload);

        $order->refresh();
        $this->assertNotEquals('paid', $order->payment_status, 'CRITICAL VULNERABILITY: code=01 with desc=success marked order as paid!');
        $this->assertEquals('pending', $order->payment_status);
        $this->assertFalse((bool) $order->is_inventory_deducted);
    }

    // =========================================================================
    // CATEGORY 7: PARAMETER TAMPERING & INJECTION ATTACKS
    // =========================================================================

    public function test_challenge_amount_underpayment_mismatch(): void
    {
        $order = $this->createDummyOrder(['total_amount' => 1000000]); // 1,000,000 VND
        $orderCode = 5566778899;

        PaymentTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => (string) $orderCode,
            'amount' => 1000000,
            'payment_method' => 'payos',
            'status' => 'pending',
        ]);

        // Attacker creates a webhook with a valid signature from PayOS, but paid amount is only 1,000 VND!
        $data = [
            'orderCode' => $orderCode,
            'amount' => 1000, // UNDERPAYMENT ATTACK
            'code' => '00',
            'desc' => 'success',
        ];
        $payload = $this->generateWebhookPayload($data);

        $response = $this->postJson('/payos/webhook', $payload);

        $order->refresh();
        // The business rule: Underpaid order should NOT be marked as fully paid!
        $this->assertNotEquals('paid', $order->payment_status, 'VULNERABILITY: Underpayment of 1,000 VND against 1,000,000 VND order was marked as paid!');
    }

    public function test_challenge_sql_injection_in_order_code_safe(): void
    {
        $sqlInjectionCode = "1' OR '1'='1";

        $data = [
            'orderCode' => $sqlInjectionCode,
            'amount' => 200000,
            'code' => '00',
            'desc' => 'success',
        ];
        $payload = $this->generateWebhookPayload($data);

        $response = $this->postJson('/payos/webhook', $payload);

        // Eloquent parameterization must prevent SQL injection; orderCode does not exist so returns 404
        $this->assertNotEquals(500, $response->getStatusCode(), 'Server crashed on SQL injection payload!');
        $response->assertStatus(404);
    }

    public function test_challenge_xss_in_desc_field_safe(): void
    {
        $order = $this->createDummyOrder();
        $orderCode = 99112233;

        $transaction = PaymentTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => (string) $orderCode,
            'amount' => $order->total_amount,
            'payment_method' => 'payos',
            'status' => 'pending',
        ]);

        $xssString = "<script>alert('XSS_PAYOS')</script>";
        $data = [
            'orderCode' => $orderCode,
            'amount' => intval($order->total_amount),
            'code' => '00',
            'desc' => $xssString,
        ];
        $payload = $this->generateWebhookPayload($data, null, '00', $xssString);

        $response = $this->postJson('/payos/webhook', $payload);

        $this->assertNotEquals(500, $response->getStatusCode());
        $response->assertStatus(200);

        $transaction->refresh();
        $this->assertEquals('success', $transaction->status);
    }

    public function test_challenge_webhook_behavior_on_already_cancelled_order(): void
    {
        $order = $this->createDummyOrder([
            'status' => 'cancelled',
            'payment_status' => 'pending',
            'is_inventory_deducted' => false,
        ]);
        $orderCode = 1122334455;

        PaymentTransaction::create([
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
        ];
        $payload = $this->generateWebhookPayload($data);

        $response = $this->postJson('/payos/webhook', $payload);

        $order->refresh();
        // Observation: When a cancelled order receives code '00', it gets un-cancelled and moved to processing
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('processing', $order->status);
        $this->assertTrue((bool) $order->is_inventory_deducted);
    }
}

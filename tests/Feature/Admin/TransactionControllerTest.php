<?php

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
        $order = Order::create([
            'user_id' => $this->admin->id,
            'customer_name' => 'Lê Thị C',
            'customer_phone' => '0901234567',
            'address' => '789 Giải Phóng, Hà Nội',
            'subtotal' => 300000,
            'shipping_fee' => 0,
            'total_amount' => 300000,
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
        ]);

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
        $order = Order::create([
            'user_id' => $this->admin->id,
            'customer_name' => 'Lê Thị C',
            'customer_phone' => '0901234567',
            'address' => '789 Giải Phóng, Hà Nội',
            'subtotal' => 450000,
            'shipping_fee' => 0,
            'total_amount' => 450000,
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
        ]);

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

<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FinanceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_access_finance_overview()
    {
        $order = Order::create([
            'user_id' => $this->admin->id,
            'customer_name' => 'Hoàng D',
            'customer_phone' => '0933445566',
            'address' => '321 Lê Duẩn, Hà Nội',
            'subtotal' => 1200000,
            'shipping_fee' => 0,
            'total_amount' => 1200000,
            'status' => 'completed',
            'payment_method' => 'payos',
            'payment_status' => 'paid',
        ]);

        PaymentTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => 'PAYOS-1234',
            'amount' => 1200000,
            'payment_method' => 'payos',
            'status' => 'success',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.finance.index'));
        $response->assertStatus(200);
        $response->assertViewHas('kpi');
        $response->assertViewHas('chartData');
        $this->assertEquals(1200000, $response->viewData('kpi')['collected_revenue']);
    }
}

<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentTransactionModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_transaction_supports_reconciliation_fields()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = Order::create([
            'user_id' => $admin->id,
            'customer_name' => 'Nguyễn Văn A',
            'customer_phone' => '0987654321',
            'address' => '123 Đường ABC, Hà Nội',
            'subtotal' => 500000,
            'shipping_fee' => 0,
            'total_amount' => 500000,
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
        ]);

        $transaction = PaymentTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => 'COD-ORD-' . $order->id,
            'amount' => 500000,
            'payment_method' => 'cod',
            'status' => 'success',
            'admin_id' => $admin->id,
            'note' => 'Bưu tá nộp tiền đợt 1',
            'reconciled_at' => now(),
        ]);

        $this->assertDatabaseHas('payment_transactions', [
            'id' => $transaction->id,
            'admin_id' => $admin->id,
            'payment_method' => 'cod',
            'note' => 'Bưu tá nộp tiền đợt 1',
        ]);
        $this->assertEquals($admin->id, $transaction->reconciledBy->id);
    }
}

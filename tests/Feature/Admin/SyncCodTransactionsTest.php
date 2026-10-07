<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SyncCodTransactionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_command_creates_transactions_for_legacy_cod_orders()
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'Trần Văn B',
            'customer_phone' => '0912345678',
            'address' => '456 Phố Huế, Hà Nội',
            'subtotal' => 750000,
            'shipping_fee' => 0,
            'total_amount' => 750000,
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
        ]);

        $this->assertEquals(0, $order->transactions()->count());

        $this->artisan('app:sync-cod-transactions')
            ->expectsOutputToContain('Đã đồng bộ giao dịch cho các đơn hàng COD')
            ->assertExitCode(0);

        $this->assertEquals(1, $order->fresh()->transactions()->count());
        $transaction = $order->transactions()->first();
        $this->assertEquals('cod', $transaction->payment_method);
        $this->assertEquals(750000, (float) $transaction->amount);
    }
}

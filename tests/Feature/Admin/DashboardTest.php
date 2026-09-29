<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_admin_dashboard()
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    }

    public function test_regular_users_cannot_access_admin_dashboard()
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_dashboard_with_operational_cockpit_metrics()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Create pending order
        $order = Order::create([
            'user_id' => $admin->id,
            'customer_name' => 'Nguyễn Thị Hoa',
            'customer_phone' => '0912345678',
            'address' => '45 Kim Mã, Ba Đình, Hà Nội',
            'total_amount' => 450000,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'cod',
        ]);

        // Create critical stock variant (<= 5)
        $product = Product::create([
            'name' => 'Đầm Xòe Công Sở',
            'slug' => 'dam-xoe-cong-so',
            'base_price' => 450000,
            'status' => 'active',
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'DX-001',
            'price' => 450000,
            'stock_quantity' => 3,
        ]);

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Nguyễn Thị Hoa');
        $response->assertSee('Đầm Xòe Công Sở');
        $response->assertSee(route('admin.analytics.index'), false);
    }
}

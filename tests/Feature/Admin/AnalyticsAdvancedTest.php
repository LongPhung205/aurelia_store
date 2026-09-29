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

class AnalyticsAdvancedTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_rfm_tab()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/analytics?tab=rfm');
        $response->assertStatus(200);
        $response->assertViewHas('activeTab', 'rfm');
        $response->assertViewHas('rfmData');
    }

    public function test_admin_can_access_market_basket_tab()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/analytics?tab=basket');
        $response->assertStatus(200);
        $response->assertViewHas('activeTab', 'basket');
        $response->assertViewHas('basketData');
    }

    public function test_admin_can_access_sales_tab_with_slow_products()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Váy Dạ Hội', 'slug' => 'vay-da-hoi']);

        $product = Product::create([
            'name' => 'Váy Dạ Hội Xẻ Tà',
            'slug' => 'vay-da-hoi-xe-ta',
            'description' => 'Test',
            'base_price' => 500000,
            'status' => 'active',
        ]);
        $product->categories()->attach($category->id);

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'VDH-001',
            'price' => 500000,
            'stock_quantity' => 15,
        ]);

        $response = $this->actingAs($admin)->get('/admin/analytics?tab=sales');
        $response->assertStatus(200);
        $response->assertSee(route('admin.flash_sales.index'), false);
    }

    public function test_admin_can_access_basket_tab_with_rules_rendered()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $p1 = Product::create(['name' => 'Áo Vest', 'slug' => 'ao-vest', 'base_price' => 300000, 'status' => 'active']);
        $v1 = ProductVariant::create(['product_id' => $p1->id, 'sku' => 'V1', 'price' => 300000, 'stock_quantity' => 10]);

        $p2 = Product::create(['name' => 'Chân Váy', 'slug' => 'chan-vay', 'base_price' => 200000, 'status' => 'active']);
        $v2 = ProductVariant::create(['product_id' => $p2->id, 'sku' => 'V2', 'price' => 200000, 'stock_quantity' => 10]);

        // Create 2 multi-item orders
        for ($i = 0; $i < 2; $i++) {
            $order = Order::create([
                'user_id' => $admin->id,
                'customer_name' => 'Customer ' . $i,
                'customer_email' => 'customer' . $i . '@example.com',
                'customer_phone' => '090123456' . $i,
                'address' => '123 Test Street, Hanoi',
                'total_amount' => 500000,
                'status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => 'cod',
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_variant_id' => $v1->id,
                'product_name' => 'Áo Vest',
                'quantity' => 1,
                'price' => 300000,
                'total' => 300000,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_variant_id' => $v2->id,
                'product_name' => 'Chân Váy',
                'quantity' => 1,
                'price' => 200000,
                'total' => 200000,
            ]);
        }

        $response = $this->actingAs($admin)->get('/admin/analytics?tab=basket');
        $response->assertStatus(200);
        $response->assertSee(route('admin.flash_sales.index'), false);
    }
}

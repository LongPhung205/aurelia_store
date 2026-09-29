<?php

namespace Tests\Feature\Client;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDetailFrequentlyBoughtTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_detail_page_loads_with_frequently_bought_together_combo()
    {
        $category = Category::create(['name' => 'Thời Trang', 'slug' => 'thoi-trang']);

        $p1 = Product::create([
            'name' => 'Áo Blazer Thanh Lịch',
            'slug' => 'ao-blazer-thanh-lich',
            'base_price' => 600000,
            'status' => 'active',
        ]);
        $p1->categories()->attach($category->id);
        $v1 = ProductVariant::create(['product_id' => $p1->id, 'sku' => 'BLZ-01', 'price' => 600000, 'stock_quantity' => 10]);

        $p2 = Product::create([
            'name' => 'Quần Tây Ống Suông',
            'slug' => 'quan-tay-ong-suong',
            'base_price' => 400000,
            'status' => 'active',
        ]);
        $p2->categories()->attach($category->id);
        $v2 = ProductVariant::create(['product_id' => $p2->id, 'sku' => 'QT-01', 'price' => 400000, 'stock_quantity' => 10]);

        $user = User::factory()->create();

        // Create 2 multi-item orders to train Apriori
        for ($i = 0; $i < 2; $i++) {
            $order = Order::create([
                'user_id' => $user->id,
                'customer_name' => 'Customer ' . $i,
                'customer_email' => 'cust' . $i . '@example.com',
                'customer_phone' => '091234567' . $i,
                'address' => '123 Test Street, Hanoi',
                'total_amount' => 1000000,
                'status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => 'cod',
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_variant_id' => $v1->id,
                'product_name' => $p1->name,
                'quantity' => 1,
                'price' => 600000,
                'total' => 600000,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_variant_id' => $v2->id,
                'product_name' => $p2->name,
                'quantity' => 1,
                'price' => 400000,
                'total' => 400000,
            ]);
        }

        $response = $this->get('/products/' . $p1->slug);
        $response->assertStatus(200);
        $response->assertViewHas('frequentlyBoughtTogether');
        $fbt = $response->viewData('frequentlyBoughtTogether');
        $this->assertNotNull($fbt);
        $this->assertEquals($p2->id, $fbt['paired_product']['id']);
        $this->assertGreaterThan(0, $fbt['bundle_price']);
        $this->assertGreaterThan(0, $fbt['savings']);
    }

    public function test_cart_add_combo_endpoint_adds_multiple_variants_atomically()
    {
        $category = Category::create(['name' => 'Thời Trang', 'slug' => 'thoi-trang']);

        $p1 = Product::create([
            'name' => 'Áo Sơ Mi Silk',
            'slug' => 'ao-so-mi-silk',
            'base_price' => 350000,
            'status' => 'active',
        ]);
        $v1 = ProductVariant::create(['product_id' => $p1->id, 'sku' => 'SMS-01', 'price' => 350000, 'stock_quantity' => 20]);

        $p2 = Product::create([
            'name' => 'Chân Váy Chữ A',
            'slug' => 'chan-vay-chu-a',
            'base_price' => 280000,
            'status' => 'active',
        ]);
        $v2 = ProductVariant::create(['product_id' => $p2->id, 'sku' => 'CVA-01', 'price' => 280000, 'stock_quantity' => 15]);

        $response = $this->postJson(route('cart.add-combo'), [
            'items' => [
                ['variant_id' => $v1->id, 'quantity' => 1],
                ['variant_id' => $v2->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertEquals(2, $response->json('cart_quantity'));
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_analytics()
    {
        $response = $this->get('/admin/analytics');
        $response->assertRedirect('/login');
    }

    public function test_regular_users_cannot_access_analytics()
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($user)->get('/admin/analytics');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_analytics_index()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get('/admin/analytics');
        $response->assertStatus(200);
        $response->assertViewIs('admin.analytics.index');
    }

    public function test_analytics_returns_correct_kpis_and_aggregations()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'user', 'created_at' => now()]);

        \App\Models\Order::create([
            'user_id' => $customer->id,
            'customer_name' => 'Test User',
            'customer_phone' => '0987654321',
            'address' => 'Hanoi, Vietnam',
            'subtotal' => 500000,
            'total_amount' => 500000,
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => 'cod',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/analytics?range=last_7_days');
        $response->assertStatus(200);
        $response->assertViewHas('data');

        $data = $response->viewData('data');
        $this->assertEquals(500000, $data['kpis']['revenue']['current']);
        $this->assertEquals(1, $data['kpis']['orders']['current']);
        $this->assertEquals(500000, $data['kpis']['aov']['current']);
        $this->assertNotEmpty($data['trendChart']['labels']);
    }

    public function test_analytics_handles_all_preset_ranges()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $ranges = ['today', 'last_7_days', 'last_30_days', 'this_month', 'this_year'];

        foreach ($ranges as $range) {
            $response = $this->actingAs($admin)->get("/admin/analytics?range={$range}");
            $response->assertStatus(200);
            $response->assertViewHas('data');
        }

        // Test custom range
        $response = $this->actingAs($admin)->get('/admin/analytics?range=custom&from_date=2026-09-01&to_date=2026-09-15');
        $response->assertStatus(200);
        $response->assertViewHas('data');
    }

    public function test_admin_can_export_analytics_csv()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/analytics/export?range=last_7_days');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('BÁO CÁO PHÂN TÍCH HOẠT ĐỘNG KINH DOANH', $content);
        $this->assertStringContainsString('Doanh thu thuần', $content);
    }
}

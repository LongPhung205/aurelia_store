<?php

namespace Tests\Feature\Admin;

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
}

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
}

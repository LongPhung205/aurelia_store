<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FinanceNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_contains_finance_and_transaction_links()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee(route('admin.finance.index'));
        $response->assertSee(route('admin.transactions.index'));
    }
}

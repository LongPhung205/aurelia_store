<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->post('/profile/update', [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'phone' => '0987654321',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Updated Name', $user->name);
        $this->assertSame('updated@example.com', $user->email);
        $this->assertSame('0987654321', $user->phone);
    }

    public function test_profile_password_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile/password');

        $response->assertOk();
    }

    public function test_password_can_be_updated_via_profile(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password-123'),
        ]);

        $response = $this
            ->actingAs($user)
            ->from('/profile/password')
            ->post('/profile/password/update', [
                'current_password' => 'old-password-123',
                'password' => 'new-password-456',
                'password_confirmation' => 'new-password-456',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile/password');

        $this->assertTrue(Hash::check('new-password-456', $user->refresh()->password));
    }

    public function test_password_cannot_be_updated_with_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('correct-password'),
        ]);

        $response = $this
            ->actingAs($user)
            ->from('/profile/password')
            ->post('/profile/password/update', [
                'current_password' => 'wrong-password',
                'password' => 'new-password-456',
                'password_confirmation' => 'new-password-456',
            ]);

        $response
            ->assertSessionHasErrors('current_password')
            ->assertRedirect('/profile/password');

        $this->assertTrue(Hash::check('correct-password', $user->refresh()->password));
    }

    public function test_user_can_add_and_delete_address(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile/addresses')
            ->post('/profile/addresses', [
                'name' => 'Nguyen Van A',
                'phone' => '0912345678',
                'province_id' => '201',
                'district_id' => '1482',
                'ward_code' => '11007',
                'address' => '123 Le Loi Street',
            ]);

        $response->assertSessionHasNoErrors();

        $address = UserAddress::where('user_id', $user->id)->first();
        $this->assertNotNull($address);
        $this->assertSame('123 Le Loi Street', $address->address);

        $deleteResponse = $this
            ->actingAs($user)
            ->delete("/profile/addresses/{$address->id}");

        $deleteResponse->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('user_addresses', ['id' => $address->id]);
    }
}

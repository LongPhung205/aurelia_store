<?php

namespace Tests\Feature\Auth;

use App\Mail\RegistrationOtpMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_registration_otp_can_be_requested(): void
    {
        Mail::fake();

        $response = $this->postJson('/register/send-otp', [
            'email' => 'newuser@example.com',
        ]);

        $response->assertStatus(200);
        $this->assertNotNull(Cache::get('otp_newuser@example.com'));
        Mail::assertSent(RegistrationOtpMail::class);
    }

    public function test_new_users_can_register_with_valid_otp(): void
    {
        $email = 'test@example.com';
        $otp = '123456';
        Cache::put('otp_'.$email, $otp, now()->addMinutes(5));

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => $email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'otp_code' => $otp,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNull(Cache::get('otp_'.$email));
    }

    public function test_new_users_cannot_register_with_invalid_otp(): void
    {
        $email = 'test@example.com';
        Cache::put('otp_'.$email, '123456', now()->addMinutes(5));

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => $email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'otp_code' => '999999',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('otp_code');
    }

    public function test_new_users_cannot_register_without_otp(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('otp_code');
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Mail\RegistrationOtpMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_is_marked_verified_upon_successful_otp_registration(): void
    {
        $email = 'verify_test@example.com';
        $otp = '654321';
        Cache::put('otp_'.$email, $otp, now()->addMinutes(5));

        $response = $this->post('/register', [
            'name' => 'Verify Test User',
            'email' => $email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'otp_code' => $otp,
        ]);

        $response->assertSessionHasNoErrors();
        $user = User::where('email', $email)->firstOrFail();
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_unverified_user_has_not_verified_email(): void
    {
        $user = User::factory()->unverified()->create();

        $this->assertFalse($user->hasVerifiedEmail());
        $this->assertNull($user->email_verified_at);
    }

    public function test_registration_otp_email_is_sent_for_verification(): void
    {
        Mail::fake();

        $response = $this->postJson('/register/send-otp', [
            'email' => 'otp_verify@example.com',
        ]);

        $response->assertStatus(200);

        Mail::assertSent(RegistrationOtpMail::class, function ($mail) {
            return ! empty($mail->otp);
        });
    }
}

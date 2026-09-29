<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use Tests\TestCase;

class AdminUserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_throws_exception_when_admin_email_is_missing(): void
    {
        Config::set('seeding.admin.email', null);
        Config::set('seeding.admin.password', 'ValidStrongP@ssw0rd123');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('SEED_ADMIN_EMAIL must be a valid email address.');

        $this->seed(AdminUserSeeder::class);
    }

    public function test_seeder_throws_exception_when_admin_email_is_invalid(): void
    {
        Config::set('seeding.admin.email', 'not-a-valid-email');
        Config::set('seeding.admin.password', 'ValidStrongP@ssw0rd123');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('SEED_ADMIN_EMAIL must be a valid email address.');

        $this->seed(AdminUserSeeder::class);
    }

    public function test_seeder_throws_exception_when_password_is_shorter_than_12_characters(): void
    {
        Config::set('seeding.admin.email', 'admin@example.com');
        Config::set('seeding.admin.password', 'short123');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('SEED_ADMIN_PASSWORD must be at least 12 characters long.');

        $this->seed(AdminUserSeeder::class);
    }

    public function test_seeder_creates_admin_user_successfully_when_valid(): void
    {
        Config::set('seeding.admin.name', 'Super Admin');
        Config::set('seeding.admin.email', 'admin@aureliastore.com');
        Config::set('seeding.admin.password', 'StrongP@ssw0rd2026!');

        $this->seed(AdminUserSeeder::class);

        $this->assertDatabaseHas('users', [
            'name' => 'Super Admin',
            'email' => 'admin@aureliastore.com',
            'role' => 'admin',
        ]);

        $admin = User::where('email', 'admin@aureliastore.com')->first();
        $this->assertNotNull($admin);
        $this->assertNotNull($admin->email_verified_at);
        $this->assertTrue(Hash::check('StrongP@ssw0rd2026!', $admin->password));
    }

    public function test_seeder_does_not_duplicate_existing_admin(): void
    {
        $existingAdmin = User::create([
            'name' => 'Existing Admin',
            'email' => 'admin@aureliastore.com',
            'password' => 'InitialStrongP@ssw0rd!',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        Config::set('seeding.admin.name', 'New Name');
        Config::set('seeding.admin.email', 'admin@aureliastore.com');
        Config::set('seeding.admin.password', 'DifferentP@ssw0rd123!');

        $this->seed(AdminUserSeeder::class);

        $this->assertEquals(1, User::where('email', 'admin@aureliastore.com')->count());

        $admin = User::where('email', 'admin@aureliastore.com')->first();
        $this->assertTrue(Hash::check('InitialStrongP@ssw0rd!', $admin->password));
    }
}

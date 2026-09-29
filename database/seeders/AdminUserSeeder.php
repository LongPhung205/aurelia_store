<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use InvalidArgumentException;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $name = config('seeding.admin.name') ?: 'Aurelia Admin';
        $email = config('seeding.admin.email');
        $password = config('seeding.admin.password');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('SEED_ADMIN_EMAIL must be a valid email address.');
        }

        if (empty($password) || strlen($password) < 12) {
            throw new InvalidArgumentException('SEED_ADMIN_PASSWORD must be at least 12 characters long.');
        }

        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            $this->command?->info("Admin user [{$email}] already exists. Skipping creation.");
            return;
        }

        $admin = new User();
        $admin->forceFill([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        $admin->save();

        $this->command?->info("Admin user [{$email}] successfully created with role admin.");
    }
}

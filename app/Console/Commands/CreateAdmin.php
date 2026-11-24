<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create {email} {--name=}';

    protected $description = 'Create an administrator account or promote an existing user';

    public function handle(): int
    {
        $email = strtolower(trim($this->argument('email')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Enter a valid email address.');

            return self::INVALID;
        }

        $user = User::where('email', $email)->first();
        if ($user) {
            if ($user->isAdmin()) {
                $this->info('This account is already an administrator.');

                return self::SUCCESS;
            }

            if (!$this->confirm("Promote {$email} to administrator?")) {
                return self::FAILURE;
            }

            $user->update(['role' => 'admin']);
        } else {
            $name = trim((string) $this->option('name'));
            if ($name === '') {
                $name = trim($this->ask('Administrator name', 'Administrator'));
            }

            $password = $this->secret('Password (minimum 12 characters)');
            $confirmation = $this->secret('Confirm password');
            if (strlen((string) $password) < 12 || $password !== $confirmation) {
                $this->error('The password must be at least 12 characters and both entries must match.');

                return self::FAILURE;
            }

            User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin',
            ]);
        }

        $this->info("Administrator account ready for {$email}.");

        return self::SUCCESS;
    }
}

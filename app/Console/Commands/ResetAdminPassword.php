<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('admin:reset-password {username} {password}')]
#[Description('Reset password admin (guard web) dari luar sistem. Contoh: php artisan admin:reset-password admin rahasia123')]
class ResetAdminPassword extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $username = (string) $this->argument('username');
        $password = (string) $this->argument('password');

        $user = User::query()->where('username', $username)->first();

        if (! $user) {
            $this->error("Admin dengan username '{$username}' tidak ditemukan.");

            return self::FAILURE;
        }

        // Kolom password User di-cast 'hashed' → nilai ini otomatis di-bcrypt.
        $user->password = $password;
        $user->save();

        $this->info("Password untuk '{$username}' berhasil direset.");

        return self::SUCCESS;
    }
}

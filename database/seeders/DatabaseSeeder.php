<?php

namespace Database\Seeders;

use App\Models\AppConfig;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'fullname' => 'Administrator',
                'password' => Hash::make('admin123'),
                'phone' => '',
                'email' => 'admin@example.com',
                'status' => 'Active',
                'creationdate' => now(),
            ],
        );

        // Default app settings (idempotent).
        $defaults = [
            'company_name' => 'PHPNuxBill',
            'currency_symbol' => 'Rp',
            'currency_code' => 'IDR',
            'country_code_phone' => '62',
        ];
        foreach ($defaults as $key => $value) {
            AppConfig::set($key, $value);
        }
    }
}

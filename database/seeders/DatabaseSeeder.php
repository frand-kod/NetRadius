<?php

namespace Database\Seeders;

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
                'user_type' => 'SuperAdmin',
                'status' => 'Active',
                'creationdate' => now(),
            ],
        );
    }
}

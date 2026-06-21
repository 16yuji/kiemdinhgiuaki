<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Quản trị viên',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
                'status' => 'active',
                'phone' => '0900000001',
            ]
        );

        User::updateOrCreate(
            ['email' => 'owner@example.com'],
            [
                'name' => 'Chủ cơ sở mẫu',
                'password' => Hash::make('12345678'),
                'role' => 'owner',
                'status' => 'active',
                'phone' => '0900000002',
                'bank_name' => 'Vietcombank',
                'bank_account_number' => '123456789',
                'bank_account_name' => 'CHU CO SO MAU',
            ]
        );

        User::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Khách hàng mẫu',
                'password' => Hash::make('12345678'),
                'role' => 'customer',
                'status' => 'active',
                'phone' => '0900000003',
            ]
        );
    }
}
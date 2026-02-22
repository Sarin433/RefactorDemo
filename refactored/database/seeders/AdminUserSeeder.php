<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@oms.local'],
            [
                'first_name' => 'Admin',
                'last_name'  => 'OMS',
                'phone'      => '0800000000',
                'password'   => Hash::make('password'),
                'role'       => 'admin',
            ]
        );
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@bpcontrol.local')],
            [
                'name' => env('ADMIN_NAME', 'prof. Ing. Juraj Ďuďák, PhD.'),
                'password' => env('ADMIN_PASSWORD', 'adm1n123456##..'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ],
        );
    }
}

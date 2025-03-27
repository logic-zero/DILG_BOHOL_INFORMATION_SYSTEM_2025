<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'Developer',
            'position' => 'Developer',
            'email' => 'new_developer@gmail.com',
            'email_verified_at' => now(),
            'password' => '12345678',

        ]);
    }
}

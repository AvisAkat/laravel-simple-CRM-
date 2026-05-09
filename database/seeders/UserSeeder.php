<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            "first_name"=> "Admin",
            "last_name"=> "User",
            "email"=> "admin@email.com",
            'password'=> Hash::make('12345'),
            'role'=> 'admin',
            'status' => 'active',
            'phone' => '1234567890',
        ]);
    }
}

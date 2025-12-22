<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // User 1: The Buyer
        User::create([
            'name' => 'BigMan',
            'email' => 'big@example.com',
            'password' => Hash::make('password123'),
            'balance' => 50000.00000000, // $50,000 USD for testing
        ]);

        // User 2: The Seller
        User::create([
            'name' => 'SmallMan',
            'email' => 'small@example.com',
            'password' => Hash::make('password123'),
            'balance' => 1000.00000000,  // $1,000 USD
        ]);
    }
}

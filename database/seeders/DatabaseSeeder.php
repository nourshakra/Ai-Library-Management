<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@library.test'],
            [
                'name' => 'Library Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        $categories = [
            'Programming',
            'Artificial Intelligence',
            'Database',
            'Web Development',
            'Cyber Security',
            'Networking',
            'Business',
            'Science',
            'Literature',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}

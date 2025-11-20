<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first user or create one
        $user = \App\Models\User::first();
        
        if (!$user) {
            $user = \App\Models\User::create([
                'name' => 'Test User',
                'email' => 'test@test.com',
                'password' => bcrypt('password123')
            ]);
        }

        $categories = [
            ['user_id' => $user->id, 'name' => 'Makanan & Minuman', 'icon' => '🍔', 'color' => '#EF4444'],
            ['user_id' => $user->id, 'name' => 'Transportasi', 'icon' => '🚗', 'color' => '#3B82F6'],
            ['user_id' => $user->id, 'name' => 'Belanja', 'icon' => '🛒', 'color' => '#8B5CF6'],
            ['user_id' => $user->id, 'name' => 'Hiburan', 'icon' => '🎮', 'color' => '#EC4899'],
            ['user_id' => $user->id, 'name' => 'Tagihan', 'icon' => '💳', 'color' => '#F59E0B'],
            ['user_id' => $user->id, 'name' => 'Kesehatan', 'icon' => '🏥', 'color' => '#10B981'],
            ['user_id' => $user->id, 'name' => 'Pendidikan', 'icon' => '📚', 'color' => '#6366F1'],
            ['user_id' => $user->id, 'name' => 'Gaji', 'icon' => '💰', 'color' => '#059669'],
            ['user_id' => $user->id, 'name' => 'Lainnya', 'icon' => '📦', 'color' => '#6B7280'],
        ];

        foreach ($categories as $category) {
            \App\Models\Category::create($category);
        }
    }
}

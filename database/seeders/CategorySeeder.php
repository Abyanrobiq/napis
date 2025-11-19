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
        $categories = [
            ['name' => 'Makanan & Minuman', 'icon' => '🍔', 'color' => '#EF4444'],
            ['name' => 'Transportasi', 'icon' => '🚗', 'color' => '#3B82F6'],
            ['name' => 'Belanja', 'icon' => '🛒', 'color' => '#8B5CF6'],
            ['name' => 'Hiburan', 'icon' => '🎮', 'color' => '#EC4899'],
            ['name' => 'Tagihan', 'icon' => '💳', 'color' => '#F59E0B'],
            ['name' => 'Kesehatan', 'icon' => '🏥', 'color' => '#10B981'],
            ['name' => 'Pendidikan', 'icon' => '📚', 'color' => '#6366F1'],
            ['name' => 'Gaji', 'icon' => '💰', 'color' => '#059669'],
            ['name' => 'Lainnya', 'icon' => '📦', 'color' => '#6B7280'],
        ];

        foreach ($categories as $category) {
            \App\Models\Category::create($category);
        }
    }
}

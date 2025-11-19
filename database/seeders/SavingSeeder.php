<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SavingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = \App\Models\User::first();
        
        if (!$user) {
            echo "No user found. Please create a user first.\n";
            return;
        }

        $savings = [
            [
                'user_id' => $user->id,
                'name' => 'Beli Mobil Baru',
                'description' => 'Target beli mobil impian Toyota Avanza',
                'target_amount' => 200000000,
                'current_amount' => 50000000,
                'icon' => '🚗',
                'color' => '#3B82F6',
                'target_date' => '2026-12-31',
                'status' => 'active',
            ],
            [
                'user_id' => $user->id,
                'name' => 'Liburan ke Jepang',
                'description' => 'Liburan keluarga ke Tokyo dan Osaka',
                'target_amount' => 30000000,
                'current_amount' => 15000000,
                'icon' => '✈️',
                'color' => '#EC4899',
                'target_date' => '2025-06-30',
                'status' => 'active',
            ],
            [
                'user_id' => $user->id,
                'name' => 'Dana Darurat',
                'description' => 'Dana darurat untuk 6 bulan pengeluaran',
                'target_amount' => 50000000,
                'current_amount' => 35000000,
                'icon' => '🛡️',
                'color' => '#10B981',
                'target_date' => '2025-12-31',
                'status' => 'active',
            ],
            [
                'user_id' => $user->id,
                'name' => 'Laptop Baru',
                'description' => 'MacBook Pro M3 untuk kerja',
                'target_amount' => 25000000,
                'current_amount' => 25000000,
                'icon' => '💻',
                'color' => '#8B5CF6',
                'target_date' => '2025-03-31',
                'status' => 'completed',
            ],
        ];

        foreach ($savings as $saving) {
            \App\Models\Saving::create($saving);
        }

        echo "Savings seeded successfully!\n";
    }
}

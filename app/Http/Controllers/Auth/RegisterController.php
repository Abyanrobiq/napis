<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        // Buat kategori default untuk user baru
        $this->createDefaultCategories();

        return redirect()->route('dashboard')->with('success', 'Registrasi berhasil! Selamat datang. Kategori default telah dibuat untuk Anda.');
    }

    private function createDefaultCategories()
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

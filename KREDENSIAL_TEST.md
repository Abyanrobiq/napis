# Kredensial Test

## User Test yang Sudah Dibuat

**Email:** test@test.com  
**Password:** password123

## Cara Login

1. Jalankan server: `php artisan serve`
2. Buka browser: `http://localhost:8000`
3. Klik "Masuk" atau langsung ke `/login`
4. Masukkan kredensial di atas
5. Klik "Masuk"

## Atau Buat Akun Baru

1. Klik "Daftar sekarang" di halaman login
2. Isi form registrasi
3. Kategori default akan otomatis dibuat

## Fitur yang Bisa Dicoba

1. **Atur Saldo Awal** - Di dashboard, masukkan saldo awal Anda
2. **Lihat Kategori** - 9 kategori default sudah tersedia
3. **Buat Budget** - Misalnya: Budget Makanan Rp 1.000.000 untuk bulan ini
4. **Tambah Transaksi** - Catat pengeluaran dan lihat budget berkurang otomatis
5. **Lihat Dashboard** - Monitor semua budget dan transaksi Anda
6. **Savings Goals** - Buat tujuan tabungan (e.g., Beli Mobil, Liburan)
   - Add money ke savings
   - Withdraw money dari savings
   - Track progress dengan visual progress bar

## Data Sample (Setelah Seeding)

Jika Anda menjalankan seeder, akan ada data sample:
- 9 Kategori default
- 4 Savings goals:
  - 🚗 Beli Mobil Baru (Rp 200jt target, 50jt terkumpul)
  - ✈️ Liburan ke Jepang (Rp 30jt target, 15jt terkumpul)
  - 🛡️ Dana Darurat (Rp 50jt target, 35jt terkumpul)
  - 💻 Laptop Baru (Completed - Rp 25jt)

## Reset Database (Jika Perlu)

```bash
php artisan migrate:fresh
```

Lalu buat user baru dengan registrasi atau jalankan:
```bash
php artisan tinker
```
Kemudian:
```php
User::create(['name' => 'Test', 'email' => 'test@test.com', 'password' => bcrypt('password123')]);
```

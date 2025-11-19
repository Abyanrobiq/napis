# 🎯 Fitur Savings Goals

## Deskripsi
Fitur Savings Goals memungkinkan pengguna untuk membuat dan melacak tujuan tabungan mereka. Berbeda dengan Budget yang untuk mengontrol pengeluaran, Savings adalah untuk target tabungan jangka panjang.

## Contoh Penggunaan
- 🚗 Beli Mobil Baru
- ✈️ Liburan ke Luar Negeri
- 🏠 DP Rumah
- 💍 Dana Pernikahan
- 📱 Gadget Baru
- 🎓 Dana Pendidikan
- 🛡️ Dana Darurat
- 💻 Upgrade Laptop

## Fitur Utama

### 1. Create Saving Goal
- **Nama Goal**: Tujuan tabungan (e.g., "Beli Mobil Baru")
- **Deskripsi**: Detail tentang goal
- **Target Amount**: Jumlah uang yang ingin dicapai
- **Current Amount**: Jumlah yang sudah terkumpul
- **Icon**: Emoji untuk visual (🚗 🏠 ✈️ 💍 📱 🎓)
- **Color**: Warna custom untuk progress bar
- **Target Date**: Tanggal target tercapai
- **Status**: Active, Completed, Paused

### 2. Track Progress
- Visual progress bar dengan persentase
- Menampilkan current amount vs target amount
- Menampilkan remaining amount (sisa yang perlu ditabung)
- Auto update status ke "Completed" saat target tercapai

### 3. Add Money
- Tambah uang ke savings goal
- Modal popup untuk input amount
- Auto update progress bar
- Auto change status ke "Completed" jika sudah mencapai target

### 4. Withdraw Money
- Tarik uang dari savings goal
- Validasi: tidak bisa tarik lebih dari current amount
- Auto update progress bar
- Auto change status kembali ke "Active" jika dari "Completed"

### 5. Edit & Delete
- Edit semua informasi goal
- Delete goal (dengan konfirmasi)

## Tampilan

### Index Page
- Grid layout dengan cards
- Setiap card menampilkan:
  - Icon dan nama goal
  - Status badge (Active/Completed/Paused)
  - Deskripsi
  - Progress bar dengan warna custom
  - Current amount vs Target amount
  - Remaining amount
  - Action buttons: Add Money, Withdraw, Edit, Delete

### Dashboard Integration
- Menampilkan 5 active savings goals terbaru
- Progress bar mini untuk setiap goal
- Total savings amount di bawah

### Create/Edit Form
- Form lengkap dengan validasi
- Color picker untuk custom color
- Date picker untuk target date
- Emoji input untuk icon
- Status dropdown

## Database Structure

```sql
CREATE TABLE savings (
    id BIGINT PRIMARY KEY,
    user_id BIGINT (FK to users),
    name VARCHAR(255),
    description TEXT,
    target_amount DECIMAL(15,2),
    current_amount DECIMAL(15,2) DEFAULT 0,
    icon VARCHAR(10),
    color VARCHAR(7) DEFAULT '#10B981',
    target_date DATE,
    status ENUM('active', 'completed', 'paused') DEFAULT 'active',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## Routes

```php
// CRUD
GET    /savings              - Index (list all)
GET    /savings/create       - Create form
POST   /savings              - Store
GET    /savings/{id}/edit    - Edit form
PUT    /savings/{id}         - Update
DELETE /savings/{id}         - Delete

// Actions
POST   /savings/{id}/add     - Add money
POST   /savings/{id}/withdraw - Withdraw money
```

## Model Methods

```php
// Hitung persentase progress
$saving->progressPercentage() // Returns 0-100

// Hitung sisa yang perlu ditabung
$saving->remainingAmount() // Returns decimal

// Cek apakah sudah tercapai
$saving->isCompleted() // Returns boolean
```

## Tips Penggunaan

1. **Buat Goal yang Spesifik**: Jangan hanya "Tabungan", tapi "Beli iPhone 15 Pro"
2. **Set Target Date Realistis**: Hitung berapa lama waktu yang dibutuhkan
3. **Update Regularly**: Tambahkan uang secara rutin (bulanan/mingguan)
4. **Gunakan Icon yang Menarik**: Visual yang bagus membuat lebih termotivasi
5. **Pause Jika Perlu**: Jika ada prioritas lain, pause dulu goal-nya

## Perbedaan dengan Budget

| Feature | Budget | Savings |
|---------|--------|---------|
| Tujuan | Kontrol pengeluaran | Target tabungan |
| Periode | Bulanan/Mingguan | Jangka panjang |
| Tracking | Spent vs Limit | Current vs Target |
| Action | Otomatis dari transaksi | Manual add/withdraw |
| Status | Active saja | Active/Completed/Paused |

## Contoh Skenario

### Skenario 1: Beli Mobil
1. Create goal "Beli Mobil Baru" dengan target Rp 200.000.000
2. Set target date 2 tahun dari sekarang
3. Setiap bulan add Rp 8.000.000
4. Track progress sampai 100%
5. Status otomatis jadi "Completed"

### Skenario 2: Dana Darurat
1. Create goal "Dana Darurat" dengan target 6x pengeluaran bulanan
2. Set icon 🛡️ dan warna hijau
3. Prioritaskan goal ini (add money lebih sering)
4. Jangan withdraw kecuali emergency
5. Maintain di status "Active" terus

## Testing

Untuk test fitur ini:
```bash
# Seed data sample
php artisan db:seed --class=SavingSeeder

# Akses halaman
http://localhost:8000/savings
```

User test: test@test.com / password123

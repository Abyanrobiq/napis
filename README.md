# 💰 Aplikasi Perencanaan Keuangan Pribadi

Aplikasi web untuk mengelola keuangan pribadi dengan fitur AI yang cerdas.

![Laravel](https://img.shields.io/badge/Laravel-12.x-red)
![PHP](https://img.shields.io/badge/PHP-8.2+-blue)
![License](https://img.shields.io/badge/License-MIT-green)

## 🌟 Fitur Utama

### 📊 Fitur Dasar
- ✅ **Pencatatan Transaksi Harian** - Catat income dan expense dengan mudah
- ✅ **Manajemen Budget** - Set budget per kategori dengan tracking otomatis
- ✅ **Savings Goals** - Buat dan track tujuan tabungan
- ✅ **Pelaporan Otomatis** - Laporan keuangan dengan export CSV

### 🤖 Fitur AI
- ✅ **Kategorisasi Cerdas** - AI auto-suggest kategori dari deskripsi
- ✅ **Analisis Pola** - Deteksi trend dan anomali pengeluaran
- ✅ **Rekomendasi Budget** - Saran budget adaptif berdasarkan history
- ✅ **Smart Reminders** - Pengingat cerdas untuk budget dan savings

## 🚀 Quick Start

### Prerequisites
- PHP 8.2 atau lebih tinggi
- Composer
- SQLite (atau database lain)

### Installation

1. **Clone repository**
```bash
git clone https://github.com/YOUR_USERNAME/financial-planner.git
cd financial-planner
```

2. **Install dependencies**
```bash
composer install
```

3. **Setup environment**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Setup database**
```bash
# Buat file database SQLite
touch database/database.sqlite

# Jalankan migration
php artisan migrate

# Seed data (opsional)
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=SavingSeeder
```

5. **Run server**
```bash
php artisan serve
```

6. **Akses aplikasi**
```
http://localhost:8000
```

## 👥 Kolaborasi

### Setup untuk Kolaborator

1. **Fork repository** (klik Fork di GitHub)

2. **Clone fork Anda**
```bash
git clone https://github.com/YOUR_USERNAME/financial-planner.git
cd financial-planner
```

3. **Add upstream remote**
```bash
git remote add upstream https://github.com/ORIGINAL_OWNER/financial-planner.git
```

4. **Install dan setup** (ikuti langkah Installation di atas)

### Workflow Kolaborasi

1. **Buat branch baru untuk fitur**
```bash
git checkout -b feature/nama-fitur
```

2. **Commit changes**
```bash
git add .
git commit -m "Add: deskripsi fitur"
```

3. **Push ke fork Anda**
```bash
git push origin feature/nama-fitur
```

4. **Buat Pull Request** di GitHub

5. **Sync dengan upstream**
```bash
git fetch upstream
git checkout main
git merge upstream/main
```

## 📖 Dokumentasi

- [PANDUAN.md](PANDUAN.md) - Panduan lengkap aplikasi
- [FITUR_SAVINGS.md](FITUR_SAVINGS.md) - Detail fitur savings
- [FITUR_AI_DAN_REPORTS.md](FITUR_AI_DAN_REPORTS.md) - Detail AI & reports
- [RINGKASAN_FITUR_LENGKAP.md](RINGKASAN_FITUR_LENGKAP.md) - Summary semua fitur

## 🧪 Testing

### Test User
```
Email: test@test.com
Password: password123
```

### Seed Data
```bash
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=SavingSeeder
```

## 📁 Struktur Project

```
├── app/
│   ├── Http/Controllers/
│   │   ├── AIController.php          # AI features
│   │   ├── ReportController.php      # Reports
│   │   ├── SavingController.php      # Savings goals
│   │   ├── BudgetController.php      # Budget management
│   │   └── TransactionController.php # Transactions
│   └── Models/
│       ├── Budget.php
│       ├── Category.php
│       ├── Saving.php
│       ├── Transaction.php
│       └── Setting.php
├── resources/views/
│   ├── ai/                           # AI feature views
│   ├── reports/                      # Report views
│   ├── savings/                      # Savings views
│   ├── budgets/                      # Budget views
│   └── transactions/                 # Transaction views
└── database/
    ├── migrations/
    └── seeders/
```

## 🛠️ Tech Stack

- **Backend**: Laravel 12
- **Database**: SQLite (default), MySQL/PostgreSQL compatible
- **Frontend**: Blade Templates + Tailwind CSS
- **AI**: Rule-based pattern recognition

## 🤝 Contributing

1. Fork repository
2. Buat branch fitur (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add: AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

### Commit Message Convention
```
Add: menambah fitur baru
Fix: memperbaiki bug
Update: update fitur existing
Remove: menghapus fitur
Docs: update dokumentasi
Style: perubahan styling
Refactor: refactoring code
```

## 📝 License

This project is licensed under the MIT License.

## 👨‍💻 Authors

- **Your Name** - *Initial work*

## 🙏 Acknowledgments

- Laravel Framework
- Tailwind CSS
- Filament Admin Panel

## 📞 Support

Jika ada pertanyaan atau issue, silakan buat [GitHub Issue](https://github.com/YOUR_USERNAME/financial-planner/issues).

---

⭐ Jangan lupa star repository ini jika bermanfaat!

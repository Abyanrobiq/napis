# Aplikasi Perencanaan Keuangan Pribadi

## Fitur Utama

### 🔐 Autentikasi
- **Registrasi** - Daftar akun baru dengan email dan password
- **Login** - Masuk dengan akun yang sudah terdaftar
- **Logout** - Keluar dari aplikasi dengan aman
- **Multi-user** - Setiap user memiliki data terpisah dan aman
- **Kategori Default** - Otomatis dibuat saat registrasi

### 📊 Dashboard
- Menampilkan saldo saat ini
- Pengaturan saldo awal
- Ringkasan budget per kategori dengan progress bar
- Daftar transaksi terakhir

### 🏷️ Kategori
- Buat kategori dengan nama, icon emoji, dan warna
- Edit dan hapus kategori
- Kategori default otomatis dibuat saat registrasi:
  - 🍔 Makanan & Minuman
  - 🚗 Transportasi
  - 🛒 Belanja
  - 🎮 Hiburan
  - 💳 Tagihan
  - 🏥 Kesehatan
  - 📚 Pendidikan
  - 💰 Gaji
  - 📦 Lainnya

### 💰 Budget
- Buat budget untuk setiap kategori
- Tentukan periode budget (tanggal mulai - selesai)
- Tracking otomatis pengeluaran vs budget
- Indikator visual sisa budget

### 📝 Transaksi
- Catat transaksi pemasukan atau pengeluaran
- Pilih kategori dan budget (opsional)
- Budget otomatis berkurang saat transaksi pengeluaran
- Edit dan hapus transaksi dengan update budget otomatis

### 🎯 Savings Goals
- Buat tujuan tabungan (e.g., Beli Mobil, Liburan, Gadget Baru)
- Set target jumlah dan tanggal
- Track progress dengan visual progress bar
- Add/Withdraw money dari savings
- Status: Active, Completed, Paused
- Custom icon dan warna untuk setiap goal

## Cara Menggunakan

### 1. Jalankan Server
```bash
php artisan serve
```

### 2. Akses Aplikasi
Buka browser: `http://localhost:8000`

### 3. Langkah Awal - User Baru
1. **Daftar Akun** - Klik "Daftar sekarang" di halaman login
2. **Isi Form Registrasi**:
   - Nama lengkap
   - Email (harus unik)
   - Password (minimal 8 karakter)
   - Konfirmasi password
3. **Otomatis Login** - Setelah registrasi berhasil
4. **Kategori Default** - Sudah dibuat otomatis untuk Anda
5. **Set Initial Balance** - Modal akan muncul untuk mengatur saldo awal

### 4. Langkah Awal - User Lama
1. **Login** dengan email dan password
2. **Dashboard** - Lihat ringkasan keuangan Anda
3. **Buat Budget** untuk kategori yang ingin dimonitor
4. **Catat Transaksi** - budget akan otomatis berkurang

## Tampilan Aplikasi

### Dashboard
- **Sidebar Navigation**: Home, Budget, Transaction, Savings
- **Top Bar**: Settings, Messages, Profile, Logout
- **Balance Cards**: Balance, Income, Expenses (dengan warna kuning)
- **Recent Transactions**: List transaksi terbaru dengan icon kategori
- **Active Budgets**: Progress bar budget yang sedang aktif
- **"No Data Yet"**: Tampil jika belum ada data

### Design Features
- ✨ Modern UI dengan rounded corners
- 🎨 Yellow gradient cards untuk balance/income/expenses
- 📊 Progress bars untuk tracking budget
- 🎯 Icon-based navigation
- 📱 Responsive design
- 🌈 Color-coded categories

## Alur Kerja Budget

1. User membuat budget untuk kategori tertentu (misal: Makanan Rp 1.000.000)
2. Saat user membuat transaksi pengeluaran dan memilih budget tersebut
3. Jumlah transaksi otomatis mengurangi budget yang tersedia
4. Dashboard menampilkan progress bar dan sisa budget
5. Jika budget habis, progress bar akan berwarna merah

## Struktur Database

- **categories**: Kategori transaksi
- **budgets**: Budget per kategori dengan periode
- **transactions**: Catatan transaksi (income/expense)
- **settings**: Pengaturan aplikasi (saldo awal, dll)

## Teknologi

- Laravel 12
- SQLite Database
- Tailwind CSS
- Blade Templates


## Keamanan & Privasi

- ✅ Setiap user memiliki data terpisah
- ✅ Password di-hash dengan bcrypt
- ✅ Session management yang aman
- ✅ CSRF protection
- ✅ Data tidak bisa diakses oleh user lain

## Alur Autentikasi

### Registrasi
1. User mengisi form registrasi
2. Validasi data (email unik, password min 8 karakter)
3. Password di-hash
4. User dibuat di database
5. Otomatis login
6. Kategori default dibuat untuk user
7. Redirect ke dashboard

### Login
1. User mengisi email dan password
2. Validasi credentials
3. Session dibuat
4. Redirect ke dashboard

### Logout
1. User klik tombol logout
2. Session dihapus
3. Redirect ke halaman login


## 📊 Fitur Pelaporan & AI

### Pelaporan Keuangan Otomatis
- **Financial Reports**: Laporan otomatis dengan filter period (week/month/year)
- **Summary Cards**: Total income, expense, dan net income
- **Visual Charts**: Breakdown per kategori dengan progress bar
- **Budget Performance**: Monitor semua budget aktif
- **Export CSV**: Download laporan untuk analisis lebih lanjut

### 🤖 AI Features

#### 1. Kategorisasi Transaksi Cerdas
- AI auto-suggest kategori saat input deskripsi transaksi
- Real-time suggestion dengan confidence score
- One-click apply untuk kemudahan
- Keyword-based pattern recognition

#### 2. Analisis Pola Pengeluaran
- Spending trend analysis (naik/turun vs bulan lalu)
- Category breakdown dengan detail
- Anomaly detection untuk transaksi tidak biasa
- AI insights dan recommendations

#### 3. Rekomendasi Anggaran Adaptif
- Smart budget calculation berdasarkan 3 bulan terakhir
- Automatic 10% buffer untuk fleksibilitas
- Status indicator (Create/Increase/Sufficient)
- Comparison current vs recommended budget

#### 4. Pengingat Cerdas
- Budget warnings (≥80% usage)
- Budget exceeded alerts (≥100%)
- Savings deadline reminders (30 hari sebelum)
- Unusual spending detection
- No transaction reminders
- Priority system (High/Medium/Low)

## Menu Navigasi

### Main Menu
- 🏠 **Home** - Dashboard utama
- 💰 **Budget** - Manajemen budget
- 📝 **Transaction** - Catatan transaksi
- 🎯 **Savings** - Tujuan tabungan
- 📊 **Reports** - Laporan keuangan

### AI Features Menu
- 🔔 **Smart Reminders** - Pengingat cerdas
- 📈 **AI Analysis** - Analisis pola pengeluaran
- 💡 **AI Recommendations** - Rekomendasi budget

## Workflow Lengkap

### Setup Awal
1. Register/Login
2. Set initial balance
3. Review kategori default (9 kategori)

### Penggunaan Harian
1. **Pagi**: Check Smart Reminders
2. **Siang**: Input transaksi (AI auto-suggest kategori)
3. **Malam**: Review dashboard

### Penggunaan Mingguan
1. Review AI Analysis
2. Check budget performance
3. Update savings goals

### Penggunaan Bulanan
1. Review Financial Reports
2. Follow AI Budget Recommendations
3. Adjust budgets untuk bulan depan
4. Export CSV untuk record

## Tips Maksimalkan Fitur AI

1. **Input Konsisten**: Catat semua transaksi untuk data akurat
2. **Deskripsi Jelas**: Gunakan kata kunci spesifik untuk AI suggestion
3. **Review Regular**: Check AI analysis dan reminders rutin
4. **Follow Recommendations**: Terapkan saran AI untuk hasil optimal
5. **Update Budget**: Sesuaikan budget berdasarkan AI recommendations

## Keunggulan Aplikasi

✅ **Otomatis**: AI yang membantu kategorisasi dan analisis
✅ **Cerdas**: Pattern recognition dan anomaly detection
✅ **Adaptif**: Rekomendasi yang menyesuaikan dengan kebiasaan
✅ **Proaktif**: Smart reminders untuk stay on track
✅ **Komprehensif**: Semua fitur terintegrasi dalam satu aplikasi

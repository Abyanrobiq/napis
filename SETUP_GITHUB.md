# 🚀 Setup GitHub - Panduan Lengkap

Panduan step-by-step untuk upload project ke GitHub dan kolaborasi dengan teman.

## 📋 Prerequisites

- [x] Git sudah terinstall (sudah ada: v2.49.0)
- [ ] Akun GitHub (buat di https://github.com jika belum punya)
- [ ] Project Laravel sudah jalan

## 🎯 Langkah 1: Buat Repository di GitHub

### A. Login ke GitHub
1. Buka https://github.com
2. Login dengan akun Anda

### B. Buat Repository Baru
1. Klik tombol **"+"** di kanan atas
2. Pilih **"New repository"**
3. Isi form:
   - **Repository name**: `financial-planner` (atau nama lain)
   - **Description**: `Aplikasi Perencanaan Keuangan Pribadi dengan AI`
   - **Visibility**: 
     - ✅ **Public** (jika ingin open source)
     - ⬜ **Private** (jika ingin private, tapi kolaborator perlu diinvite)
   - ⬜ **JANGAN** centang "Initialize with README" (kita sudah punya)
   - ⬜ **JANGAN** pilih .gitignore (kita sudah punya)
   - ⬜ **JANGAN** pilih license (bisa ditambah nanti)
4. Klik **"Create repository"**

### C. Copy URL Repository
Setelah dibuat, akan muncul URL seperti:
```
https://github.com/USERNAME/financial-planner.git
```
Copy URL ini!

## 🎯 Langkah 2: Initialize Git di Project

Buka terminal/command prompt di folder project, lalu jalankan:

```bash
# 1. Initialize git
git init

# 2. Add semua file
git add .

# 3. Commit pertama
git commit -m "Initial commit: Financial Planner App with AI features"

# 4. Rename branch ke main (jika masih master)
git branch -M main

# 5. Add remote origin (ganti URL dengan URL repository Anda)
git remote add origin https://github.com/USERNAME/financial-planner.git

# 6. Push ke GitHub
git push -u origin main
```

**Catatan:** Ganti `USERNAME` dengan username GitHub Anda!

## 🎯 Langkah 3: Verify Upload

1. Refresh halaman repository di GitHub
2. Semua file seharusnya sudah muncul
3. Check README.md tampil dengan baik

## 👥 Langkah 4: Invite Kolaborator

### A. Invite Teman sebagai Collaborator

1. Buka repository di GitHub
2. Klik tab **"Settings"**
3. Klik **"Collaborators"** di sidebar kiri
4. Klik **"Add people"**
5. Masukkan username/email GitHub teman Anda
6. Klik **"Add [username] to this repository"**
7. Teman Anda akan dapat email invitation

### B. Teman Accept Invitation

1. Teman buka email dari GitHub
2. Klik link invitation
3. Klik **"Accept invitation"**
4. Sekarang teman bisa clone dan push ke repository

## 🎯 Langkah 5: Clone untuk Kolaborator

Teman Anda jalankan:

```bash
# 1. Clone repository
git clone https://github.com/USERNAME/financial-planner.git
cd financial-planner

# 2. Install dependencies
composer install

# 3. Copy environment file
cp .env.example .env

# 4. Generate app key
php artisan key:generate

# 5. Create database
touch database/database.sqlite

# 6. Run migrations
php artisan migrate

# 7. Seed data
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=SavingSeeder

# 8. Run server
php artisan serve
```

## 🔄 Workflow Kolaborasi

### Untuk Anda (Owner)

#### Saat Mulai Coding
```bash
# 1. Pull changes terbaru
git pull origin main

# 2. Buat branch baru
git checkout -b feature/nama-fitur

# 3. Coding...

# 4. Commit
git add .
git commit -m "Add: deskripsi fitur"

# 5. Push
git push origin feature/nama-fitur

# 6. Buat Pull Request di GitHub (optional)
# Atau langsung merge ke main:
git checkout main
git merge feature/nama-fitur
git push origin main
```

### Untuk Teman (Collaborator)

#### Saat Mulai Coding
```bash
# 1. Pull changes terbaru
git pull origin main

# 2. Buat branch baru
git checkout -b feature/nama-fitur-teman

# 3. Coding...

# 4. Commit
git add .
git commit -m "Add: deskripsi fitur"

# 5. Push
git push origin feature/nama-fitur-teman

# 6. Buat Pull Request di GitHub
```

## 🔀 Merge Pull Request

### Di GitHub
1. Buka tab **"Pull requests"**
2. Klik PR yang ingin di-review
3. Review code changes
4. Jika OK, klik **"Merge pull request"**
5. Klik **"Confirm merge"**
6. Delete branch (optional)

### Di Local
```bash
# Update main branch
git checkout main
git pull origin main
```

## 🐛 Troubleshooting

### Error: Permission Denied
**Solusi:** Pastikan teman sudah accept invitation sebagai collaborator

### Error: Conflict saat Merge
**Solusi:**
```bash
# 1. Pull changes terbaru
git pull origin main

# 2. Resolve conflict di file yang conflict
# Edit file, hapus marker <<<<<<<, =======, >>>>>>>

# 3. Add dan commit
git add .
git commit -m "Fix: resolve merge conflict"

# 4. Push
git push origin main
```

### Error: Push Rejected
**Solusi:**
```bash
# Pull dulu, baru push
git pull origin main
git push origin main
```

## 📝 Best Practices

### 1. Commit Messages
```bash
# Good ✅
git commit -m "Add: AI category suggestion feature"
git commit -m "Fix: budget calculation error"
git commit -m "Update: dashboard UI improvements"

# Bad ❌
git commit -m "update"
git commit -m "fix bug"
git commit -m "changes"
```

### 2. Branch Naming
```bash
# Good ✅
feature/export-pdf
fix/budget-calculation
update/dashboard-ui

# Bad ❌
my-branch
test
branch1
```

### 3. Pull Before Push
```bash
# Selalu pull sebelum push
git pull origin main
git push origin main
```

### 4. Commit Often
```bash
# Commit kecil-kecil lebih baik
git commit -m "Add: export button"
git commit -m "Add: PDF generation logic"
git commit -m "Add: PDF styling"

# Daripada 1 commit besar
git commit -m "Add: complete PDF export feature"
```

## 🎓 Resources

- [GitHub Docs](https://docs.github.com/)
- [Git Cheat Sheet](https://education.github.com/git-cheat-sheet-education.pdf)
- [CONTRIBUTING.md](CONTRIBUTING.md) - Panduan kontribusi
- [GIT_COMMANDS.md](GIT_COMMANDS.md) - Git commands reference

## 📞 Need Help?

Jika ada masalah:
1. Check [GIT_COMMANDS.md](GIT_COMMANDS.md) untuk command reference
2. Google error message
3. Tanya di GitHub Issues
4. Tanya teman yang lebih expert

## ✅ Checklist

Setup GitHub:
- [ ] Buat repository di GitHub
- [ ] Initialize git di project
- [ ] Push ke GitHub
- [ ] Invite kolaborator
- [ ] Kolaborator clone project
- [ ] Test kolaborasi (commit & push)

Files yang sudah dibuat:
- [x] `.gitignore` - Ignore files
- [x] `README.md` - Project overview
- [x] `.env.example` - Environment template
- [x] `CONTRIBUTING.md` - Contribution guide
- [x] `GIT_COMMANDS.md` - Git commands reference
- [x] `SETUP_GITHUB.md` - Setup guide (file ini)

---

🎉 **Selamat! Project Anda sudah siap untuk kolaborasi!**

Happy coding! 🚀

# 🤝 Contributing Guide

Terima kasih ingin berkontribusi! Berikut panduan untuk kolaborasi.

## 🚀 Quick Start untuk Kolaborator

### 1. Fork & Clone

```bash
# Fork repository di GitHub (klik tombol Fork)

# Clone fork Anda
git clone https://github.com/YOUR_USERNAME/financial-planner.git
cd financial-planner

# Add upstream remote
git remote add upstream https://github.com/ORIGINAL_OWNER/financial-planner.git
```

### 2. Setup Project

```bash
# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Create database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed data (optional)
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=SavingSeeder

# Run server
php artisan serve
```

## 📝 Workflow

### 1. Sync dengan Upstream

Sebelum mulai coding, pastikan fork Anda up-to-date:

```bash
git fetch upstream
git checkout main
git merge upstream/main
git push origin main
```

### 2. Buat Branch Baru

```bash
# Format: feature/nama-fitur atau fix/nama-bug
git checkout -b feature/add-export-pdf
```

### 3. Coding

- Ikuti coding standards Laravel
- Tulis code yang clean dan readable
- Tambahkan comments jika perlu
- Test fitur Anda sebelum commit

### 4. Commit Changes

```bash
git add .
git commit -m "Add: export PDF feature"
```

**Commit Message Convention:**
- `Add:` untuk fitur baru
- `Fix:` untuk bug fix
- `Update:` untuk update fitur existing
- `Remove:` untuk menghapus fitur
- `Docs:` untuk dokumentasi
- `Style:` untuk styling
- `Refactor:` untuk refactoring

### 5. Push ke Fork

```bash
git push origin feature/add-export-pdf
```

### 6. Buat Pull Request

1. Buka repository fork Anda di GitHub
2. Klik "Pull Request"
3. Pilih branch yang baru dibuat
4. Isi deskripsi PR dengan jelas:
   - Apa yang diubah
   - Kenapa diubah
   - Screenshot (jika UI changes)
5. Submit PR

## 🎯 Area Kontribusi

### High Priority
- [ ] Export PDF untuk reports
- [ ] Email notifications
- [ ] Mobile responsive improvements
- [ ] Dark mode
- [ ] Multi-currency support

### Medium Priority
- [ ] Recurring transactions
- [ ] Budget templates
- [ ] Data backup/restore
- [ ] Import from CSV
- [ ] Charts & graphs

### Low Priority
- [ ] Social sharing
- [ ] Gamification
- [ ] Achievement badges
- [ ] Custom themes

## 📋 Coding Standards

### PHP/Laravel
- Follow PSR-12 coding standard
- Use type hints
- Write descriptive variable names
- Keep methods small and focused
- Use Eloquent relationships properly

### Blade Templates
- Use components when possible
- Keep logic minimal in views
- Use @include for reusable parts
- Follow consistent indentation

### JavaScript
- Use modern ES6+ syntax
- Keep scripts minimal
- Comment complex logic
- Use meaningful variable names

### CSS/Tailwind
- Use Tailwind utility classes
- Keep custom CSS minimal
- Follow mobile-first approach
- Use consistent spacing

## 🧪 Testing

Sebelum submit PR, pastikan:

- [ ] Code berjalan tanpa error
- [ ] Fitur baru sudah ditest manual
- [ ] Tidak ada breaking changes
- [ ] Database migrations berjalan lancar
- [ ] Dokumentasi sudah diupdate (jika perlu)

## 📚 Dokumentasi

Jika menambah fitur baru, update dokumentasi:

- `README.md` - Overview fitur
- `PANDUAN.md` - Panduan penggunaan
- Buat file dokumentasi terpisah jika perlu

## 🐛 Melaporkan Bug

Gunakan GitHub Issues dengan template:

```markdown
**Deskripsi Bug:**
[Jelaskan bug dengan jelas]

**Langkah Reproduksi:**
1. Buka halaman...
2. Klik tombol...
3. Error muncul...

**Expected Behavior:**
[Apa yang seharusnya terjadi]

**Screenshots:**
[Jika ada]

**Environment:**
- OS: Windows/Mac/Linux
- Browser: Chrome/Firefox/Safari
- PHP Version: 8.2
- Laravel Version: 12.x
```

## 💡 Request Fitur

Gunakan GitHub Issues dengan label "enhancement":

```markdown
**Fitur yang Diinginkan:**
[Jelaskan fitur]

**Kenapa Fitur Ini Penting:**
[Alasan]

**Contoh Penggunaan:**
[Skenario penggunaan]

**Alternatif:**
[Solusi alternatif yang sudah dipertimbangkan]
```

## ✅ PR Review Process

1. **Automated Checks** - GitHub Actions (jika ada)
2. **Code Review** - Maintainer akan review code
3. **Testing** - Test manual oleh reviewer
4. **Feedback** - Mungkin ada request changes
5. **Merge** - Setelah approved, PR akan di-merge

## 🎓 Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Git Workflow](https://www.atlassian.com/git/tutorials/comparing-workflows)
- [Conventional Commits](https://www.conventionalcommits.org/)

## 📞 Komunikasi

- **GitHub Issues** - Bug reports & feature requests
- **Pull Requests** - Code contributions
- **Discussions** - General questions

## 🙏 Thank You!

Setiap kontribusi sangat dihargai, baik itu:
- Code contributions
- Bug reports
- Feature suggestions
- Documentation improvements
- Testing & feedback

Happy coding! 🚀

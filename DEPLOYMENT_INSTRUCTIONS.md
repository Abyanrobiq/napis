# Deployment Instructions - Fix Settings Unique Constraint

## Problem
Error: `Duplicate entry 'initial_balance' for key 'settings_key_unique'`

## Solution
Jalankan perintah berikut di server production:

### 1. Pull Latest Code
```bash
git pull origin main
```

### 2. Run Migrations
```bash
php artisan migrate
```

### 3. Clear Cache (Optional)
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## What the Migration Does

1. **Cleanup Duplicate Data**: Menghapus duplicate entries di tabel settings
2. **Add user_id Column**: Menambahkan kolom user_id jika belum ada
3. **Fix Unique Constraint**: Mengubah unique constraint dari `key` saja menjadi `(key, user_id)`
4. **Assign Default user_id**: Memberikan user_id default untuk data lama

## Backup Recommendation

Sebelum menjalankan migration, disarankan untuk backup database:

```bash
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql
```

## Verification

Setelah migration berhasil, coba:
1. Login ke aplikasi
2. Set initial balance di dashboard
3. Pastikan tidak ada error lagi

## Rollback (Jika Diperlukan)

Jika ada masalah, rollback dengan:
```bash
php artisan migrate:rollback --step=1
```

## Files Changed

- `app/Models/Setting.php` - Added error handling
- `app/Http/Controllers/DashboardController.php` - Added try-catch
- `database/migrations/2025_12_10_170800_cleanup_settings_data.php` - New cleanup migration
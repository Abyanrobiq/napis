# 📚 Git Commands Cheat Sheet

Panduan lengkap Git commands untuk kolaborasi project ini.

## 🚀 Setup Awal

### Clone Repository
```bash
# Clone dari GitHub
git clone https://github.com/USERNAME/financial-planner.git
cd financial-planner
```

### Setup Remote
```bash
# Lihat remote yang ada
git remote -v

# Tambah upstream (repository asli)
git remote add upstream https://github.com/ORIGINAL_OWNER/financial-planner.git

# Verify
git remote -v
# Output:
# origin    https://github.com/YOUR_USERNAME/financial-planner.git (fetch)
# origin    https://github.com/YOUR_USERNAME/financial-planner.git (push)
# upstream  https://github.com/ORIGINAL_OWNER/financial-planner.git (fetch)
# upstream  https://github.com/ORIGINAL_OWNER/financial-planner.git (push)
```

## 🔄 Sync dengan Upstream

### Update Fork dari Upstream
```bash
# Fetch changes dari upstream
git fetch upstream

# Pindah ke branch main
git checkout main

# Merge changes dari upstream
git merge upstream/main

# Push ke fork Anda
git push origin main
```

### Shortcut (jika sudah setup)
```bash
git pull upstream main
git push origin main
```

## 🌿 Branch Management

### Buat Branch Baru
```bash
# Buat dan pindah ke branch baru
git checkout -b feature/nama-fitur

# Atau pisah command
git branch feature/nama-fitur
git checkout feature/nama-fitur
```

### Lihat Branch
```bash
# Lihat semua branch
git branch -a

# Lihat branch saat ini
git branch
```

### Pindah Branch
```bash
git checkout main
git checkout feature/nama-fitur
```

### Hapus Branch
```bash
# Hapus branch lokal
git branch -d feature/nama-fitur

# Force delete
git branch -D feature/nama-fitur

# Hapus branch remote
git push origin --delete feature/nama-fitur
```

## 💾 Commit Changes

### Check Status
```bash
# Lihat file yang berubah
git status

# Lihat perubahan detail
git diff

# Lihat perubahan file tertentu
git diff app/Http/Controllers/AIController.php
```

### Stage Changes
```bash
# Add semua file
git add .

# Add file tertentu
git add app/Http/Controllers/AIController.php

# Add folder tertentu
git add resources/views/ai/

# Add dengan pattern
git add *.php
```

### Commit
```bash
# Commit dengan message
git commit -m "Add: AI category suggestion feature"

# Commit dengan message multi-line
git commit -m "Add: AI category suggestion feature" -m "- Real-time suggestion
- Confidence score
- One-click apply"

# Amend commit terakhir
git commit --amend -m "Add: AI category suggestion feature (fixed typo)"
```

### Unstage Changes
```bash
# Unstage file
git reset HEAD app/Http/Controllers/AIController.php

# Unstage semua
git reset HEAD
```

### Discard Changes
```bash
# Discard changes di file
git checkout -- app/Http/Controllers/AIController.php

# Discard semua changes
git checkout -- .
```

## 📤 Push & Pull

### Push
```bash
# Push ke origin (fork Anda)
git push origin feature/nama-fitur

# Push pertama kali (set upstream)
git push -u origin feature/nama-fitur

# Force push (hati-hati!)
git push -f origin feature/nama-fitur
```

### Pull
```bash
# Pull dari origin
git pull origin main

# Pull dari upstream
git pull upstream main
```

## 🔀 Merge & Rebase

### Merge Branch
```bash
# Pindah ke branch tujuan
git checkout main

# Merge branch lain
git merge feature/nama-fitur
```

### Rebase
```bash
# Rebase branch saat ini dengan main
git rebase main

# Continue setelah resolve conflict
git rebase --continue

# Abort rebase
git rebase --abort
```

## 🔍 History & Log

### View Log
```bash
# Log lengkap
git log

# Log compact
git log --oneline

# Log dengan graph
git log --graph --oneline --all

# Log file tertentu
git log app/Http/Controllers/AIController.php

# Log dengan limit
git log -5
```

### View Commit Detail
```bash
# Show commit tertentu
git show COMMIT_HASH

# Show file di commit tertentu
git show COMMIT_HASH:app/Http/Controllers/AIController.php
```

## 🔧 Stash (Simpan Sementara)

### Save Changes
```bash
# Stash changes
git stash

# Stash dengan message
git stash save "WIP: working on AI feature"

# Stash include untracked files
git stash -u
```

### Apply Stash
```bash
# List stash
git stash list

# Apply stash terakhir
git stash apply

# Apply stash tertentu
git stash apply stash@{0}

# Apply dan hapus stash
git stash pop
```

### Delete Stash
```bash
# Hapus stash tertentu
git stash drop stash@{0}

# Hapus semua stash
git stash clear
```

## 🐛 Troubleshooting

### Conflict Resolution
```bash
# Saat ada conflict, edit file yang conflict
# Cari marker: <<<<<<<, =======, >>>>>>>

# Setelah resolve, add file
git add .

# Continue merge/rebase
git merge --continue
# atau
git rebase --continue
```

### Undo Commit
```bash
# Undo commit terakhir (keep changes)
git reset --soft HEAD~1

# Undo commit terakhir (discard changes)
git reset --hard HEAD~1

# Undo commit tertentu
git reset --hard COMMIT_HASH
```

### Revert Commit
```bash
# Revert commit (buat commit baru)
git revert COMMIT_HASH
```

## 📋 Useful Aliases

Tambahkan ke `~/.gitconfig`:

```ini
[alias]
    st = status
    co = checkout
    br = branch
    ci = commit
    cm = commit -m
    ca = commit --amend
    df = diff
    lg = log --graph --oneline --all
    last = log -1 HEAD
    unstage = reset HEAD --
    undo = reset --soft HEAD~1
```

Penggunaan:
```bash
git st          # git status
git co main     # git checkout main
git cm "message" # git commit -m "message"
git lg          # git log --graph --oneline --all
```

## 🎯 Workflow Lengkap

### Mulai Fitur Baru
```bash
# 1. Sync dengan upstream
git checkout main
git pull upstream main
git push origin main

# 2. Buat branch baru
git checkout -b feature/export-pdf

# 3. Coding...

# 4. Commit changes
git add .
git commit -m "Add: PDF export feature"

# 5. Push ke fork
git push -u origin feature/export-pdf

# 6. Buat Pull Request di GitHub
```

### Update Branch dengan Main
```bash
# Jika main sudah update, sync branch Anda
git checkout feature/export-pdf
git fetch upstream
git rebase upstream/main
git push -f origin feature/export-pdf
```

## 🔐 Git Configuration

### Setup User
```bash
# Set username
git config --global user.name "Your Name"

# Set email
git config --global user.email "your.email@example.com"

# Check config
git config --list
```

### Setup Editor
```bash
# Set default editor
git config --global core.editor "code --wait"  # VS Code
git config --global core.editor "vim"          # Vim
```

## 📚 Resources

- [Git Documentation](https://git-scm.com/doc)
- [GitHub Guides](https://guides.github.com/)
- [Git Cheat Sheet](https://education.github.com/git-cheat-sheet-education.pdf)
- [Atlassian Git Tutorial](https://www.atlassian.com/git/tutorials)

## 💡 Tips

1. **Commit Often**: Commit kecil-kecil lebih baik dari commit besar
2. **Clear Messages**: Tulis commit message yang jelas
3. **Pull Before Push**: Selalu pull sebelum push
4. **Branch per Feature**: Satu branch untuk satu fitur
5. **Review Before Commit**: Check `git diff` sebelum commit
6. **Backup**: Push ke remote secara regular

---

Happy Git-ing! 🚀

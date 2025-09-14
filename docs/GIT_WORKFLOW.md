# Git Workflow dan Strategi Branching - Q-POS Application

## Daftar Isi
1. [Strategi Branching](#strategi-branching)
2. [Panduan Versioning dengan Tags](#panduan-versioning-dengan-tags)
3. [Alur Kerja Pengembangan](#alur-kerja-pengembangan)
4. [Best Practices untuk Kolaborasi Tim](#best-practices-untuk-kolaborasi-tim)

---

## Strategi Branching

### Branch Utama

#### 1. `main` (Production Branch)
- **Purpose**: Branch utama yang berisi kode production-ready
- **Karakteristik**:
  - Selalu dalam kondisi stabil dan dapat di-deploy
  - Hanya menerima merge dari `release/*` atau `hotfix/*`
  - Setiap commit di-tag dengan versi release
  - Protected branch dengan review requirement

#### 2. `dev` (Development Branch)
- **Purpose**: Branch pengembangan utama untuk integrasi fitur
- **Karakteristik**:
  - Base branch untuk semua feature development
  - Tempat integrasi dan testing fitur-fitur baru
  - Dapat berisi kode yang belum stabil
  - Merge target untuk semua `feature/*` branches

### Branch Pendukung

#### 3. `feature/*` (Feature Branches)
- **Purpose**: Pengembangan fitur baru atau enhancement
- **Konvensi Penamaan**:
  ```
  feature/user-authentication
  feature/pos-transaction
  feature/inventory-management
  feature/reporting-dashboard
  ```
- **Lifecycle**:
  - Dibuat dari: `dev`
  - Merge ke: `dev`
  - Dihapus setelah merge

#### 4. `release/*` (Release Branches)
- **Purpose**: Persiapan release dan bug fixing minor
- **Konvensi Penamaan**:
  ```
  release/v1.0.0
  release/v1.1.0
  release/v2.0.0-beta
  ```
- **Lifecycle**:
  - Dibuat dari: `dev`
  - Merge ke: `main` dan `dev`
  - Dihapus setelah release

#### 5. `hotfix/*` (Hotfix Branches)
- **Purpose**: Perbaikan critical bug di production
- **Konvensi Penamaan**:
  ```
  hotfix/critical-security-patch
  hotfix/payment-gateway-fix
  hotfix/v1.0.1
  ```
- **Lifecycle**:
  - Dibuat dari: `main`
  - Merge ke: `main` dan `dev`
  - Dihapus setelah merge

#### 6. `bugfix/*` (Bug Fix Branches)
- **Purpose**: Perbaikan bug non-critical
- **Konvensi Penamaan**:
  ```
  bugfix/login-validation
  bugfix/inventory-calculation
  bugfix/ui-responsive-issue
  ```
- **Lifecycle**:
  - Dibuat dari: `dev`
  - Merge ke: `dev`
  - Dihapus setelah merge

---

## Panduan Versioning dengan Tags

### Semantic Versioning (SemVer)
Menggunakan format: `MAJOR.MINOR.PATCH`

#### Struktur Versi
```
v1.0.0
│ │ │
│ │ └── PATCH: Bug fixes, hotfixes
│ └──── MINOR: New features, backwards compatible
└────── MAJOR: Breaking changes, major updates
```

#### Contoh Versioning
```bash
# Release awal
v1.0.0

# Penambahan fitur baru (backwards compatible)
v1.1.0 - Menambah fitur laporan penjualan
v1.2.0 - Menambah fitur manajemen supplier

# Bug fixes
v1.2.1 - Perbaikan bug kalkulasi diskon
v1.2.2 - Perbaikan UI responsive

# Major update dengan breaking changes
v2.0.0 - Refactor API, update database schema
```

#### Pre-release Tags
```bash
v1.0.0-alpha.1    # Alpha release
v1.0.0-beta.1     # Beta release
v1.0.0-rc.1       # Release candidate
```

#### Perintah Git untuk Tagging
```bash
# Membuat tag
git tag -a v1.0.0 -m "Release version 1.0.0 - Initial Q-POS release"

# Push tag ke remote
git push origin v1.0.0

# Push semua tags
git push origin --tags

# Melihat semua tags
git tag -l

# Menghapus tag
git tag -d v1.0.0
git push origin --delete v1.0.0
```

---

## Alur Kerja Pengembangan

### 1. Pengembangan Fitur Baru

```bash
# 1. Update dev branch
git checkout dev
git pull origin dev

# 2. Buat feature branch
git checkout -b feature/pos-transaction

# 3. Develop dan commit
git add .
git commit -m "feat: implement POS transaction module"

# 4. Push feature branch
git push -u origin feature/pos-transaction

# 5. Buat Pull Request ke dev
# 6. Code review dan merge
# 7. Hapus feature branch setelah merge
git branch -d feature/pos-transaction
git push origin --delete feature/pos-transaction
```

### 2. Proses Release

```bash
# 1. Buat release branch dari dev
git checkout dev
git pull origin dev
git checkout -b release/v1.1.0

# 2. Update version numbers, changelog
git add .
git commit -m "chore: prepare release v1.1.0"

# 3. Testing dan bug fixes
git commit -m "fix: resolve minor UI issues for release"

# 4. Merge ke main
git checkout main
git merge --no-ff release/v1.1.0

# 5. Tag release
git tag -a v1.1.0 -m "Release v1.1.0"

# 6. Merge back ke dev
git checkout dev
git merge --no-ff release/v1.1.0

# 7. Push dan cleanup
git push origin main
git push origin dev
git push origin v1.1.0
git branch -d release/v1.1.0
```

### 3. Hotfix Process

```bash
# 1. Buat hotfix branch dari main
git checkout main
git pull origin main
git checkout -b hotfix/critical-payment-fix

# 2. Fix critical issue
git add .
git commit -m "hotfix: resolve payment gateway timeout issue"

# 3. Merge ke main
git checkout main
git merge --no-ff hotfix/critical-payment-fix

# 4. Tag hotfix
git tag -a v1.0.1 -m "Hotfix v1.0.1 - Payment gateway fix"

# 5. Merge ke dev
git checkout dev
git merge --no-ff hotfix/critical-payment-fix

# 6. Push dan cleanup
git push origin main
git push origin dev
git push origin v1.0.1
git branch -d hotfix/critical-payment-fix
```

### 4. Skenario Pengembangan Nyata

#### Skenario A: Pengembangan Fitur Inventory Management
```bash
# Developer A mengembangkan fitur inventory
git checkout dev
git pull origin dev
git checkout -b feature/inventory-management

# Implementasi fitur
git commit -m "feat: add product CRUD operations"
git commit -m "feat: implement stock tracking"
git commit -m "feat: add low stock alerts"

# Push dan buat PR
git push -u origin feature/inventory-management
# Create Pull Request to dev branch
```

#### Skenario B: Bug Fix pada Fitur yang Sudah Ada
```bash
# Developer B memperbaiki bug di fitur login
git checkout dev
git pull origin dev
git checkout -b bugfix/login-validation

# Perbaikan bug
git commit -m "fix: improve email validation in login form"
git commit -m "fix: handle empty password field properly"

# Push dan merge
git push -u origin bugfix/login-validation
# Create Pull Request to dev branch
```

---

## Best Practices untuk Kolaborasi Tim

### 1. Commit Message Conventions

Menggunakan [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

#### Types:
- `feat`: Fitur baru
- `fix`: Bug fix
- `docs`: Perubahan dokumentasi
- `style`: Perubahan formatting, missing semicolons, etc
- `refactor`: Code refactoring
- `test`: Menambah atau memperbaiki tests
- `chore`: Maintenance tasks

#### Contoh:
```bash
feat(auth): implement JWT token refresh mechanism

fix(pos): resolve calculation error in discount application

docs(api): update authentication endpoint documentation

refactor(database): optimize query performance for reports

test(inventory): add unit tests for stock management

chore(deps): update Laravel to version 10.x
```

### 2. Pull Request Guidelines

#### Template PR Description:
```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Unit tests pass
- [ ] Integration tests pass
- [ ] Manual testing completed

## Checklist
- [ ] Code follows project style guidelines
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] No breaking changes (or documented)
```

#### Review Process:
1. **Self Review**: Developer melakukan review sendiri
2. **Peer Review**: Minimal 1 reviewer untuk feature, 2 untuk critical changes
3. **Testing**: Automated tests harus pass
4. **Documentation**: Update dokumentasi jika diperlukan

### 3. Branch Protection Rules

#### Main Branch:
- Require pull request reviews (minimum 2)
- Require status checks to pass
- Require branches to be up to date
- Restrict pushes to admins only
- Require signed commits

#### Dev Branch:
- Require pull request reviews (minimum 1)
- Require status checks to pass
- Allow force pushes for maintainers

### 4. Workflow Automation

#### GitHub Actions / CI/CD:
```yaml
# .github/workflows/ci.yml
name: CI/CD Pipeline

on:
  push:
    branches: [ main, dev ]
  pull_request:
    branches: [ main, dev ]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: php artisan test
```

### 5. Code Quality Standards

#### Pre-commit Hooks:
```bash
# Install pre-commit hooks
npm install -g @commitlint/cli @commitlint/config-conventional

# Setup commitlint
echo "module.exports = {extends: ['@commitlint/config-conventional']}" > commitlint.config.js
```

#### Code Formatting:
- **Backend (Laravel)**: PHP CS Fixer, PHPStan
- **Frontend (Quasar/Vue)**: ESLint, Prettier
- **Consistent**: EditorConfig untuk semua file

### 6. Communication Guidelines

#### Daily Standup:
- Apa yang dikerjakan kemarin?
- Apa yang akan dikerjakan hari ini?
- Ada blocker atau kendala?
- Status branch dan PR yang sedang berjalan

#### Documentation:
- Update README.md untuk perubahan setup
- Dokumentasi API untuk endpoint baru
- Changelog untuk setiap release
- Architecture Decision Records (ADR) untuk keputusan teknis

#### Issue Tracking:
- Gunakan GitHub Issues atau Jira
- Label yang konsisten (bug, enhancement, documentation)
- Milestone untuk tracking release
- Assignment yang jelas

### 7. Emergency Procedures

#### Rollback Process:
```bash
# Rollback ke versi sebelumnya
git checkout main
git revert <commit-hash>
git push origin main

# Atau rollback ke tag tertentu
git checkout v1.0.0
git checkout -b hotfix/rollback-v1.0.1
git push -u origin hotfix/rollback-v1.0.1
```

#### Critical Bug Response:
1. **Immediate**: Buat hotfix branch dari main
2. **Fix**: Implement minimal fix
3. **Test**: Quick testing di staging
4. **Deploy**: Merge ke main dan deploy
5. **Follow-up**: Merge ke dev dan buat proper fix

---

## Kesimpulan

Workflow ini dirancang untuk:
- **Stabilitas**: Main branch selalu production-ready
- **Fleksibilitas**: Multiple feature development parallel
- **Kualitas**: Code review dan testing requirements
- **Traceability**: Clear commit history dan versioning
- **Collaboration**: Clear guidelines untuk tim

Pastikan semua anggota tim memahami dan mengikuti workflow ini untuk menjaga kualitas dan konsistensi pengembangan aplikasi Q-POS.
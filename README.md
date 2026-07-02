# SIOPAL UDINUS — Sistem Informasi Operasional Laboratorium

Aplikasi manajemen laboratorium komputer berbasis web, dibangun dengan **Laravel 12** dan **Filament 3**.

---

## Tech Stack

- PHP 8.2+
- Laravel 12
- Filament 3 (admin panel)
- MySQL
- Composer
- Node.js & NPM (untuk asset build)

---

## Prerequisites

Pastikan sudah terinstall di mesin kamu:

| Tool | Versi Minimum |
|------|--------------|
| PHP | 8.2 |
| Composer | 2.x |
| Node.js | 18.x |
| NPM | 9.x |
| MySQL | 5.7 / 8.0 |
| Git | any |

> Rekomendasi: gunakan [Laragon](https://laragon.org/) (Windows) atau [Herd](https://herd.laravel.com/) sebagai local dev environment.

---

## Clone Project (Fresh Setup)

Gunakan langkah ini jika kamu **belum punya** salinan project di lokal.

### 1. Clone Repository

```bash
git clone https://github.com/USERNAME/REPO_NAME.git
cd REPO_NAME
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node Dependencies

```bash
npm install
```

### 4. Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Buka file `.env` dan sesuaikan konfigurasi database:

```env
APP_NAME=SIOPAL
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=siopal_udinus2
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Buat Database

Buat database di MySQL dengan nama yang sama seperti `DB_DATABASE` di `.env`:

```sql
CREATE DATABASE siopal_udinus2;
```

### 6. Jalankan Migrasi & Seeder

```bash
php artisan migrate
php artisan db:seed
```

### 7. Build Assets

```bash
npm run build
```

### 8. Jalankan Aplikasi

```bash
php artisan serve
```

Akses aplikasi di: **http://localhost:8000/admin**

---

## Git Pull (Update Project yang Sudah Ada)

Gunakan langkah ini jika kamu **sudah punya** project di lokal dan ingin mengambil perubahan terbaru.

### 1. Pastikan Tidak Ada Perubahan Lokal yang Belum Disimpan

```bash
git status
```

Jika ada perubahan yang belum ingin di-commit, simpan dulu dengan stash:

```bash
git stash
```

### 2. Pull Perubahan Terbaru

```bash
git pull origin main
```

> Ganti `main` dengan nama branch yang kamu gunakan jika berbeda (misalnya `master` atau `develop`).

### 3. Update PHP Dependencies

Jalankan ini jika ada perubahan di `composer.json`:

```bash
composer install
```

### 4. Update Node Dependencies

Jalankan ini jika ada perubahan di `package.json`:

```bash
npm install
```

### 5. Jalankan Migrasi Terbaru

```bash
php artisan migrate
```

### 6. Clear Cache

```bash
php artisan optimize:clear
```

### 7. Build Ulang Assets

```bash
npm run build
```

### 8. (Opsional) Kembalikan Stash

Jika tadi menyimpan perubahan lokal:

```bash
git stash pop
```

---

## Struktur Role

| Role | Akses |
|------|-------|
| `super_admin` | Akses penuh ke semua fitur |
| `Laboran_X` | Akses terbatas — dashboard, jadwal, rekap inventaris lab X, pelaporan PTPP |

> `X` adalah kode lab, contoh: `Laboran_A`, `Laboran_D2K`

---

## Perintah Berguna

```bash
# Jalankan server development
php artisan serve

# Lihat semua route
php artisan route:list

# Reset & seed ulang database
php artisan migrate:fresh --seed

# Clear semua cache
php artisan optimize:clear

# Buat shield permissions (Filament)
php artisan shield:generate --all
```

---

## Troubleshooting

**Error: `SQLSTATE[HY000] [1049] Unknown database`**
→ Pastikan database sudah dibuat dan nama di `.env` sesuai.

**Error: `php artisan key:generate` tidak bisa dijalankan**
→ Pastikan file `.env` sudah ada (`cp .env.example .env`).

**Halaman `/admin` menampilkan 404**
→ Jalankan `php artisan route:clear` lalu coba lagi.

**Asset tidak muncul (CSS/JS kosong)**
→ Jalankan `npm run build` atau `npm run dev` untuk development.

**Error setelah `git pull`**
→ Coba jalankan `composer install` dan `php artisan migrate` ulang.

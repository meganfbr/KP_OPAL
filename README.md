# SIOPAL - Sistem Informasi Operasional Laboratorium

Aplikasi Sistem Informasi Manajemen Laboratorium (SIOPAL) berbasis Laravel dan Filament. Repository ini berisi kodingan sistem secara penuh beserta seeder otomatis (mengambil data real hardware & laboran dari Excel) guna memudahkan proses instalasi awal.

## 🚀 Instalasi untuk Developer (Cara Cloning Project)

Ikuti langkah-langkah di bawah ini jika kamu baru pertama kali melakukan clone pada repository ini.

### 1. Clone Repository & Masuk ke Folder Project
```bash
git clone [URL_REPOSITORI_SIOPAL]
cd KP-NEW
```

### 2. Instalasi Dependensi Backend (PHP / Laravel)
Pastikan kamu menggunakan PHP versi 8.2 ke atas dan telah menginstal Composer.
```bash
composer install
```
*(Proses ini akan mengunduh vendor-vendor laravel yang dibutuhkan).*

### 3. Instalasi Dependensi Frontend (Node.js / NPM)
Pastikan kamu telah menginstal Node.js.
```bash
npm install
npm run build
```
*(Untuk keperluan kompilasi aset Laravel Vite).*

### 4. Konfigurasi Environment & Database
Salin file konfigurasi *.env.example* menjadi *.env*.
```bash
cp .env.example .env
```
Setelah itu, buat application key:
```bash
php artisan key:generate
```
Buka file `.env` kamu di editor (VS Code dll), lalu sesuaikan konfigurasi koneksi database:
```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database_kamu
DB_USERNAME=root
DB_PASSWORD=
```
*Pastikan kamu sudah membuat database kosong (contoh: `nama_database_kamu`) melalui phpMyAdmin, HeidiSQL, atau DBeaver di lokal kamu.*

### 5. Proses Auto-Seeding (Migrasi Tabel beserta Data Excel)
Jalankan perintah ajaib ini untuk menyusun seluruh database beserta data master dari *seeders*, termasuk import data PC (560 PC Real) & data Laboran dari Excel:
```bash
php artisan migrate:fresh --seed
```
📝 **Catatan Penting saat Menjalankan `migrate:fresh --seed`:**
- Proses ini aman karena *script* Python secara pintar membaca isi _spreadsheet Excel_ kamu dari direktori `database/imports/`.
- Jangan menghapus file `Data_Laboran_2026-06-30.xlsx` maupun `template_import_inventaris_pc_siopal_560.xlsx` di keranjang folder penyimpanan tersebut. Jika kebetulan terhapus, seeder akan melewatinya begitu saja (tanpa layar *Error Red* panjang).

### 6. Jalankan Aplikasi
Jalankan development server Laravel:
```bash
php artisan serve
```
Akses aplikasi melalui *browser* pada halaman admin:
👉 `http://127.0.0.1:8000/admin`

---

## 👨‍💻 Info Akun / Kredensial Login
Akun dengan Level **Super Admin** yang *include* secara otomatis saat seeding:
- **Email:** `admin@mail.com`
- **Password:** `password`

Sedangkan untuk akun tiap **Laboran** *(default hasil tarikan data import)*:
- **Password:** `password123`

---

## 🔄 Pemeliharaan Update (Cara Melakukan Git Pull Terstruktur)

Jika kamu ingin melakukan tarikan kode dari *branch origin* ketika ada rilis pembaruan baru atau mengambil pekerjaan temanmu, gunakan alur ini:

```bash
# 1. Tarik pembaruan repo
git pull origin main

# 2. Perbarui dependency jika sewaktu-waktu bertambah
composer install
npm install
npm run build

# 3. Jalankan migrasi agar skema tabel tersinkronisasi 
# (Gunakan migrate apabila TIDAK ingin menghapus datamu, HANYA men-update struktur tabel)
php artisan migrate
```
Jika sistem koding terbaru membutuhkan data reset besar-besaran (contoh: *fresh installation*):
```bash
php artisan migrate:fresh --seed
```

### Lingkungan Requirement
*   PHP ^8.2
*   Node.js ^18.x / ^20.x
*   MySQL 8+ atau MariaDB 10+
*   Ekstensi PHP (WAJIB ENABLE): `pdo_mysql`, `mbstring`, `gd`, `intl`, `bcmath`, `zip`.
*   Python ^3.x (Diperlukan oleh sistem seeder untuk melakukan konversi data file master `.xlsx`).

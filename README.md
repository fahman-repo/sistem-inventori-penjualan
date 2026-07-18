# Sistem Inventori & Penjualan (Mini POS)

Aplikasi web sederhana untuk mengelola stok barang, transaksi pembelian, dan transaksi penjualan pada toko/usaha kecil. Dibangun sebagai proyek belajar "vibe coding" menggunakan **Claude Code**.

## 🚀 Tech Stack

| Teknologi | Versi |
|-----------|-------|
| Laravel | 11 |
| PHP | 8.2+ |
| Database | MySQL (XAMPP) |
| Template | Blade + AdminLTE 3 (Bootstrap 4) |
| Autentikasi | Laravel Breeze |
| Laporan | Chart.js |
| PDF | barryvdh/laravel-dompdf (opsional) |

## 📦 Fitur Utama

### Role Pengguna
- **Admin**: Akses penuh ke semua modul (master data, pembelian, penjualan, laporan, kelola user)
- **Kasir**: Hanya modul penjualan (buat transaksi baru, lihat riwayat penjualan)

### Modul
1. **Autentikasi** - Login & logout dengan role-based access
2. **Master Data Kategori** - CRUD kategori barang
3. **Master Data Produk** - CRUD produk dengan stok, SKU, harga beli/jual
4. **Transaksi Pembelian** - Stok masuk otomatis
5. **Transaksi Penjualan** - Stok keluar otomatis, validasi stok
6. **Laporan** - Stok, penjualan, laba kotor, grafik Chart.js
7. **Dashboard** - Ringkasan stok & penjualan harian

## 🛠️ Instalasi

### Prasyarat
- PHP 8.2+ dengan ekstensi: `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `json`, `ctype`, `bcrypt`
- Composer
- MySQL (via XAMPP)
- Node.js & npm (opsional untuk frontend)

### Langkah Instalasi

```bash
# 1. Clone/unzip proyek ke direktori XAMPP
cd C:\xampp\htdocs\laravel_claude

# 2. Install dependency PHP
composer install

# 3. Copy file environment
cp .env.example .env
# atau buat .env manual dengan konfigurasi database:
# DB_DATABASE=pos_db
# DB_USERNAME=root
# DB_PASSWORD=

# 4. Generate key aplikasi
php artisan key:generate

# 5. Install & setup AdminLTE 3
composer require jeroennoten/laravel-adminlte
php artisan adminlte:install
php artisan adminlte:install --only=auth_views

# 6. Setup database
php artisan migrate:fresh --seed

# 7. (Opsional) Install dependency npm untuk bower/adminlte
npm install
npm run dev
```

## ▶️ Menjalankan Aplikasi

```bash
# Jalankan server development Laravel
php artisan serve

# Buka di browser: http://localhost:8000
```

## 🔐 Akun Default (Setelah Seeding)

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@example.com | password |
| Kasir | kasir@example.com | password |

## 📁 Struktur Direktori

```
app/
├── Models/           # Model Eloquent
├── Http/
│   ├── Controllers/  # Resource Controllers
│   ├── Requests/     # Form Request classes
│   └── Middleware/  # Role check middleware
database/
├── migrations/       # Schema database
└── seeders/          # Data dummy
resources/
├── views/
│   ├── products/     # View modul produk
│   ├── sales/        # View modul penjualan
│   ├── purchases/    # View modul pembelian
│   ├── categories/   # View modul kategori
│   └── reports/      # View modul laporan
routes/
└── web.php           # Route web
```

## 📐 Konvensi Kode

- **Model**: PascalCase, singular (misal: `Product`)
- **Variabel/Method**: camelCase (misal: `$totalStock`)
- **Kolom Database**: snake_case (misal: `product_name`)
- **Validasi**: Menggunakan Form Request class
- **Transaksi**: Menggunakan `DB::transaction()` untuk operasi multi-tabel

## 🏪 Aturan Bisnis

1. Stok produk tidak boleh minus
2. Transaksi pembelian menambah stok otomatis
3. Transaksi penjualan mengurangi stok otomatis
4. Harga disimpan pada saat transaksi (tidak realtime)
5. Nomor invoice unik: `INV-YYYYMMDD-XXXX`

## 📚 Dokumentasi Tambahan

- [PRD.md](PRD.md) - Product Requirement Document
- [CLAUDE.md](CLAUDE.md) - Instruksi pengembangan untuk Claude Code
- [SCHEMA.md](SCHEMA.md) - Skema database (baca sebelum buat migration)

## 🤝 License

Proyek pembelajaran untuk menguakai kemampuan Claude Code.
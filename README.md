# Sistem Inventori & Penjualan (Mini POS)

Aplikasi web untuk mengelola stok barang, transaksi pembelian, dan transaksi penjualan pada toko/usaha kecil. Dilengkapi dengan **Activity Log** (audit trail) dan **Stock Opname** (pencocokan stok fisik). Dibangun sebagai proyek belajar "vibe coding" menggunakan **Claude Code**.

## 🚀 Tech Stack

| Teknologi | Versi |
|-----------|-------|
| Laravel | 11 |
| PHP | 8.2+ |
| Database | MySQL (XAMPP) |
| Template | Blade + AdminLTE 3 (Bootstrap 4) |
| Autentikasi | Laravel Breeze (tampilan disesuaikan ke AdminLTE) |
| Grafik | Chart.js (bawaan AdminLTE 3) |
| PDF | barryvdh/laravel-dompdf |
| Excel | maatwebsite/laravel-excel |

## 📦 Fitur Lengkap

### Fase 1 — Transaksi Stok & Penjualan

| Modul | Fitur | Role |
|-------|-------|------|
| **Autentikasi** | Login & logout, role-based redirect | Admin & Kasir |
| **Dashboard** | Ringkasan: total produk, stok menipis, stok habis, penjualan hari ini, grafik penjualan 7 hari (Chart.js) | Admin & Kasir (tampilan berbeda) |
| **Kategori** | CRUD kategori barang | Admin |
| **Produk** | CRUD produk, SKU unik, indikator stok menipis/habis, pencarian/filter (nama, SKU, kategori) | Admin |
| **Pembelian** | Form dinamis (tambah/hapus item via jQuery), auto stok bertambah, nomor invoice otomatis (`PO-YYYYMMDD-XXXX`), filter tanggal, detail transaksi | Admin |
| **Penjualan** | Form dinamis, auto stok berkurang, validasi server-side (stok tidak boleh minus), nomor invoice otomatis (`INV-YYYYMMDD-XXXX`), filter tanggal, cetak invoice PDF | Admin & Kasir (kasir hanya lihat transaksinya sendiri) |
| **Laporan** | Laporan stok (aman/menipis/habis), laporan penjualan (filter tanggal), laporan laba kotor, export ke Excel | Admin |

### Fase 2 — Audit & Kontrol Stok

| Modul | Fitur | Role |
|-------|-------|------|
| **Activity Log** | Catat otomatis setiap create/update/delete pada produk, pembelian, dan penjualan. Lihat riwayat lengkap dengan filter user & tanggal. | Admin |
| **Stock Opname** | Pencocokan stok fisik vs sistem. Input hasil hitung fisik per produk, selisih otomatis, stok disesuaikan ke nilai fisik. Riwayat & detail opname dengan warna selisih (merah = hilang, hijau = lebih). | Admin |
| **Notifikasi Stok Menipis** | Dropdown di navbar (icon lonceng) menampilkan produk dengan stok ≤ min_stok, diurutkan dari yang paling kritis. Badge jumlah otomatis. | Semua role |
| **Dark Mode** | Toggle dark/light mode di navbar kanan (bawaan AdminLTE 3). | Semua role |

## 🛠️ Instalasi

### Prasyarat
- PHP 8.2+ dengan ekstensi: `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `json`, `ctype`, `bcrypt`
- Composer
- MySQL (via XAMPP)

### Langkah Instalasi

```bash
# 1. Clone/unzip proyek ke direktori XAMPP
cd C:\xampp\htdocs\laravel_claude

# 2. Install dependency PHP
composer install

# 3. Copy file environment
cp .env.example .env
# Konfigurasi database di .env:
# DB_DATABASE=pos_db
# DB_USERNAME=root
# DB_PASSWORD=

# 4. Generate key aplikasi
php artisan key:generate

# 5. Setup database & seed data awal
php artisan migrate:fresh --seed

# 6. Jalankan server
php artisan serve
```

Buka di browser: **http://localhost:8000**

## 🔐 Akun Default (Setelah Seeding)

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@example.com | password |
| Kasir | kasir@example.com | password |

## 📁 Struktur Direktori

```
app/
├── Models/                  # Eloquent Models (11 model)
│   ├── Product.php, Category.php, Supplier.php
│   ├── Purchase.php, PurchaseItem.php
│   ├── Sale.php, SaleItem.php
│   ├── ActivityLog.php
│   ├── StockOpname.php, StockOpnameItem.php
│   └── User.php
├── Http/
│   ├── Controllers/         # Resource Controllers
│   │   ├── DashboardController.php
│   │   ├── CategoryController.php, ProductController.php
│   │   ├── PurchaseController.php, SaleController.php
│   │   ├── ReportController.php (+ export Excel)
│   │   ├── ActivityLogController.php
│   │   ├── StockOpnameController.php
│   │   └── NotificationController.php
│   ├── Requests/            # Form Request classes (validasi)
│   └── Middleware/          # Role check middleware
├── Services/
│   └── ActivityLogger.php   # Service untuk catat activity log
├── Exports/
│   └── SalesReportExport.php
database/
├── migrations/              # 11 migration (termasuk Fase 2)
└── seeders/                 # Data dummy (kategori, produk, user)
resources/
├── views/
│   ├── dashboard.blade.php
│   ├── categories/          # CRUD kategori
│   ├── products/            # CRUD produk
│   ├── purchases/           # Transaksi pembelian
│   ├── sales/               # Transaksi penjualan
│   ├── reports/             # Laporan stok, penjualan, laba
│   ├── activity-logs/       # Riwayat audit trail
│   └── stock-opnames/       # Form & riwayat opname
routes/
└── web.php                  # Semua route aplikasi
```

## 📐 Konvensi Kode

- **Model**: PascalCase, singular (contoh: `Product`)
- **Variabel/Method**: camelCase (contoh: `$totalStock`)
- **Kolom Database**: snake_case (contoh: `product_name`)
- **Validasi**: Menggunakan Form Request class (`php artisan make:request`)
- **Transaksi multi-tabel**: Wajib `DB::transaction()` — rollback jika satu saja gagal

## 🏪 Aturan Bisnis

1. **Stok tidak boleh minus** — divalidasi server-side sebelum transaksi penjualan
2. **Pembelian** → stok produk otomatis **bertambah**
3. **Penjualan** → stok produk otomatis **berkurang**
4. **Harga transaksi** adalah harga *saat itu* (bukan harga terkini produk), agar riwayat akurat
5. **Nomor invoice unik**: `INV-YYYYMMDD-XXXX` (penjualan), `PO-YYYYMMDD-XXXX` (pembelian), `SO-YYYYMMDD-XXXX` (opname)
6. **Activity Log**: Setiap create/update/delete pada Produk, Pembelian, dan Penjualan otomatis tercatat
7. **Stock Opname**: Stok disesuaikan **langsung** ke nilai fisik (bukan ditambah/dikurangi)

## 📚 Dokumentasi Tambahan

- [PRD.md](PRD.md) — Product Requirement Document (detail fitur & alur bisnis)
- [CLAUDE.md](CLAUDE.md) — Instruksi pengembangan untuk Claude Code
- [SCHEMA.md](SCHEMA.md) — Skema database lengkap (11 tabel)
- [TASKS.md](TASKS.md) — Checklist Fase 1 (Hari 1-7)
- [TASKS_PHASE2.md](TASKS_PHASE2.md) — Checklist Fase 2 (Activity Log, Stock Opname, Quick Wins)

## 🤝 Lisensi

Proyek pembelajaran untuk menguasai kemampuan Claude Code.
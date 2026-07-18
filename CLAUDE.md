# Project: Sistem Inventori & Penjualan (Mini POS)

## Deskripsi Singkat
Aplikasi web untuk mengelola stok barang, transaksi pembelian, dan transaksi
penjualan pada sebuah toko/usaha kecil. Dibangun sebagai proyek belajar
"vibe coding" menggunakan Claude Code.

## Tech Stack
- Laravel 11, PHP 8.2+
- Blade template + **AdminLTE 3** (Bootstrap 4) via package `jeroennoten/laravel-adminlte`
- jQuery untuk interaktivitas ringan seperti form dinamis (tambah/hapus item transaksi) — AdminLTE 3 sudah menyertakan jQuery, jadi tidak perlu Alpine.js
- MySQL (dijalankan via XAMPP)
- Laravel Breeze untuk autentikasi (khusus logic auth; tampilan login/register akan disesuaikan ke style AdminLTE)
- barryvdh/laravel-dompdf untuk cetak invoice/nota (opsional, hari ke-6)
- Chart.js untuk grafik laporan (AdminLTE 3 sudah punya contoh integrasi Chart.js bawaan)

## Setup AdminLTE 3
```bash
composer require jeroennoten/laravel-adminlte
php artisan adminlte:install
php artisan adminlte:install --only=auth_views   # sesuaikan tampilan Breeze ke AdminLTE
```
- Layout utama: extend `layouts.adminlte::page` di setiap view.
- Konfigurasi menu sidebar ada di `config/adminlte.php` (bagian `menu`).
- Sidebar menu WAJIB disesuaikan per role (admin lihat semua menu, kasir hanya menu penjualan) — bisa pakai key `can` atau filter manual di config, atau override lewat Blade composer jika butuh logic dinamis.

## Konvensi Kode
- Gunakan Eloquent ORM, hindari raw query kecuali benar-benar perlu (contoh: laporan agregat kompleks).
- Controller mengikuti pola resource controller (index, create, store, edit, update, destroy).
- Semua validasi input WAJIB menggunakan Form Request class (`php artisan make:request`), bukan validasi inline di controller.
- Transaksi pembelian dan penjualan WAJIB dibungkus `DB::transaction()` karena melibatkan lebih dari satu tabel (header + detail + update stok).
- Penamaan kolom database: `snake_case`.
- Penamaan variabel/method PHP: `camelCase`.
- Penamaan Model: singular, PascalCase (`Product`, bukan `Products`).
- Setiap Model punya relasi Eloquent yang jelas (`hasMany`, `belongsTo`, dst) — jangan query manual untuk relasi yang sudah bisa didefinisikan.
- View disusun per modul di `resources/views/{module}/`, contoh: `resources/views/products/index.blade.php`.
- Setiap view yang butuh layout admin WAJIB `@extends('adminlte::page')` dan mengisi section `content`, `content_header`, serta `css`/`js` jika perlu asset tambahan per halaman.
- Gunakan komponen AdminLTE bawaan (`card`, `box`, `table table-bordered`, `btn btn-primary`, dsb) — jangan campur dengan class Tailwind.
- Untuk form dinamis (tambah/hapus item transaksi), gunakan jQuery biasa (`$(document).on('click', ...)`), taruh script di section `js` pada view terkait, jangan taruh script global yang tidak perlu di semua halaman.

## Struktur Folder Penting
```
app/Models/
app/Http/Controllers/
app/Http/Requests/
app/Http/Middleware/          -> untuk role check (admin/kasir)
database/migrations/
database/seeders/
resources/views/{module}/index.blade.php
resources/views/{module}/create.blade.php
resources/views/{module}/edit.blade.php
routes/web.php
```

## Role User
- **admin**: akses penuh ke semua modul (master data, pembelian, penjualan, laporan, kelola user).
- **kasir**: hanya bisa akses modul penjualan (buat transaksi baru, lihat riwayat penjualan miliknya).

Gunakan middleware custom (misal `CheckRole`) atau package `spatie/laravel-permission` jika ingin lebih rapi — tapi untuk scope 7 hari, middleware sederhana dengan kolom `role` di tabel `users` sudah cukup.

## Aturan Bisnis Penting (WAJIB diikuti saat generate kode)
1. Stok produk tidak boleh minus. Saat transaksi penjualan disimpan, validasi dulu stok tersedia sebelum mengurangi.
2. Setiap transaksi pembelian otomatis MENAMBAH stok produk terkait.
3. Setiap transaksi penjualan otomatis MENGURANGI stok produk terkait.
4. Harga jual & harga beli yang tersimpan di `sale_items`/`purchase_items` adalah harga SAAT transaksi terjadi (bukan mengambil harga terbaru dari tabel produk), supaya riwayat transaksi tidak berubah jika harga produk di-update kemudian.
5. Nomor transaksi (invoice number) harus unik, format bebas tapi konsisten, contoh: `INV-20260712-0001`.
6. *(Fase 2)* Setiap create/update/delete pada Produk, Pembelian, Penjualan WAJIB dicatat lewat `ActivityLogger::log()` — lihat `app/Services/ActivityLogger.php`.
7. *(Fase 2)* Saat stock opname disimpan, `products.stock` disesuaikan LANGSUNG ke nilai `physical_stock` (bukan ditambah/dikurangi), dan tetap dibungkus `DB::transaction()`.

## Perintah yang Sering Dipakai
```bash
php artisan serve
php artisan migrate:fresh --seed
php artisan make:model Product -mfr
php artisan make:request StoreProductRequest
php artisan route:list
php artisan adminlte:install
php artisan adminlte:install --only=auth_views
```

## Catatan untuk Claude Code
- Selalu baca `SCHEMA.md` sebelum membuat migration/model baru.
- Selalu baca `PRD.md` sebelum membuat fitur baru agar sesuai alur bisnis.
- Cek `TASKS.md` (fase 1) dan `TASKS_PHASE2.md` (fase 2) untuk tahu progres dan task yang sedang dikerjakan.
- Jangan generate seluruh aplikasi sekaligus dalam satu prompt — ikuti breakdown per task di `TASKS.md` / `TASKS_PHASE2.md`.

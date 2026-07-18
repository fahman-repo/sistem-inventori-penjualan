# TASKS.md
# Checklist Pengerjaan 7 Hari — Sistem Inventori & Penjualan

Gunakan file ini sebagai acuan progres. Centang `[x]` setiap task selesai.
Kerjakan bertahap per hari, jangan minta Claude Code generate semua sekaligus.

---

## Hari 1 — Setup & Struktur Database
- [x] Install project Laravel baru, konfigurasi `.env` untuk koneksi MySQL (XAMPP)
- [x] Buat database di phpMyAdmin
- [x] Install Laravel Breeze (Blade version)
- [x] Install package `jeroennoten/laravel-adminlte`, jalankan `php artisan adminlte:install`
- [x] Sesuaikan tampilan login/register Breeze ke AdminLTE (`php artisan adminlte:install --only=auth_views`)
- [x] Cek layout dasar bisa jalan (buat 1 halaman percobaan `@extends('adminlte::page')`)
- [x] Tambah kolom `role` ke tabel `users` (migration tambahan)
- [x] Buat migration: `categories`, `products`, `suppliers` (opsional)
- [x] Buat migration: `purchases`, `purchase_items`, `sales`, `sale_items`
- [x] Buat Model + relasi Eloquent untuk semua tabel di atas
- [x] Buat Seeder: kategori, produk contoh, user admin & kasir
- [x] Jalankan `php artisan migrate:fresh --seed`, pastikan tidak ada error

> Contoh prompt: *"Baca CLAUDE.md dan SCHEMA.md. Buatkan migration dan model lengkap dengan relasi Eloquent untuk semua tabel sesuai SCHEMA.md. Sertakan seeder dengan data contoh."*

---

## Hari 2 — Auth, Role, & Master Data Kategori/Produk
- [x] Setup middleware/pengecekan role (admin vs kasir), redirect sesuai role setelah login
- [x] Konfigurasi menu sidebar AdminLTE di `config/adminlte.php`, tampilkan menu berbeda untuk admin dan kasir (pakai `can`/gate atau filter manual)
- [x] CRUD Kategori (index, create, edit, delete) — hanya admin
- [x] CRUD Produk (index, create, edit, delete) — hanya admin
- [x] Validasi produk pakai Form Request (SKU unik, harga & stok tidak negatif)
- [x] Tambahkan indikator visual stok menipis/habis di halaman index produk
- [x] Fitur pencarian/filter produk (nama, SKU, kategori)

> Contoh prompt: *"Buatkan middleware role admin/kasir. Lalu buatkan CRUD lengkap untuk Category dan Product mengikuti konvensi di CLAUDE.md, termasuk Form Request untuk validasi."*

---

## Hari 3 — Transaksi Pembelian
- [x] Buat halaman form pembelian baru dengan form dinamis tambah/hapus item (jQuery), styling pakai komponen card/table AdminLTE
- [x] Buat PurchaseController (store dengan `DB::transaction()`)
- [x] Logika: simpan header + detail, tambah stok produk otomatis
- [x] Validasi server-side (jumlah > 0, produk valid)
- [x] Generate invoice_number otomatis (format: `PO-YYYYMMDD-XXXX`)
- [x] Halaman riwayat pembelian (index) dengan filter tanggal
- [x] Halaman detail 1 transaksi pembelian

> Contoh prompt: *"Buatkan fitur transaksi pembelian sesuai alur bisnis di PRD.md bagian 3.4. Form dinamis pakai jQuery, layout pakai komponen AdminLTE (card, table), proses simpan pakai DB::transaction(), stok produk otomatis bertambah."*

---

## Hari 4 — Transaksi Penjualan
- [x] Buat halaman form penjualan baru dengan form dinamis tambah/hapus item
- [x] Buat SaleController (store dengan `DB::transaction()`)
- [x] Validasi stok tersedia SEBELUM menyimpan (tidak boleh minus)
- [x] Logika: simpan header + detail, kurangi stok produk otomatis
- [x] Generate invoice_number otomatis (format: `INV-YYYYMMDD-XXXX`)
- [x] Kasir hanya bisa lihat riwayat transaksinya sendiri; admin lihat semua
- [x] Halaman detail 1 transaksi penjualan

> Contoh prompt: *"Buatkan fitur transaksi penjualan sesuai alur bisnis di PRD.md bagian 3.5. Pastikan validasi stok tidak boleh minus dilakukan di server, bukan hanya client."*

---

## Hari 5 — Dashboard & Laporan
- [x] Dashboard: total produk, jumlah stok menipis, total penjualan hari ini
- [x] Grafik penjualan 7 hari terakhir (Chart.js) di dashboard
- [x] Halaman Laporan Stok (status aman/menipis/habis)
- [x] Halaman Laporan Penjualan dengan filter rentang tanggal
- [x] (Opsional) Laporan laba kotor dari selisih sell_price - buy_price di sale_items

> Contoh prompt: *"Buatkan dashboard dengan ringkasan data dan grafik Chart.js untuk penjualan 7 hari terakhir. Lalu buatkan halaman laporan stok dan laporan penjualan dengan filter tanggal."*

---

## Hari 6 — Cetak Invoice & Styling
- [x] Install `barryvdh/laravel-dompdf`
- [x] Buat fitur cetak/download PDF invoice penjualan
- [x] Rapikan tampilan seluruh halaman pakai komponen AdminLTE (card, box, small-box untuk dashboard, table-bordered) agar konsisten di semua modul
- [x] Tambahkan flash message sukses/error pakai alert Bootstrap (`alert alert-success`, dll) atau toastr jika ingin lebih rapi (AdminLTE 3 mendukung plugin toastr)
- [x] Responsive check (tampilan di layar kecil minimal tidak berantakan) — Bootstrap 4 grid AdminLTE sudah responsive by default, tinggal cek breakpoint

> Contoh prompt: *"Tambahkan fitur cetak invoice PDF untuk transaksi penjualan menggunakan barryvdh/laravel-dompdf. Buat template invoice yang rapi."*

---

## Hari 7 — Testing & Polish
- [x] Test alur penuh: login admin → tambah produk → transaksi pembelian → cek stok bertambah
- [x] Test alur penuh: login kasir → transaksi penjualan → cek stok berkurang → cek validasi stok minus tertolak
- [x] Cek semua validasi form (input kosong, angka negatif, SKU duplikat, dll)
- [x] Perbaiki bug yang ditemukan
- [x] Bersihkan kode yang tidak terpakai, rapikan penamaan
- [x] (Opsional) Tulis README singkat cara install & menjalankan project

> Contoh prompt: *"Saya sudah menjalankan alur [jelaskan langkah], tapi terjadi error/bug berikut: [paste error]. Tolong analisis dan perbaiki."*

---

## Tips Prompting Harian
1. Selalu mulai prompt dengan: *"Baca CLAUDE.md, PRD.md, dan SCHEMA.md sebagai konteks."* (atau minta Claude Code baca file spesifik yang relevan).
2. Kerjakan satu fitur per prompt, jangan gabung banyak fitur sekaligus.
3. Setelah fitur jadi, selalu minta Claude Code jelaskan kode yang baru dibuat secara singkat — supaya kamu tetap paham alurnya, bukan cuma "terima jadi".
4. Kalau ada bug, paste pesan error lengkap + langkah yang kamu lakukan sebelum error muncul.

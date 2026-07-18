# PRD (Product Requirement Document)
# Sistem Inventori & Penjualan (Mini POS)

## 1. Tujuan
Membangun aplikasi web sederhana untuk mengelola stok barang dan mencatat
transaksi pembelian & penjualan pada usaha kecil (toko kelontong, warung,
distro, dsb), lengkap dengan laporan dasar.

## 2. Target Pengguna
- **Admin**: pemilik toko, mengelola master data, memantau laporan, mengelola user.
- **Kasir**: karyawan yang melayani transaksi penjualan harian.

## 3. Modul & Fitur

### 3.1 Autentikasi
- Login & logout (Laravel Breeze).
- Role otomatis menentukan menu yang tampil (admin vs kasir).
- Admin bisa menambah/menghapus akun kasir.

### 3.2 Master Data — Kategori
- CRUD kategori barang (nama kategori).
- Kategori dipakai untuk mengelompokkan produk di laporan & filter.

### 3.3 Master Data — Produk
- CRUD produk: nama, SKU/kode barang (unik), kategori, harga beli, harga jual, stok saat ini, satuan (pcs/box/kg, dsb).
- Validasi: SKU unik, harga & stok tidak boleh negatif.
- Indikator visual untuk stok menipis (misal < 10) dan stok habis (0).
- Fitur cari/filter produk berdasarkan nama, SKU, atau kategori.

### 3.4 Transaksi Pembelian (Stok Masuk)
- Form input pembelian: pilih produk (bisa lebih dari 1 item per transaksi), jumlah, harga beli saat itu.
- Form dinamis: tombol "tambah item" dan "hapus item" (pakai Alpine.js).
- Saat disimpan:
  - Data header (`purchases`) dan detail (`purchase_items`) tersimpan dalam satu `DB::transaction()`.
  - Stok produk otomatis bertambah sesuai jumlah yang dibeli.
- Riwayat pembelian bisa dilihat & difilter per tanggal.
- Detail 1 transaksi pembelian bisa dilihat kembali (invoice pembelian).

### 3.5 Transaksi Penjualan (Stok Keluar)
- Form input penjualan: pilih produk, jumlah, harga jual otomatis terisi (bisa diedit jika perlu diskon).
- Form dinamis tambah/hapus item.
- Validasi: jumlah yang dijual tidak boleh melebihi stok tersedia (dicek per item saat submit).
- Saat disimpan:
  - Data header (`sales`) dan detail (`sale_items`) tersimpan dalam satu `DB::transaction()`.
  - Stok produk otomatis berkurang.
  - Total transaksi dihitung otomatis (jumlah × harga jual per item, dijumlah semua item).
- Kasir hanya bisa melihat riwayat transaksi yang dia buat sendiri; admin bisa melihat semua.
- Cetak nota/invoice penjualan (PDF, opsional hari ke-6).

### 3.6 Laporan
- **Laporan Stok**: daftar produk dengan status (aman/menipis/habis), bisa export atau minimal tampil di tabel.
- **Laporan Penjualan**: total penjualan per hari/bulan, filter rentang tanggal, grafik batang/garis (Chart.js) menampilkan tren penjualan.
- **Laporan Laba Kotor** (opsional jika waktu cukup): selisih harga jual dan harga beli dari data `sale_items` yang tersimpan.

### 3.7 Dashboard
- Ringkasan cepat saat login: total produk, produk stok menipis, total penjualan hari ini, grafik penjualan 7 hari terakhir.

## 4. Alur Bisnis Kunci

### Alur Transaksi Penjualan
1. Kasir membuka form penjualan baru.
2. Kasir menambahkan produk satu per satu (cari produk → pilih → input jumlah).
3. Sistem menampilkan subtotal per item dan total keseluruhan secara real-time (JS di sisi client).
4. Kasir submit form.
5. Server memvalidasi ulang stok setiap item (validasi tidak boleh hanya di client).
6. Jika valid: simpan header `sales`, simpan semua `sale_items`, kurangi stok tiap produk — semua dalam satu `DB::transaction()`. Jika ada satu saja yang gagal, seluruh transaksi di-rollback.
7. Sistem menampilkan halaman sukses / invoice.

### Alur Transaksi Pembelian
Sama seperti alur penjualan, tapi kebalikannya: stok bertambah, tidak perlu validasi batas maksimum stok.

## 5. Batasan Scope (Non-Goals untuk versi 7 hari)
- Tidak ada multi-cabang/multi-gudang.
- Tidak ada integrasi payment gateway.
- Tidak ada retur barang (bisa jadi pengembangan lanjutan).
- Tidak ada multi-supplier kompleks (cukup 1 field nama supplier opsional, tanpa modul supplier terpisah jika waktu terbatas).

## 6. Kriteria Sukses (Definition of Done)
- Admin bisa CRUD kategori & produk.
- Kasir bisa melakukan transaksi penjualan dan stok berkurang otomatis dengan benar.
- Admin bisa melakukan transaksi pembelian dan stok bertambah otomatis dengan benar.
- Laporan stok dan penjualan menampilkan data yang akurat sesuai transaksi yang terjadi.
- Tidak ada kasus stok menjadi minus dalam kondisi apa pun.

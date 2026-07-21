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

### 3.8 Activity Log (Fase 2)
- Setiap aksi create/update/delete pada modul Produk, Pembelian, dan Penjualan otomatis tercatat: siapa, kapan, aksi apa, data sebelum & sesudah.
- Hanya admin yang bisa melihat halaman riwayat activity log.
- Filter berdasarkan user dan rentang tanggal.
- Tujuan: transparansi dan audit trail untuk sistem yang melibatkan uang & stok.

### 3.9 Stock Opname (Fase 2)
- Admin/kasir yang ditugaskan bisa melakukan pencocokan stok fisik vs stok sistem secara berkala.
- Saat opname dibuat: sistem menampilkan semua produk beserta stok sistem saat ini, user menginput hasil hitung fisik per produk.
- Sistem menghitung selisih otomatis (physical_stock - system_stock).
- Setelah disimpan, stok produk di tabel `products` disesuaikan langsung ke nilai physical_stock (bukan ditambah/dikurangi).
- Setiap opname tercatat di activity log (aksi 'stock_opname').
- Riwayat opname bisa dilihat kembali beserta detail selisih tiap produk (selisih negatif = stok hilang, ditandai warna berbeda).

### 3.10 Manajemen User (Fase 3)
- Admin bisa CRUD user: tambah kasir baru, edit data user, nonaktifkan/hapus user.
- Form tambah/edit user memilih role (admin/kasir) lewat dropdown yang tervalidasi (bukan input bebas).
- Admin tidak bisa menghapus akun dirinya sendiri (guard sederhana agar tidak ada admin yang terkunci dari sistem).
- Riwayat siapa membuat/mengubah user tercatat lewat activity log yang sudah ada di Fase 2.

### 3.11 Supplier & Utang (Fase 3)
- Modul supplier penuh: CRUD data supplier (nama, telepon, alamat, email).
- Setiap transaksi pembelian punya status pembayaran: `cash` (lunas langsung) atau `credit` (menjadi utang).
- Jika `credit`: sistem otomatis membuat record di `supplier_debts` sebesar total pembelian, dengan status awal `unpaid`.
- Admin bisa mencatat pembayaran cicilan utang (`supplier_debt_payments`) — status utang otomatis berubah jadi `partial` atau `paid` tergantung total yang sudah dibayar vs total utang.
- Halaman daftar utang: filter berdasarkan status (unpaid/partial/paid) dan supplier, tampilkan sisa utang & jatuh tempo.
- Riwayat pembelian per supplier bisa dilihat dari halaman detail supplier.

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

### Alur Stock Opname
1. User membuka form opname baru, sistem menampilkan seluruh produk beserta stok sistem saat ini.
2. User menginput hasil hitung fisik untuk tiap produk (produk yang tidak diubah dianggap sama dengan stok sistem).
3. User submit form.
4. Server menghitung selisih tiap item, menyimpan header `stock_opnames` + detail `stock_opname_items`, lalu menyesuaikan `products.stock` ke nilai physical_stock — semua dalam satu `DB::transaction()`.
5. Aksi ini otomatis tercatat di `activity_logs`.

### Alur Pencatatan & Pembayaran Utang Supplier
1. Admin membuat transaksi pembelian, memilih status pembayaran `credit`.
2. Sistem otomatis membuat record `supplier_debts` (total_amount = total pembelian, paid_amount = 0, status = 'unpaid').
3. Saat supplier menerima pembayaran (cicilan atau lunas sekaligus), admin mencatat pembayaran baru di halaman detail utang.
4. Sistem menambahkan record ke `supplier_debt_payments`, lalu menghitung ulang `paid_amount` (SUM semua payment) dan mengupdate `status`: `paid` jika paid_amount >= total_amount, `partial` jika 0 < paid_amount < total_amount, `unpaid` jika belum ada pembayaran.
5. Semua proses ini dibungkus `DB::transaction()` agar konsisten.

## 5. Batasan Scope (Non-Goals untuk versi 7 hari, di luar Fase 3)
- Tidak ada multi-cabang/multi-gudang.
- Tidak ada integrasi payment gateway.
- Tidak ada retur barang (bisa jadi pengembangan lanjutan).
- Tidak ada piutang pelanggan (baru mencakup utang ke supplier di Fase 3, piutang pelanggan bisa jadi fase berikutnya).

## 6. Kriteria Sukses (Definition of Done)
- Admin bisa CRUD kategori & produk.
- Kasir bisa melakukan transaksi penjualan dan stok berkurang otomatis dengan benar.
- Admin bisa melakukan transaksi pembelian dan stok bertambah otomatis dengan benar.
- Laporan stok dan penjualan menampilkan data yang akurat sesuai transaksi yang terjadi.
- Tidak ada kasus stok menjadi minus dalam kondisi apa pun.

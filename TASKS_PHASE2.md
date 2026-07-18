# TASKS_PHASE2.md
# Pengembangan Lanjutan — Sistem Inventori & Penjualan

Fase 2 ini fokus ke 2 fitur prioritas: **Activity Log** dan **Stock Opname**,
ditambah beberapa quick-win. Setiap task sudah dilengkapi prompt siap pakai —
tinggal copy-paste ke Claude Code, sesuaikan detail kecil jika perlu.

Tetap kerjakan **satu per satu**, review & test tiap task sebelum lanjut,
baru centang.

---

## A. Activity Log (Audit Trail)

Tujuan: mencatat siapa melakukan apa (create/update/delete) pada data penting
(produk, pembelian, penjualan) — krusial untuk sistem yang melibatkan uang & stok.

### A1. Setup tabel activity log
- [x] Buat migration & model `ActivityLog`

**Prompt:**
```
Baca CLAUDE.md sebagai konteks. Saya ingin menambahkan fitur activity log.
Buatkan migration tabel `activity_logs` dengan kolom: id, user_id (foreign ke users),
action (string, contoh: 'create', 'update', 'delete'), model_type (string, nama model terkait
misal 'Product'), model_id (bigint, id record yang diubah), description (text, ringkasan
perubahan), old_values (json, nullable), new_values (json, nullable), timestamps.
Buatkan juga Model ActivityLog dengan relasi belongsTo ke User.
```

### A2. Buat helper/service untuk mencatat log
- [x] Buat class helper agar logging bisa dipanggil singkat dari controller manapun

**Prompt:**
```
Buatkan sebuah service class app/Services/ActivityLogger.php dengan method statis
log(string $action, Model $model, ?array $oldValues = null, ?array $newValues = null).
Method ini otomatis mengambil user yang sedang login (auth()->id()) dan menyimpan
record baru ke tabel activity_logs sesuai model ActivityLog yang sudah dibuat.
Sertakan juga contoh cara pemanggilannya di komentar.
```

### A3. Terapkan logging di modul Produk, Pembelian, Penjualan
- [x] Tambahkan pemanggilan `ActivityLogger::log()` di controller terkait

**Prompt:**
```
Tambahkan pemanggilan ActivityLogger::log() pada ProductController (method store,
update, destroy), PurchaseController (method store), dan SaleController (method store).
Untuk update, sertakan old_values (data sebelum diubah) dan new_values (data sesudah
diubah). Untuk create, new_values saja. Untuk delete, old_values saja.
```

### A4. Halaman riwayat activity log
- [x] Buat halaman index activity log (khusus admin) dengan filter user & tanggal

**Prompt:**
```
Buatkan ActivityLogController dengan method index yang menampilkan riwayat activity log,
diurutkan terbaru dulu, dengan filter berdasarkan user dan rentang tanggal. Buatkan juga
view resources/views/activity-logs/index.blade.php menggunakan layout AdminLTE (table
dengan komponen card), tampilkan kolom: waktu, user, aksi, model, deskripsi. Halaman ini
hanya bisa diakses role admin.
```

---

## B. Stock Opname (Pencocokan Stok Fisik)

Tujuan: mencatat hasil pengecekan stok fisik dan menyesuaikan stok sistem secara
terkontrol, dengan riwayat penyesuaian yang bisa diaudit.

### B1. Skema database stock opname
- [x] Buat migration & model `stock_opnames` dan `stock_opname_items`

**Prompt:**
```
Baca SCHEMA.md sebagai referensi pola tabel header-detail yang sudah dipakai di
purchases/sales. Buatkan migration dan model untuk fitur stock opname dengan pola serupa:
- stock_opnames: id, opname_number (unique), user_id, opname_date, notes, timestamps
- stock_opname_items: id, stock_opname_id (foreign, cascade), product_id, system_stock
  (integer, stok menurut sistem saat opname dibuat), physical_stock (integer, hasil hitung
  fisik), difference (integer, physical_stock - system_stock), timestamps
Sertakan relasi Eloquent lengkap (hasMany, belongsTo).
```

### B2. Form input stock opname
- [x] Buat halaman untuk memilih produk dan input stok fisik hasil hitung

**Prompt:**
```
Buatkan StockOpnameController dengan method create dan store. Halaman create menampilkan
daftar semua produk (nama, SKU, stok sistem saat ini) dengan input jumlah stok fisik
di sampingnya (bisa pakai form biasa dengan array input, tidak perlu tambah/hapus item
dinamis karena semua produk ditampilkan sekaligus). Gunakan layout AdminLTE (table di
dalam card). Saat submit, hitung selisih (difference) otomatis di server.
```

### B3. Proses penyesuaian stok setelah opname disimpan
- [x] Update stok produk sesuai hasil opname, di dalam DB::transaction()

**Prompt:**
```
Lengkapi method store() di StockOpnameController: simpan header stock_opnames dan
semua stock_opname_items dalam satu DB::transaction(). Setelah item tersimpan, update
kolom stock di tabel products sesuai nilai physical_stock dari setiap item (bukan
ditambah/dikurangi, tapi langsung disesuaikan ke nilai hasil hitung fisik). Panggil
ActivityLogger::log() untuk mencatat aksi ini dengan action 'stock_opname'.
```

### B4. Riwayat & detail stock opname
- [x] Halaman index riwayat opname + detail per opname (menampilkan selisih tiap produk)

**Prompt:**
```
Buatkan halaman index riwayat stock opname (tabel: nomor opname, tanggal, user, jumlah
produk yang disesuaikan) dan halaman detail yang menampilkan semua item beserta
system_stock, physical_stock, dan difference-nya. Beri warna berbeda untuk selisih
negatif (stok hilang, warna merah) dan positif (stok lebih, warna hijau) menggunakan
class Bootstrap (text-danger / text-success).
```

---

## C. Quick Wins (opsional, kerjakan kalau waktu masih ada)

### C1. Export laporan ke Excel
**Prompt:**
```
Install package maatwebsite/excel. Buatkan fitur export laporan penjualan ke Excel
(.xlsx) berdasarkan filter tanggal yang sudah ada di halaman laporan penjualan.
Sertakan kolom: tanggal, invoice, kasir, total.
```

### C2. Dark mode toggle
**Prompt:**
```
AdminLTE 3 sudah mendukung dark mode bawaan. Tolong aktifkan opsi dark mode di
config/adminlte.php dan tambahkan toggle switch dark/light di navbar agar user bisa
mengubah tema sesuai preferensi, tersimpan di session.
```

### C3. Notifikasi stok menipis di navbar
**Prompt:**
```
Tambahkan dropdown notifikasi di navbar AdminLTE yang menampilkan daftar produk dengan
stock <= min_stock, mirip komponen notification bawaan AdminLTE (icon lonceng dengan
badge jumlah). Ambil data langsung dari tabel products, urutkan dari yang stoknya
paling kritis.
```

---

## Catatan Pengerjaan
- Kerjakan bagian **A (Activity Log)** dulu sampai selesai sebelum masuk ke **B (Stock Opname)** — karena B3 di atas memanfaatkan `ActivityLogger` yang dibuat di A2.
- Setelah tiap task selesai dan sudah kamu test manual, update checklist ini (`- [ ]` → `- [x]`).
- Kalau ada error saat testing, screenshot dan ikuti alur debugging seperti yang sudah dibahas sebelumnya (drag-drop screenshot ke Claude Code + sebutkan file terkait).

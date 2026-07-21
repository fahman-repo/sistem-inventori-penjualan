# TASKS_PHASE3.md
# Manajemen User & Supplier/Utang — Sistem Inventori & Penjualan

Fase ini murni **penambahan modul baru**, tidak mengubah struktur tabel inti
(`products`, logic stok) yang sudah berjalan stabil. Risiko jauh lebih rendah
dibanding opsi multi-cabang. Tetap disarankan backup database sebelum mulai,
tapi tidak ada migrasi data yang berisiko seperti fase multi-cabang.

Kerjakan **satu per satu**, review & test tiap task sebelum lanjut, baru centang.
Urutan: **A (Manajemen User)** boleh dikerjakan duluan atau setelah **B (Supplier
& Utang)** — keduanya independen satu sama lain.

---

## A. Manajemen User

### A1. CRUD User
- [x] Halaman kelola user (tambah, edit, nonaktifkan/hapus)

**Prompt:**
```
Baca CLAUDE.md dan PRD.md bagian 3.10 (Manajemen User) sebagai konteks. Buatkan
UserController resource (index, create, store, edit, update, destroy) khusus role
admin. View menggunakan layout AdminLTE. Form create/edit menyertakan dropdown
role (admin/kasir) yang tervalidasi lewat Form Request — jangan biarkan input role
bebas. Untuk create, password wajib diisi (minimal 8 karakter); untuk edit,
password opsional (kosongkan jika tidak ingin mengubah).
```

### A2. Guard agar admin tidak bisa hapus akun sendiri
- [x] Tambahkan validasi sederhana di method destroy

**Prompt:**
```
Tambahkan validasi di method destroy UserController: tolak penghapusan jika
$user->id sama dengan auth()->id() (mencegah admin menghapus akunnya sendiri
yang sedang login), tampilkan pesan error yang jelas via session flash message.
```

### A3. Integrasikan dengan Activity Log (Fase 2)
- [x] Pastikan aksi CRUD user tercatat

**Prompt:**
```
Tambahkan pemanggilan ActivityLogger::log() (yang sudah dibuat di Fase 2) pada
method store, update, dan destroy di UserController, mengikuti pola yang sama
seperti sudah diterapkan di ProductController.
```

---

## B. Supplier & Utang

### B1. Aktifkan modul Supplier penuh
- [x] Migration alter tabel suppliers (tambah kolom email), CRUD supplier

**Prompt:**
```
Baca SCHEMA.md bagian "Perubahan pada tabel yang sudah ada (Fase 3)". Buatkan
migration alter table suppliers untuk menambah kolom email (nullable). Lalu
buatkan SupplierController resource lengkap (index, create, store, edit, update,
destroy) dengan view AdminLTE, khusus role admin. Tambahkan validasi lewat Form
Request (nama wajib, email format valid jika diisi).
```

### B2. Tambah status pembayaran di transaksi pembelian
- [x] Migration alter purchases, update form pembelian

**Prompt:**
```
Buatkan migration alter table purchases untuk menambah kolom payment_status
(enum 'cash','credit', default 'cash') sesuai SCHEMA.md. Update form create
transaksi pembelian: tambahkan pilihan radio/dropdown "Cash" atau "Credit (Utang)",
serta dropdown pilih supplier (relasi yang sudah ada). Update PurchaseController
agar menyimpan payment_status dan supplier_id yang dipilih.
```

### B3. Skema utang & pembuatan otomatis saat pembelian credit
- [x] Migration supplier_debts, supplier_debt_payments + logic otomatis

**Prompt:**
```
Baca alur bisnis "Alur Pencatatan & Pembayaran Utang Supplier" di PRD.md dan
skema di SCHEMA.md. Buatkan migration dan model untuk supplier_debts dan
supplier_debt_payments lengkap dengan relasi Eloquent. Lalu update method store()
di PurchaseController: setelah transaksi pembelian berhasil disimpan, JIKA
payment_status == 'credit', otomatis buat record baru di supplier_debts dengan
total_amount = total pembelian, paid_amount = 0, status = 'unpaid'. Proses ini
tetap dalam DB::transaction() yang sama dengan penyimpanan pembelian.
```

### B4. Halaman daftar & pembayaran utang
- [x] Index utang dengan filter, form catat pembayaran cicilan

**Prompt:**
```
Buatkan SupplierDebtController dengan method index (daftar semua utang, filter
berdasarkan status unpaid/partial/paid dan supplier, tampilkan sisa utang =
total_amount - paid_amount) dan method show (detail 1 utang beserta riwayat
pembayaran). Di halaman detail, sediakan form untuk mencatat pembayaran baru
(jumlah, tanggal, catatan). Saat pembayaran disimpan: buat record baru di
supplier_debt_payments, lalu hitung ulang paid_amount (SUM semua payment terkait)
dan update status supplier_debts sesuai aturan di PRD.md (paid/partial/unpaid).
Gunakan layout AdminLTE, tampilkan badge warna berbeda untuk tiap status.
```

### B5. Riwayat pembelian per supplier
- [x] Halaman detail supplier menampilkan riwayat transaksi

**Prompt:**
```
Tambahkan method show di SupplierController yang menampilkan detail supplier
beserta daftar riwayat pembelian dari supplier tersebut (tanggal, invoice, total,
status pembayaran, status utang jika ada). Gunakan komponen table AdminLTE.
```

---

## C. Quick Wins Tambahan (opsional)

### C1. Notifikasi utang jatuh tempo di dashboard
- [x] Widget small-box warning/danger + daftar utang di dashboard
**Prompt:**
```
Tambahkan widget di dashboard yang menampilkan daftar utang supplier dengan
status belum lunas (unpaid/partial) yang due_date-nya sudah lewat atau dalam
7 hari ke depan. Gunakan komponen small-box AdminLTE dengan warna warning/danger.
```

### C2. Export daftar utang ke Excel
- [x] Export Excel mengikuti filter aktif (status, supplier)
**Prompt:**
```
Kalau package maatwebsite/excel sudah terpasang dari fase sebelumnya, tambahkan
fitur export daftar utang supplier ke Excel dari halaman index SupplierDebtController,
mengikuti filter yang sedang aktif (status, supplier).
```

---

## Catatan Pengerjaan
- Task A dan B bisa dikerjakan dalam urutan bebas (tidak saling bergantung).
- Task B2 harus selesai sebelum B3 (karena B3 memakai kolom `payment_status` yang dibuat di B2).
- Setelah tiap task selesai dan sudah kamu test manual, update checklist ini.

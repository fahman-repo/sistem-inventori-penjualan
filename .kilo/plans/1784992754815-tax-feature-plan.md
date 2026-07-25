# Plan: Fitur Pajak (Tax) untuk Transaksi Pembelian & Penjualan

## Konteks

Sistem Inventori & Penjualan saat ini tidak memiliki fitur pajak. Transaksi pembelian dan penjualan hanya mencatat subtotal per item dan total keseluruhan. Invoice PDF sudah memiliki placeholder "Pajak: Rp 0" yang belum terhubung ke data apapun.

**Tujuan:** Menambahkan modul data master pajak (CRUD) dan integrasi pajak pada transaksi pembelian/penjualan dengan checkbox opsional.

---

## Desain Database

### Tabel baru: `taxes`

| Kolom      | Tipe                       | Keterangan                      |
|------------|----------------------------|----------------------------------|
| id         | bigIncrements              | PK                               |
| name       | string                     | Nama pajak (e.g. "PPN 11%")     |
| rate       | decimal(5,2)               | Tarif dalam persen (e.g. 11.00) |
| is_active  | boolean, default true      | Untuk soft-deactivate            |
| timestamps |                            |                                  |

### Perubahan tabel existing

**`purchases`** — tambah kolom:

| Kolom      | Tipe                          | Keterangan                    |
|------------|-------------------------------|--------------------------------|
| tax_id     | foreignId → taxes.id, nullable| null = tidak kena pajak        |
| tax_amount | decimal(14,2), default 0      | Nominal pajak saat transaksi   |

**`sales`** — tambah kolom yang sama:

| Kolom      | Tipe                          | Keterangan                    |
|------------|-------------------------------|--------------------------------|
| tax_id     | foreignId → taxes.id, nullable| null = tidak kena pajak        |
| tax_amount | decimal(14,2), default 0      | Nominal pajak saat transaksi   |

**Alasan menyimpan `tax_amount` (bukan hanya `tax_id` + hitung ulang):** Konsisten dengan aturan bisnis CLAUDE.md poin 4 — harga/tarif yang tersimpan adalah saat transaksi terjadi, bukan mengambil tarif terbaru. Jika tarif pajak berubah, riwayat transaksi lama tetap akurat.

---

## Daftar Task

### Task 1: Migration & Model `Tax`

**File baru:**
- `database/migrations/xxxx_xx_xx_create_taxes_table.php`
- `app/Models/Tax.php`

**File ubah:**
- `database/migrations/xxxx_xx_xx_add_tax_columns_to_purchases_table.php` (migration baru)
- `database/migrations/xxxx_xx_xx_add_tax_columns_to_sales_table.php` (migration baru)
- `app/Models/Purchase.php` — tambah `tax_id`, `tax_amount` ke `$fillable`, cast `tax_amount` sebagai decimal, tambah relasi `belongsTo(Tax::class)`
- `app/Models/Sale.php` — sama seperti Purchase

**Detail Model Tax:**
```php
// app/Models/Tax.php
protected $fillable = ['name', 'rate', 'is_active'];
protected $casts = ['rate' => 'decimal:2', 'is_active' => 'boolean'];

public function purchases() { return $this->hasMany(Purchase::class); }
public function sales() { return $this->hasMany(Sale::class); }
```

**Detail perubahan Purchase/Sale Model:**
- Tambah `tax_id`, `tax_amount` ke `$fillable`
- Tambah `'tax_amount' => 'decimal:2'` ke `$casts`
- Tambah relasi `public function tax(): BelongsTo { return $this->belongsTo(Tax::class); }`

---

### Task 2: CRUD Data Master Pajak (Admin Only)

**File baru:**
- `app/Http/Controllers/TaxController.php` — resource controller (index, create, store, edit, update, destroy)
- `app/Http/Requests/StoreTaxRequest.php` — validasi: `name` required, `rate` required numeric min:0 max:100, `is_active` boolean
- `app/Http/Requests/UpdateTaxRequest.php` — sama + unique name ignore current
- `resources/views/taxes/index.blade.php` — tabel AdminLTE dengan badge rate, tombol edit/hapus
- `resources/views/taxes/create.blade.php` — form: name, rate (%), is_active checkbox
- `resources/views/taxes/edit.blade.php` — form edit

**File ubah:**
- `routes/web.php` — tambah `Route::resource('admin/taxes', TaxController::class);` di dalam group admin
- `config/adminlte.php` — tambah submenu "Pajak" di bawah "Supplier" dalam Master Data, icon `fas fa-fw fa-percent`
- Integrasi `ActivityLogger::log()` pada store, update, destroy (mengikuti pola ProductController)

**Validasi StoreTaxRequest:**
```php
'name' => ['required', 'string', 'max:255', 'unique:taxes,name'],
'rate' => ['required', 'numeric', 'min:0', 'max:100'],
'is_active' => ['nullable', 'boolean'],
```

**Validasi UpdateTaxRequest:**
```php
'name' => ['required', 'string', 'max:255', 'unique:taxes,name,' . $this->route('tax')->id],
'rate' => ['required', 'numeric', 'min:0', 'max:100'],
'is_active' => ['nullable', 'boolean'],
```

---

### Task 3: Integrasi Pajak pada Transaksi Pembelian

**File ubah:**
- `app/Http/Controllers/PurchaseController.php`
  - `create()` / `edit()`: passing `$taxes = Tax::where('is_active', true)->get()` ke view
  - `store()`: ambil `tax_id` dari validated, jika ada → hitung `tax_amount = $total * ($tax->rate / 100)`, simpan ke purchase
  - `update()`: logika sama, hitung ulang tax_amount
- `app/Http/Requests/StorePurchaseRequest.php` — tambah: `'tax_id' => ['nullable', 'exists:taxes,id']`
- `app/Http/Requests/UpdatePurchaseRequest.php` — tambah sama
- `resources/views/purchases/create.blade.php`
- `resources/views/purchases/edit.blade.php`
- `resources/views/purchases/show.blade.php`
- `resources/views/purchases/index.blade.php`

**Perubahan di view create/edit:**

Tambah section antara "Catatan" dan "Total":
1. Checkbox `<input type="checkbox" id="use_tax"> Kena Pajak`
2. Dropdown select pajak (hidden by default, muncul saat checkbox dicentang):
   ```html
   <div id="tax-section" style="display:none;">
       <select name="tax_id" id="tax-select" class="form-control">
           <option value="">-- Pilih Pajak --</option>
           @foreach($taxes as $tax)
               <option value="{{ $tax->id }}" data-rate="{{ $tax->rate }}">{{ $tax->name }} ({{ $tax->rate }}%)</option>
           @endforeach
       </select>
   </div>
   ```
3. Display area: Subtotal | Pajak (Rp xxx) | **Grand Total (Rp xxx)**
4. Hidden input `<input type="hidden" name="tax_amount" id="tax-amount-input" value="0">`

**Perubahan jQuery:**
```javascript
// Pada calculateTotal(), tambah:
function calculateTax() {
    const subtotal = /* hitung dari semua .subtotal */;
    let taxAmount = 0;
    if ($('#use_tax').is(':checked')) {
        const rate = parseFloat($('#tax-select option:selected').data('rate')) || 0;
        taxAmount = subtotal * (rate / 100);
    }
    $('#tax-amount').text('Rp ' + taxAmount.toLocaleString('id-ID'));
    $('#tax-amount-input').val(taxAmount);
    const grandTotal = subtotal + taxAmount;
    $('#grand-total').text('Rp ' + grandTotal.toLocaleString('id-ID'));
}

// Event handlers:
$('#use_tax').change(function() { /* toggle #tax-section visibility, recalculate */ });
$('#tax-select').change(function() { calculateTax(); });
```

**Perubahan di PurchaseController::store():**
```php
// Setelah hitung $total:
$taxAmount = 0;
if (!empty($validated['tax_id'])) {
    $tax = Tax::find($validated['tax_id']);
    $taxAmount = $total * ($tax->rate / 100);
}

// Di dalam Purchase::create():
'tax_id' => $validated['tax_id'] ?? null,
'tax_amount' => $taxAmount,
```

**Perubahan di view show:** Tambah baris di tabel detail:
```
Subtotal:  Rp xxx  (dari $purchase->total)
Pajak:     Rp xxx  (dari $purchase->tax_amount, tampilkan nama pajak jika ada)
Grand Total: Rp xxx (total + tax_amount)
```

**Perubahan di view index:** Tambah kolom "Pajak" opsional atau tampilkan grand total.

---

### Task 4: Integrasi Pajak pada Transaksi Penjualan

**File ubah:**
- `app/Http/Controllers/SaleController.php`
  - `create()` / `edit()`: passing `$taxes`
  - `store()`: hitung tax_amount, simpan
  - `update()`: hitung ulang tax_amount
- `app/Http/Requests/StoreSaleRequest.php` — tambah `'tax_id' => ['nullable', 'exists:taxes,id']`
- `app/Http/Requests/UpdateSaleRequest.php` — tambah sama
- `resources/views/sales/create.blade.php`
- `resources/views/sales/edit.blade.php`
- `resources/views/sales/show.blade.php`
- `resources/views/sales/index.blade.php`

Pola sama persis dengan Task 3, hanya beda field harga (`sell_price` vs `buy_price`).

---

### Task 5: Update Invoice PDF

**File ubah:**
- `resources/views/sales/invoice.blade.php`

Ganti bagian summary yang hardcode:
```blade
<tr>
    <td>Pajak</td>
    <td>Rp 0</td>
</tr>
```
Menjadi:
```blade
<tr>
    <td>Pajak {{ $sale->tax ? '(' . $sale->tax->name . ')' : '' }}</td>
    <td>Rp {{ number_format($sale->tax_amount, 0, ',', '.') }}</td>
</tr>
<tr class="total-row">
    <td>Total</td>
    <td>Rp {{ number_format($sale->total + $sale->tax_amount, 0, ',', '.') }}</td>
</tr>
```

---

### Task 6: Seeder & Verifikasi

**File baru:**
- `database/seeders/TaxSeeder.php` — contoh: PPN 11% (rate: 11.00, is_active: true)

**File ubah:**
- `database/seeders/DatabaseSeeder.php` — tambah `TaxSeeder::class`

Jalankan: `php artisan migrate:fresh --seed`

---

## Urutan Pengerjaan

1. **Task 1** (Migration & Model) — foundation, harus dulu
2. **Task 2** (CRUD Tax) — independen, bisa paralel dengan Task 3/4 tapi lebih baik dulu agar ada data pajak
3. **Task 3** (Pembelian) dan **Task 4** (Penjualan) — bisa paralel, pola sama
4. **Task 5** (Invoice PDF) — setelah Task 4 selesai
5. **Task 6** (Seeder) — kapan saja setelah Task 1

---

## Perhitungan Grand Total

**Server-side (di controller):**
```
subtotal = SUM(quantity × price) dari semua item → disimpan di kolom `total`
tax_amount = subtotal × (tax.rate / 100) → disimpan di kolom `tax_amount`
grand_total = subtotal + tax_amount → dihitung saat display (bukan kolom tersendiri)
```

**Client-side (jQuery di view):**
```
subtotal = SUM semua .subtotal input
tax_amount = subtotal × (selected_rate / 100)  [jika checkbox aktif]
grand_total = subtotal + tax_amount
```

---

## Catatan Penting

1. **Kolom `total` tetap menyimpan subtotal items** (tanpa pajak) agar tidak break laporan existing. Grand total = `total + tax_amount` dihitung di view.
2. **Tax_amount dihitung dari subtotal keseluruhan**, bukan per item. Satu pajak per transaksi (bukan per item).
3. **`tax_id` nullable** — transaksi tanpa pajak tetap valid (tax_id = null, tax_amount = 0).
4. **Form Request wajib** sesuai konvensi CLAUDE.md — validasi `tax_id` ada di Form Request, bukan inline di controller.
5. **ActivityLogger** dipanggil di store/update/destroy TaxController.
6. **Sidebar menu** pajak masuk submenu "Master Data" (admin only).
7. **Kasir** tetap bisa melihat pajak di form penjualan (dropdown pajak ditampilkan), tapi hanya admin yang bisa manage data master pajak.

---

## Validasi & Testing

1. `php artisan migrate:fresh --seed` — pastikan migration jalan tanpa error
2. CRUD pajak via browser — create, edit, delete tax
3. Buat transaksi pembelian **tanpa** centang pajak → tax_id = null, tax_amount = 0, total = subtotal
4. Buat transaksi pembelian **dengan** centang pajak PPN 11% → tax_amount = subtotal × 0.11
5. Edit transaksi → ubah pajak atau hilangkan centang → pastikan tax_amount berubah
6. Lakukan hal sama untuk penjualan
7. Cetak invoice PDF → pastikan pajak tampil dengan benar
8. Cek laporan penjualan & laba kotor → pastikan tidak error (total tetap subtotal, pajak terpisah)
9. `php artisan route:list` — pastikan route tax terdaftar

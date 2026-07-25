# Plan: Update Documentation for Implemented Features

## Context
The codebase has 5 implemented features not reflected in AGENTS.md, PRD.md, and SCHEMA.md. All Phase 1, 2, and 3 tasks are completed. The documentation must be brought up to date.

## Undocumented Features

| # | Feature | Evidence |
|---|---------|----------|
| 1 | **Tax Module** | `Tax` model, `TaxController`, `taxes` migration, `tax_id`/`tax_amount` on `purchases` & `sales`, `StoreTaxRequest`/`UpdateTaxRequest`, `TaxSeeder`, `resources/views/taxes/` |
| 2 | **`buy_price` in `sale_items`** | Migration `2026_07_13_000800`, `SaleItem.buy_price` in fillable/casts, used in `SaleController::store()` and profit report |
| 3 | **Dark Mode Toggle** | `HandleDarkMode` middleware, session-based toggle |
| 4 | **Low Stock Notifications** | `NotificationController` (AJAX JSON), route `notifications/low-stock` |
| 5 | **Excel Export** | `SalesReportExport`, `SupplierDebtExport`, routes `reports.sales.export` and `supplier-debts.export` |
| 6 | **Profit Report** | `ReportController::profit()` fully implemented with chart (was "opsional" in PRD) |
| 7 | **Dashboard Debt Widget** | `DashboardController` shows upcoming/overdue supplier debts |

---

## File 1: `SCHEMA.md`

### 1a. Add `taxes` table — new section after `stock_opname_items`

```markdown
## 12. taxes

| Kolom      | Tipe            | Keterangan                |
|------------|-----------------|---------------------------|
| id         | bigIncrements   | PK                        |
| name       | string           | nama pajak (misal: PPN)  |
| rate       | decimal(5,2)     | tarif dalam persen        |
| is_active  | boolean, default true | status aktif/nonaktif |
| timestamps | -                |                           |

Relasi:
- `hasMany(Purchase::class)`
- `hasMany(Sale::class)`
```

### 1b. Update `purchases` table (section 5)
- Add columns: `tax_id` (foreignId → taxes.id, nullable, nullOnDelete), `tax_amount` (decimal 14,2, default 0)
- Add relation: `belongsTo(Tax::class)`
- Add relation: `hasOne(SupplierDebt::class)`

### 1c. Update `sales` table (section 7)
- Add columns: `tax_id` (foreignId → taxes.id, nullable, nullOnDelete), `tax_amount` (decimal 14,2, default 0)
- Add relation: `belongsTo(Tax::class)`

### 1d. Update `sale_items` table (section 8)
- Add column: `buy_price` (decimal 12,2, default 0) after `sell_price`

### 1e. Update migration order
- Insert `taxes` before `purchases` (since purchases references taxes)
- Note alter migrations for tax columns on purchases/sales

### 1f. Renumber sections
- Current `supplier_debts` is section 12 → becomes 13
- Current `supplier_debt_payments` is section 13 → becomes 14
- New `taxes` becomes section 12

---

## File 2: `PRD.md`

### 2a. Add section 3.12 — Manajemen Pajak
- CRUD pajak: nama, tarif (%), status aktif/nonaktif
- Pajak bisa dipilih saat membuat transaksi pembelian/penjualan (opsional per transaksi)
- Tax amount dihitung otomatis dari subtotal × rate dan tersimpan di transaksi
- Hanya admin yang bisa mengelola data pajak

### 2b. Update section 3.4 (Pembelian)
- Tambah: transaksi pembelian mendukung penerapan pajak opsional

### 2c. Update section 3.5 (Penjualan)
- Tambah: transaksi penjualan mendukung penerapan pajak opsional
- Tambah: `buy_price` produk saat transaksi turut disimpan di `sale_items` untuk perhitungan laba

### 2d. Update section 3.6 (Laporan)
- Update "Laporan Laba Kotor" dari opsional menjadi fitur aktif
- Tambah: tersedia export laporan penjualan ke Excel (.xlsx)

### 2e. Update section 3.7 (Dashboard)
- Tambah: notifikasi stok menipis di navbar (bell icon AJAX)
- Tambah: widget utang supplier jatuh tempo

### 2f. Update section 3.11 (Supplier & Utang)
- Tambah: export daftar utang ke Excel

### 2g. Add section 3.13 — Dark Mode
- Toggle dark/light mode di navbar, tersimpan di session

---

## File 3: `AGENTS.md`

### 3a. Update "Struktur Folder Penting"
Add missing directories:
```
app/Services/
app/Exports/
resources/views/taxes/
resources/views/activity-logs/
resources/views/stock-opnames/
resources/views/supplier-debts/
resources/views/users/
resources/views/reports/
```

### 3b. Update "Aturan Bisnis Penting"
- Add rule 10: Pajak bersifat opsional per transaksi. Jika dipilih, `tax_amount` dihitung dari `subtotal × rate / 100` dan disimpan bersama transaksi.
- Add rule 11: `buy_price` wajib disimpan di `sale_items` saat transaksi penjualan (diambil dari `products.buy_price` saat itu) untuk menjaga akurasi laporan laba kotor.

### 3c. Update "Role User"
- Update admin description to include "kelola pajak"

### 3d. Update "Catatan untuk Claude Code"
- Add reference to `TASKS_PHASE3.md` (currently only mentions TASKS.md and TASKS_PHASE2.md)

### 3e. Update "Tech Stack"
- Add `maatwebsite/excel` for Excel export

---

## Verification
- Cross-check column names in SCHEMA.md against actual migration files
- Ensure business rules in AGENTS.md match actual controller logic
- Ensure PRD.md feature descriptions match actual implementation scope

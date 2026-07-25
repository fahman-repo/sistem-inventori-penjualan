# SCHEMA.md
# Skema Database — Sistem Inventori & Penjualan

Catatan: semua tabel menggunakan `id` (bigIncrements) sebagai primary key
dan `timestamps()` (`created_at`, `updated_at`) kecuali disebutkan lain.

---

## 1. users
*(hasil generate Laravel Breeze, ditambah kolom `role`)*

| Kolom      | Tipe                              | Keterangan                     |
|------------|------------------------------------|---------------------------------|
| id         | bigIncrements                      | PK                              |
| name       | string                              |                                  |
| email      | string, unique                     |                                  |
| password   | string                              |                                  |
| role       | enum('admin', 'kasir') default 'kasir' | menentukan hak akses       |
| timestamps | -                                   |                                  |

---

## 2. categories

| Kolom      | Tipe            | Keterangan   |
|------------|-----------------|--------------|
| id         | bigIncrements   | PK           |
| name       | string          | unique       |
| timestamps | -               |              |

---

## 3. products

| Kolom        | Tipe                          | Keterangan                          |
|--------------|-------------------------------|--------------------------------------|
| id           | bigIncrements                 | PK                                    |
| category_id  | foreignId → categories.id, nullable | on delete set null              |
| name         | string                         |                                        |
| sku          | string, unique                 | kode barang                           |
| unit         | string, default 'pcs'          | satuan (pcs, box, kg, dll)            |
| buy_price    | decimal(12,2), default 0       | harga beli terkini (acuan default)    |
| sell_price   | decimal(12,2), default 0       | harga jual terkini (acuan default)    |
| stock        | integer, default 0             | stok saat ini, tidak boleh negatif    |
| min_stock    | integer, default 10            | ambang batas "stok menipis"           |
| timestamps   | -                               |                                        |

Relasi:
- `belongsTo(Category::class)`
- `hasMany(PurchaseItem::class)`
- `hasMany(SaleItem::class)`

---

## 4. suppliers

| Kolom   | Tipe             | Keterangan |
|---------|------------------|------------|
| id      | bigIncrements    | PK         |
| name    | string           |            |
| phone   | string, nullable |            |
| email   | string, nullable |            |
| address | text, nullable   |            |
| timestamps | -           |            |

---

## 5. purchases *(header pembelian)*

| Kolom          | Tipe                              | Keterangan                        |
|----------------|-------------------------------------|-------------------------------------|
| id             | bigIncrements                       | PK                                   |
| invoice_number | string, unique                      | contoh: PO-20260712-0001             |
| user_id        | foreignId → users.id                | siapa yang input                     |
| supplier_id    | foreignId → suppliers.id, nullable  | opsional                             |
| purchase_date  | date                                 |                                       |
| total          | decimal(14,2), default 0            | total keseluruhan (dihitung dari items) |
| tax_id         | foreignId → taxes.id, nullable      | on delete null                       |
| tax_amount     | decimal(14,2), default 0            | pajak yang dikenakan                 |
| notes          | text, nullable                      |                                       |
| payment_status | enum('cash','credit'), default 'cash'| cash = lunas, credit = utang        |
| timestamps     | -                                    |                                       |

Relasi:
- `belongsTo(User::class)`
- `belongsTo(Supplier::class)`
- `belongsTo(Tax::class)`
- `hasMany(PurchaseItem::class)`
- `hasOne(SupplierDebt::class)`

---

## 6. purchase_items *(detail pembelian)*

| Kolom        | Tipe                             | Keterangan                                  |
|--------------|------------------------------------|------------------------------------------------|
| id           | bigIncrements                      | PK                                              |
| purchase_id  | foreignId → purchases.id, cascade  |                                                  |
| product_id   | foreignId → products.id            |                                                  |
| quantity     | integer                            |                                                  |
| buy_price    | decimal(12,2)                      | harga beli SAAT transaksi ini (bukan ambil dari products.buy_price saat ditampilkan ulang) |
| subtotal     | decimal(14,2)                      | quantity × buy_price                            |
| timestamps   | -                                   |                                                  |

Relasi:
- `belongsTo(Purchase::class)`
- `belongsTo(Product::class)`

---

## 7. sales *(header penjualan)*

| Kolom          | Tipe                              | Keterangan                          |
|----------------|-------------------------------------|---------------------------------------|
| id             | bigIncrements                       | PK                                     |
| invoice_number | string, unique                      | contoh: INV-20260712-0001              |
| user_id        | foreignId → users.id                | kasir yang melayani                    |
| sale_date      | date                                 |                                         |
| total          | decimal(14,2), default 0            | total keseluruhan (dihitung dari items)|
| tax_id         | foreignId → taxes.id, nullable      | on delete null                         |
| tax_amount     | decimal(14,2), default 0            | pajak yang dikenakan                   |
| notes          | text, nullable                      |                                         |
| timestamps     | -                                    |                                         |

Relasi:
- `belongsTo(User::class)`
- `belongsTo(Tax::class)`
- `hasMany(SaleItem::class)`

---

## 8. sale_items *(detail penjualan)*

| Kolom       | Tipe                             | Keterangan                                     |
|-------------|------------------------------------|--------------------------------------------------|
| id          | bigIncrements                      | PK                                                 |
| sale_id     | foreignId → sales.id, cascade      |                                                    |
| product_id  | foreignId → products.id            |                                                    |
| quantity    | integer                            |                                                    |
| buy_price   | decimal(12,2), default 0           | harga beli SAAT transaksi (untuk laporan laba)     |
| sell_price  | decimal(12,2)                      | harga jual SAAT transaksi ini                      |
| subtotal    | decimal(14,2)                      | quantity × sell_price                              |
| timestamps  | -                                   |                                                    |

Relasi:
- `belongsTo(Sale::class)`
- `belongsTo(Product::class)`

---

## Diagram Relasi (ringkas, teks)

```
users 1---N purchases
users 1---N sales
categories 1---N products
suppliers 1---N purchases
taxes 1---N purchases
taxes 1---N sales

purchases 1---N purchase_items N---1 products
sales     1---N sale_items     N---1 products
purchases 1---1 supplier_debts 1---N supplier_debt_payments
```

## 9. activity_logs *(Audit Trail)*

| Kolom       | Tipe                              | Keterangan                                    |
|-------------|--------------------------------------|--------------------------------------------------|
| id          | bigIncrements                        | PK                                                 |
| user_id     | foreignId → users.id                 | siapa yang melakukan aksi                          |
| action      | string                                | contoh: 'create', 'update', 'delete', 'stock_opname' |
| model_type  | string                                | nama model terkait, contoh: 'Product'              |
| model_id    | bigInteger                            | id record yang diubah                              |
| description | text, nullable                       | ringkasan perubahan                                |
| old_values  | json, nullable                       | data sebelum perubahan                             |
| new_values  | json, nullable                       | data sesudah perubahan                             |
| timestamps  | -                                     |                                                     |

Relasi:
- `belongsTo(User::class)`

---

## 10. stock_opnames *(header)*

| Kolom         | Tipe                        | Keterangan                        |
|---------------|-------------------------------|--------------------------------------|
| id            | bigIncrements                 | PK                                     |
| opname_number | string, unique                | contoh: SO-20260712-0001               |
| user_id       | foreignId → users.id          | siapa yang melakukan opname            |
| opname_date   | date                            |                                         |
| notes         | text, nullable                 |                                         |
| timestamps    | -                               |                                         |

Relasi:
- `belongsTo(User::class)`
- `hasMany(StockOpnameItem::class)`

---

## 11. stock_opname_items *(detail)*

| Kolom          | Tipe                                | Keterangan                                  |
|----------------|----------------------------------------|--------------------------------------------------|
| id             | bigIncrements                          | PK                                                 |
| stock_opname_id| foreignId → stock_opnames.id, cascade  |                                                    |
| product_id     | foreignId → products.id                |                                                    |
| system_stock   | integer                                 | stok menurut sistem saat opname dibuat            |
| physical_stock | integer                                 | hasil hitung fisik                                |
| difference     | integer                                 | physical_stock - system_stock                     |
| timestamps     | -                                        |                                                    |

Relasi:
- `belongsTo(StockOpname::class)`
- `belongsTo(Product::class)`

---

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

---

## 13. supplier_debts *(utang ke supplier)*

| Kolom        | Tipe                                  | Keterangan                                    |
|--------------|------------------------------------------|--------------------------------------------------|
| id           | bigIncrements                            | PK                                                 |
| purchase_id  | foreignId → purchases.id, cascade        | utang muncul dari transaksi pembelian ini          |
| supplier_id  | foreignId → suppliers.id                 |                                                    |
| total_amount | decimal(14,2)                            | total utang (biasanya = purchases.total)           |
| paid_amount  | decimal(14,2), default 0                 | total yang sudah dibayar                           |
| due_date     | date, nullable                            | jatuh tempo                                        |
| status       | enum('unpaid','partial','paid'), default 'unpaid' | dihitung ulang tiap kali ada pembayaran |
| timestamps   | -                                          |                                                    |

Relasi:
- `belongsTo(Purchase::class)`
- `belongsTo(Supplier::class)`
- `hasMany(SupplierDebtPayment::class)`

---

## 14. supplier_debt_payments *(riwayat cicilan pembayaran utang)*

| Kolom            | Tipe                                    | Keterangan          |
|-------------------|--------------------------------------------|-----------------------|
| id                | bigIncrements                               | PK                     |
| supplier_debt_id  | foreignId → supplier_debts.id, cascade      |                        |
| user_id           | foreignId → users.id                        | siapa yang mencatat pembayaran |
| amount            | decimal(14,2)                               | jumlah dibayar kali ini |
| payment_date      | date                                          |                        |
| notes             | text, nullable                              |                        |
| timestamps        | -                                             |                        |

Relasi:
- `belongsTo(SupplierDebt::class)`
- `belongsTo(User::class)`

---

## Perubahan pada tabel yang sudah ada

**purchases** — kolom `payment_status`, `tax_id`, `tax_amount` ditambahkan via migration alter.
**sales** — kolom `tax_id`, `tax_amount` ditambahkan via migration alter.
**sale_items** — kolom `buy_price` ditambahkan via migration alter (untuk laporan laba kotor).

---

## Urutan Migration yang Disarankan
1. `categories`
2. `products` (bergantung ke categories)
3. `suppliers`
4. `taxes`
5. `purchases` (bergantung ke users, suppliers, taxes)
6. `purchase_items` (bergantung ke purchases, products)
7. `sales` (bergantung ke users, taxes)
8. `sale_items` (bergantung ke sales, products)

**Fase 2:**
9. `activity_logs` (bergantung ke users)
10. `stock_opnames` (bergantung ke users)
11. `stock_opname_items` (bergantung ke stock_opnames, products)

**Fase 3:**
12. migration alter: tambah `email` ke `suppliers`, tambah `payment_status` ke `purchases`
13. migration alter: tambah `tax_id` + `tax_amount` ke `purchases` dan `sales`, tambah `buy_price` ke `sale_items`
14. `supplier_debts` (bergantung ke purchases, suppliers)
15. `supplier_debt_payments` (bergantung ke supplier_debts, users)

## Contoh Seeder Awal (untuk testing)
- 3-5 kategori (Makanan, Minuman, ATK, dll)
- 15-20 produk dengan stok bervariasi (termasuk beberapa yang stoknya di bawah `min_stock` untuk testing indikator stok menipis)
- 1 user admin, 1-2 user kasir
- 1-2 pajak contoh (PPN 11%, PPh 2%)
- 3-5 supplier contoh

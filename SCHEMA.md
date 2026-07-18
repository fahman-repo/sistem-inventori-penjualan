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

## 4. suppliers *(opsional, buat jika waktu cukup)*

| Kolom   | Tipe          | Keterangan |
|---------|---------------|------------|
| id      | bigIncrements | PK         |
| name    | string        |            |
| phone   | string, nullable |         |
| address | text, nullable |           |
| timestamps | -          |            |

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
| notes          | text, nullable                      |                                       |
| timestamps     | -                                    |                                       |

Relasi:
- `belongsTo(User::class)`
- `belongsTo(Supplier::class)`
- `hasMany(PurchaseItem::class)`

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
| notes          | text, nullable                      |                                         |
| timestamps     | -                                    |                                         |

Relasi:
- `belongsTo(User::class)`
- `hasMany(SaleItem::class)`

---

## 8. sale_items *(detail penjualan)*

| Kolom       | Tipe                             | Keterangan                                     |
|-------------|------------------------------------|--------------------------------------------------|
| id          | bigIncrements                      | PK                                                 |
| sale_id     | foreignId → sales.id, cascade      |                                                    |
| product_id  | foreignId → products.id            |                                                    |
| quantity    | integer                            |                                                    |
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
suppliers 1---N purchases (opsional)

purchases 1---N purchase_items N---1 products
sales     1---N sale_items     N---1 products
```

## Urutan Migration yang Disarankan
1. `categories`
2. `products` (bergantung ke categories)
3. `suppliers` (opsional)
4. `purchases` (bergantung ke users, suppliers)
5. `purchase_items` (bergantung ke purchases, products)
6. `sales` (bergantung ke users)
7. `sale_items` (bergantung ke sales, products)

## Contoh Seeder Awal (untuk testing)
- 3-5 kategori (Makanan, Minuman, ATK, dll)
- 15-20 produk dengan stok bervariasi (termasuk beberapa yang stoknya di bawah `min_stock` untuk testing indikator stok menipis)
- 1 user admin, 1-2 user kasir

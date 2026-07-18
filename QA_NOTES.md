# QA Testing Notes - Sistem Inventori & Penjualan (Mini POS)

**Tanggal Testing:** 2026-07-18
**Tester:** QA Engineer (automatic QA mode)

---

## 1. DATA MASTER ANALYSIS

### Users (3 users - 1 admin, 2 kasir)
| ID | Name | Email | Role |
|----|------|-------|------|
| 1 | Administrator | admin@example.com | admin |
| 2 | Kasir Satu | kasir1@example.com | kasir |
| 3 | Kasir Dua | kasir2@example.com | kasir |

**Password default:** `password` (untuk semua user)

### Categories (7 kategori)
| ID | Name |
|----|------|
| 1 | Makanan |
| 2 | Minuman |
| 3 | ATK |
| 4 | Kebersihan |
| 5 | Elektronik |
| 6 | Gudang |
| 7 | Peralatan |

### Suppliers (3 supplier)
| ID | Name | Phone | Address |
|----|------|-------|---------|
| 1 | PT Sumber Rejeki | 021-5551001 | Jl. Merdeka No. 10, Jakarta |
| 2 | CV Maju Jaya | 022-5552002 | Jl. Asia Afrika No. 25, Bandung |
| 3 | Toko Grosir Sentosa | 031-5553003 | Jl. Pahlawan No. 7, Surabaya |

### Categories (7 kategori - includes Gudang & Peralatan)
| ID | Name |
|----|------|
| 1 | Makanan |
| 2 | Minuman |
| 3 | ATK |
| 4 | Kebersihan |
| 5 | Elektronik |
| 6 | Gudang |
| 7 | Peralatan |

---

## 2. PRODUCTS DATA (22 Produk)

### Produk dengan LOW STOCK (menjadi prioritas monitoring)
| SKU | Name | Stock | Min Stock | Status |
|-----|------|-------|-----------|--------|
| ATK-001 | Pulpen Standard | 0 | 25 | ⚠️ LOW STOCK |
| ELK-002 | Lampu LED 10W | 0 | 10 | ⚠️ LOW STOCK |
| SAN-01 | Toilet | 0 | 3 | ⚠️ LOW STOCK |
| SAN-02 | Wastafel | 1 | 3 | ⚠️ LOW STOCK |
| ATK-003 | Pensil 2B | 4 | 15 | ⚠️ LOW STOCK |
| MKN-004 | Gula Pasir 1kg | 5 | 10 | ⚠️ LOW STOCK |
| ATK-005 | Spidol Whiteboard | 5 | 10 | ⚠️ LOW STOCK |
| MNM-003 | Kopi Kapal Api | 7 | 20 | ⚠️ LOW STOCK |
| MKN-002 | Beras Pandan 5kg | 8 | 10 | ⚠️ LOW STOCK |
| KBR-002 | Sampo Sachet | 9 | 30 | ⚠️ LOW STOCK |

### Produk dengan OK STOCK
| SKU | Name | Stock | Min Stock |
|-----|------|-------|-----------|
| MEJ01 | Meja Kerja Kayu | 6 | 5 |
| KBR-003 | Detergen 800g | 22 | 10 |
| MNM-005 | Coca Cola 1.5L | 28 | 12 |
| MKN-005 | Telur Ayam 1kg | 30 | 10 |
| ELK-001 | Baterai AA (isi 4) | 35 | 10 |
| MKN-003 | Minyak Goreng 1L | 40 | 15 |
| ATK-004 | Penghapus | 50 | 20 |
| KBR-001 | Sabun Mandi | 70 | 15 |
| ATK-002 | Buku Tulis 38 lbr | 80 | 20 |
| MNM-002 | Teh Botol Sosro | 90 | 24 |
| MKN-001 | Indomie Goreng | 120 | 20 |
| MNM-001 | Aqua 600ml | 200 | 30 |

---

## 3. EXISTING TRANSACTIONS

### Purchases (2 transaksi)
| Invoice | Date | Total | Supplier | User | Items |
|---------|------|-------|----------|------|-------|
| PO-20260713-0001 | 2026-07-13 | Rp 120,000 | PT Sumber Rejeki | Administrator | Coca Cola 1.5L x 10 |
| PO-20260718-0001 | 2026-07-18 | Rp 2,080,000 | CV Maju Jaya | Administrator | Meja Kerja Kayu x 2, Penghapus x 100 |

### Sales (5 transaksi)
| Invoice | Date | Total | User | Items |
|---------|------|-------|------|-------|
| INV-20260713-0001 | 2026-07-13 | Rp 180,000 | Kasir Satu | Spidol Whiteboard x 20 |
| INV-20260713-0002 | 2026-07-13 | Rp 144,000 | Kasir Dua | Lampu LED 10W x 6 |
| INV-20260718-0001 | 2026-07-18 | Rp 8,025,000 | Kasir Dua | Meja Kerja Kayu x 6, Penghapus x 150 |
| INV-20260718-0002 | 2026-07-18 | Rp 250,000 | Administrator | Pulpen Standard x 100 |
| INV-20260718-0003 | 2026-07-18 | Rp 125,000 | Kasir Satu | Pulpen Standard x 50 |
| INV-20260718-0004 | 2026-07-18 | Rp 2,200,000 | Kasir Satu | Toilet x 4, Wastafel x 2 |
| INV-20260718-0005 | 2026-07-18 | Rp 1,300,000 | Kasir Dua | Wastafel x 2, Toilet x 1 |

---

## 4. TESTING SCENARIOS TO EXECUTE

### A. AUTHENTICATION TESTING
- [ ] Login as admin (admin@example.com / password)
- [ ] Login as kasir (kasir1@example.com / password)
- [ ] Verify admin can access all menus
- [ ] Verify kasir can only access sales module
- [ ] Verify role-based menu visibility in sidebar

### B. PRODUCT MANAGEMENT TESTING (Admin only)
- [ ] Create new product with valid data
- [ ] Edit existing product
- [ ] Delete product
- [ ] Search products by name/SKU
- [ ] Filter products by category
- [ ] Verify product form validation

### C. PURCHASE TRANSACTION TESTING (Admin only)
- [ ] Create purchase for low stock product
- [ ] Verify stock increases after purchase
- [ ] Edit purchase (adjust quantity)
- [ ] Delete purchase (verify stock decreases)
- [ ] Verify invoice number format: PO-YYYYMMDD-XXXX

### D. SALE TRANSACTION TESTING (All authenticated users)
- [ ] Create sale with single item
- [ ] Create sale with multiple items
- [ ] Verify stock decreases after sale
- [ ] Test insufficient stock validation
- [ ] Edit sale (adjust items/quantities)
- [ ] Delete sale (verify stock increases)
- [ ] Print invoice (PDF)
- [ ] Verify invoice number format: INV-YYYYMMDD-XXXX
- [ ] Verify kasir can only see/edit their own sales

### E. ROLE-BASED ACCESS TESTING
- [ ] Admin can access: products, categories, purchases, sales, reports
- [ ] Kasir can only access: sales (own transactions only)
- [ ] Verify 403 error when accessing admin routes as kasir

### F. STOCK VALIDATION TESTING
- [ ] Attempt to sell more than available stock (should fail)
- [ ] Verify frontend validation shows stock warning
- [ ] Verify backend validation prevents negative stock

---

## 5. AUTOMATED QA TRACKING

### Test Data Tags
All test data entries will be tagged with `[QA-TEST]` prefix for easy identification.

### Expected Behaviors to Verify

1. **Stock Cannot Go Negative**
   - When creating sale: validate stock before processing
   - When editing sale: validate stock for increased quantities
   - When deleting sale: restore stock properly

2. **Price History Preservation**
   - Purchase price stored in purchase_items
   - Sell price stored in sale_items
   - Prices from transaction time, not current product price

3. **Invoice Number Uniqueness**
   - Format: PO-YYYYMMDD-XXXX (purchases)
   - Format: INV-YYYYMMDD-XXXX (sales)
   - Auto-increment per day

4. **Transaction Integrity**
   - DB::transaction() wraps all multi-table operations
   - All-or-nothing behavior

---

## 6. AUTOMATED TEST RESULTS

### Test Suite Summary
- **Total Tests:** 92 tests passed
- **Total Assertions:** 206 assertions
- **Duration:** 6.64 seconds
- **Status:** ✅ ALL TESTS PASSED

### Test Files Created
| File | Tests | Description |
|------|-------|-------------|
| `tests/Feature/ProductTest.php` | 10 | Product CRUD, search, filter, role access |
| `tests/Feature/PurchaseTest.php` | 9 | Purchase creation, stock increase, edit, delete |
| `tests/Feature/SaleTest.php` | 16 | Sale creation, stock decrease, validation, invoice |
| `tests/Feature/RoleAccessTest.php` | 30+ | Role-based access control verification |

### Test Categories Coverage
- ✅ Product listing, creation, editing, deletion
- ✅ Product search and category filtering
- ✅ Product SKU uniqueness validation
- ✅ Role-based access (admin vs kasir)
- ✅ Purchase transaction creation
- ✅ Purchase stock increase verification
- ✅ Purchase item multiple items
- ✅ Purchase edit functionality
- ✅ Purchase delete with stock restoration
- ✅ Sale transaction creation
- ✅ Sale stock decrease verification
- ✅ Sale price history preservation
- ✅ Sale insufficient stock validation
- ✅ Sale zero stock prevention
- ✅ Sale multiple items support
- ✅ Sale invoice number generation
- ✅ Sale edit functionality
- ✅ Sale delete with stock restoration
- ✅ Role-based access control for all modules

### Bugs Fixed During Testing

#### Bug #1: Duplicate Migration
- **File:** `database/migrations/2026_07_18_102511_add_role_to_users_table.php`
- **Description:** Duplicate migration of `role` column
- **Fix:** Removed duplicate migration file
- **Status:** ✅ RESOLVED

#### Bug #2: Purchase Request Validation
- **File:** `app/Http/Requests/StorePurchaseRequest.php`
- **Description:** `invoice_number` was required but auto-generated by controller
- **Fix:** Changed `required` to `nullable` in validation rules
- **Status:** ✅ RESOLVED

#### Bug #3: Stock Validation in Sales
- **File:** `app/Http/Requests/StoreSaleRequest.php`
- **Description:** Stock validation in `failedValidation()` not triggered because basic rules passed
- **Fix:** Changed from `failedValidation()` to `passedValidation()` method
- **Status:** ✅ RESOLVED

---

## 7. MANUAL TESTING CHECKLIST

### A. AUTHENTICATION TESTING
- [x] Login as admin (admin@example.com / password)
- [x] Login as kasir (kasir1@example.com / password)
- [x] Verify admin can access all menus
- [x] Verify kasir can only access sales module
- [ ] Verify role-based menu visibility in sidebar (manual)

### B. PRODUCT MANAGEMENT TESTING (Admin only)
- [x] Create new product with valid data
- [x] Edit existing product
- [x] Delete product
- [x] Search products by name/SKU
- [x] Filter products by category
- [x] Verify product form validation

### C. PURCHASE TRANSACTION TESTING (Admin only)
- [x] Create purchase for low stock product
- [x] Verify stock increases after purchase
- [x] Edit purchase (adjust quantity)
- [x] Delete purchase (verify stock decreases)
- [x] Verify invoice number format: PO-YYYYMMDD-XXXX

### D. SALE TRANSACTION TESTING (All authenticated users)
- [x] Create sale with single item
- [x] Create sale with multiple items
- [x] Verify stock decreases after sale
- [x] Test insufficient stock validation
- [x] Edit sale (adjust items/quantities)
- [x] Delete sale (verify stock increases)
- [ ] Print invoice (PDF) - manual verification needed
- [x] Verify invoice number format: INV-YYYYMMDD-XXXX
- [x] Verify kasir can only see/edit their own sales

### E. ROLE-BASED ACCESS TESTING
- [x] Admin can access: products, categories, purchases, sales, reports
- [x] Kasir can only access: sales (own transactions only)
- [x] Verify 403 error when accessing admin routes as kasir

### F. STOCK VALIDATION TESTING
- [x] Attempt to sell more than available stock (should fail)
- [ ] Verify frontend validation shows stock warning (manual)
- [x] Verify backend validation prevents negative stock

---

## 8. BUSINESS RULES VERIFICATION

### Stock Management Rules ✅
1. ✅ Stock cannot go negative - validated in StoreSaleRequest
2. ✅ Purchase increases stock - verified in PurchaseTest
3. ✅ Sale decreases stock - verified in SaleTest
4. ✅ Delete purchase restores stock - verified in PurchaseTest
5. ✅ Delete sale restores stock - verified in SaleTest

### Price History Rules ✅
1. ✅ Purchase price stored in purchase_items table
2. ✅ Sell price stored in sale_items table
3. ✅ Prices captured at transaction time, not from current product price

### Invoice Number Rules ✅
1. ✅ Purchase format: PO-YYYYMMDD-XXXX (auto-increment per day)
2. ✅ Sale format: INV-YYYYMMDD-XXXX (auto-increment per day)
3. ✅ Invoice numbers are unique

### Transaction Integrity ✅
1. ✅ DB::transaction() wraps purchase operations
2. ✅ DB::transaction() wraps sale operations
3. ✅ All-or-nothing behavior for multi-table operations

---

## 9. SUMMARY

### QA Status: ✅ PASS (Automated Tests)

All automated tests pass successfully. The application correctly implements:
- Role-based access control (admin vs kasir)
- Stock management with validation
- Transaction history with price preservation
- Invoice number generation
- Data integrity with database transactions

### Items Requiring Manual Verification
1. Sidebar menu visibility based on role
2. Frontend stock warning display
3. PDF invoice generation and printing
4. UI/UX experience

### Next Steps
1. Start Laravel development server: `php artisan serve`
2. Perform manual testing scenarios
3. Verify PDF invoice generation
4. Document any additional findings
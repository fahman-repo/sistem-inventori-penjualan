# Plan: Auto-calculate Due Date (60 days) for Credit Purchases

## Context
When an admin creates a purchase with `payment_status = 'credit'`, the system should auto-calculate `due_date = purchase_date + 60 days` and store it in `supplier_debts`. Currently the due_date is always `null` because:
1. The create form has no `due_date` input field
2. The controller passes `$validated['due_date'] ?? null` which is always null
3. No automatic calculation logic exists

The `supplier_debts` table, model, and views already support `due_date` — they just need data.

## Changes

### 1. `app/Http/Controllers/PurchaseController.php` — store() method (line ~107-116)
Replace `'due_date' => $validated['due_date'] ?? null` with automatic calculation:
```php
'due_date' => \Carbon\Carbon::parse($validated['purchase_date'])->addDays(60),
```
This makes the 60-day rule server-enforced and not dependent on client input.

### 2. `resources/views/purchases/create.blade.php` — Add due date info display
After the payment status radio buttons, add a read-only info section that:
- Is hidden by default
- Shows when "Credit" radio is selected
- Displays the calculated due date (`purchase_date + 60 days`) using jQuery
- Updates dynamically if `purchase_date` changes while credit is selected
- Uses Bootstrap `alert-info` with a calendar icon

### 3. `resources/views/purchases/show.blade.php` — Show due date for credit purchases
After the "Status Pembayaran" row (line ~43), add a conditional row:
- Only shown when `payment_status == 'credit'` and `supplierDebt` exists
- Displays the due date from `$purchase->supplierDebt->due_date`
- Shows "Tidak ada data utang" if the debt record is missing (edge case)

### 4. `resources/views/purchases/edit.blade.php` — Show due date info for credit
Add the same jQuery-driven due date preview in the edit form, pre-populated with the existing due date from the supplier debt if available.

### 5. `app/Http/Controllers/PurchaseController.php` — update() method
When updating a purchase that has an associated `SupplierDebt` and `purchase_date` changes, recalculate the due_date:
```php
if ($purchase->supplierDebt && $validated['payment_status'] === 'credit') {
    $purchase->supplierDebt->update([
        'due_date' => \Carbon\Carbon::parse($validated['purchase_date'])->addDays(60),
    ]);
}
```

### 6. Tests — `tests/Feature/PurchaseTest.php`
Add new test methods:
- `test_credit_purchase_auto_sets_due_date_to_60_days()` — Creates a credit purchase via POST, asserts `supplier_debts.due_date == purchase_date + 60 days`
- `test_cash_purchase_does_not_create_supplier_debt()` — Creates a cash purchase, asserts no `supplier_debt` record exists
- `test_due_date_displayed_on_supplier_debts_index()` — Creates a credit purchase, visits supplier-debts.index, asserts the formatted due date appears in the response
- `test_due_date_displayed_on_supplier_debt_detail()` — Creates a credit purchase, visits supplier-debts.show, asserts the formatted due date appears

## Files to modify
1. `app/Http/Controllers/PurchaseController.php`
2. `resources/views/purchases/create.blade.php`
3. `resources/views/purchases/show.blade.php`
4. `resources/views/purchases/edit.blade.php`
5. `tests/Feature/PurchaseTest.php`

## Validation
1. Run `php artisan test --filter=PurchaseTest` to verify new and existing tests pass
2. Run `php artisan test --filter=SupplierDebtTest` to verify no regressions
3. Manual verification: create a credit purchase, check supplier-debts index shows correct due date

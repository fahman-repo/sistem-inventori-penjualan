<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierDebt;
use App\Models\SupplierDebtPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierDebtTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $kasir;
    private Supplier $supplier;
    private SupplierDebt $debt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin-debt@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->kasir = User::create([
            'name' => 'Test Kasir',
            'email' => 'kasir-debt@example.com',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        $category = Category::create(['name' => 'Makanan']);

        $this->supplier = Supplier::create([
            'name' => 'PT Debt Supplier',
            'phone' => '021-5551111',
            'address' => 'Jl. Debt No. 1',
            'email' => 'debt@supplier.com',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Debt Product',
            'sku' => 'DEBT-PROD-001',
            'unit' => 'pcs',
            'buy_price' => 5000,
            'sell_price' => 7500,
            'stock' => 10,
            'min_stock' => 5,
        ]);

        $purchase = Purchase::create([
            'invoice_number' => 'PO-DEBT-001',
            'user_id' => $this->admin->id,
            'supplier_id' => $this->supplier->id,
            'purchase_date' => '2026-07-21',
            'total' => 100000,
            'payment_status' => 'credit',
        ]);

        $this->debt = SupplierDebt::create([
            'purchase_id' => $purchase->id,
            'supplier_id' => $this->supplier->id,
            'total_amount' => 100000,
            'paid_amount' => 0,
            'due_date' => '2026-08-21',
            'status' => 'unpaid',
        ]);
    }

    // ==================== DEBT LISTING TESTS ====================

    public function test_debt_index_can_be_rendered(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/supplier-debts');

        $response->assertStatus(200)
            ->assertSee('PT Debt Supplier')
            ->assertSee('100.000');
    }

    public function test_kasir_cannot_access_debts(): void
    {
        $response = $this->actingAs($this->kasir)
            ->get('/admin/supplier-debts');

        $response->assertStatus(403);
    }

    public function test_debt_index_can_be_filtered_by_status(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/supplier-debts?status=unpaid');

        $response->assertStatus(200)
            ->assertSee('PT Debt Supplier');
    }

    public function test_debt_index_can_be_filtered_by_supplier(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/supplier-debts?supplier_id=' . $this->supplier->id);

        $response->assertStatus(200)
            ->assertSee('PT Debt Supplier');
    }

    // ==================== DEBT DETAIL TESTS ====================

    public function test_debt_show_displays_debt_details(): void
    {
        $response = $this->actingAs($this->admin)
            ->get("/admin/supplier-debts/{$this->debt->id}");

        $response->assertStatus(200)
            ->assertSee('PT Debt Supplier')
            ->assertSee('PO-DEBT-001')
            ->assertSee('100.000');
    }

    public function test_debt_show_displays_payment_history(): void
    {
        SupplierDebtPayment::create([
            'supplier_debt_id' => $this->debt->id,
            'user_id' => $this->admin->id,
            'amount' => 50000,
            'payment_date' => '2026-07-25',
            'notes' => 'Pembayaran pertama',
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/supplier-debts/{$this->debt->id}");

        $response->assertStatus(200)
            ->assertSee('50.000')
            ->assertSee('Pembayaran pertama');
    }

    // ==================== DEBT PAYMENT TESTS ====================

    public function test_partial_payment_updates_debt_status_to_partial(): void
    {
        $response = $this->actingAs($this->admin)
            ->post("/admin/supplier-debts/{$this->debt->id}/payments", [
                'amount' => 30000,
                'payment_date' => '2026-07-25',
                'notes' => 'Pembayaran sebagian',
            ]);

        $response->assertRedirect("/admin/supplier-debts/{$this->debt->id}")
            ->assertSessionHas('success');

        $this->debt->refresh();
        $this->assertEquals(30000, $this->debt->paid_amount);
        $this->assertEquals('partial', $this->debt->status);
    }

    public function test_full_payment_updates_debt_status_to_paid(): void
    {
        $response = $this->actingAs($this->admin)
            ->post("/admin/supplier-debts/{$this->debt->id}/payments", [
                'amount' => 100000,
                'payment_date' => '2026-07-25',
                'notes' => 'Pembayaran lunas',
            ]);

        $response->assertRedirect("/admin/supplier-debts/{$this->debt->id}")
            ->assertSessionHas('success');

        $this->debt->refresh();
        $this->assertEquals(100000, $this->debt->paid_amount);
        $this->assertEquals('paid', $this->debt->status);
    }

    public function test_over_payment_still_marks_debt_as_paid(): void
    {
        $response = $this->actingAs($this->admin)
            ->post("/admin/supplier-debts/{$this->debt->id}/payments", [
                'amount' => 150000,
                'payment_date' => '2026-07-25',
                'notes' => 'Kelebihan bayar',
            ]);

        $response->assertRedirect("/admin/supplier-debts/{$this->debt->id}")
            ->assertSessionHas('success');

        $this->debt->refresh();
        $this->assertEquals(150000, $this->debt->paid_amount);
        $this->assertEquals('paid', $this->debt->status);
    }

    public function test_multiple_payments_cumulate_correctly(): void
    {
        // First payment: 30,000
        $this->actingAs($this->admin)
            ->post("/admin/supplier-debts/{$this->debt->id}/payments", [
                'amount' => 30000,
                'payment_date' => '2026-07-25',
            ]);

        $this->debt->refresh();
        $this->assertEquals(30000, $this->debt->paid_amount);
        $this->assertEquals('partial', $this->debt->status);

        // Second payment: 20,000
        $this->actingAs($this->admin)
            ->post("/admin/supplier-debts/{$this->debt->id}/payments", [
                'amount' => 20000,
                'payment_date' => '2026-08-01',
            ]);

        $this->debt->refresh();
        $this->assertEquals(50000, $this->debt->paid_amount);
        $this->assertEquals('partial', $this->debt->status);

        // Final payment: 50,000
        $this->actingAs($this->admin)
            ->post("/admin/supplier-debts/{$this->debt->id}/payments", [
                'amount' => 50000,
                'payment_date' => '2026-08-10',
            ]);

        $this->debt->refresh();
        $this->assertEquals(100000, $this->debt->paid_amount);
        $this->assertEquals('paid', $this->debt->status);
    }

    public function test_payment_amount_is_required(): void
    {
        $response = $this->actingAs($this->admin)
            ->post("/admin/supplier-debts/{$this->debt->id}/payments", [
                'amount' => '',
                'payment_date' => '2026-07-25',
            ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_payment_amount_must_be_positive(): void
    {
        $response = $this->actingAs($this->admin)
            ->post("/admin/supplier-debts/{$this->debt->id}/payments", [
                'amount' => -1000,
                'payment_date' => '2026-07-25',
            ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_payment_date_is_required(): void
    {
        $response = $this->actingAs($this->admin)
            ->post("/admin/supplier-debts/{$this->debt->id}/payments", [
                'amount' => 50000,
                'payment_date' => '',
            ]);

        $response->assertSessionHasErrors('payment_date');
    }

    // ==================== DEBT STATUS TRANSITION TESTS ====================

    public function test_debt_status_starts_as_unpaid(): void
    {
        $this->assertEquals('unpaid', $this->debt->status);
        $this->assertEquals(0, $this->debt->paid_amount);
    }

    public function test_debt_becomes_partial_after_partial_payment(): void
    {
        $this->actingAs($this->admin)
            ->post("/admin/supplier-debts/{$this->debt->id}/payments", [
                'amount' => 50000,
                'payment_date' => '2026-07-25',
            ]);

        $this->debt->refresh();
        $this->assertEquals('partial', $this->debt->status);
        $this->assertEquals(50000, $this->debt->remaining_amount);
    }

    public function test_debt_becomes_paid_after_full_payment(): void
    {
        $this->actingAs($this->admin)
            ->post("/admin/supplier-debts/{$this->debt->id}/payments", [
                'amount' => 100000,
                'payment_date' => '2026-07-25',
            ]);

        $this->debt->refresh();
        $this->assertEquals('paid', $this->debt->status);
        $this->assertEquals(0, $this->debt->remaining_amount);
    }

    // ==================== DEBT MODEL TESTS ====================

    public function test_remaining_amount_attribute(): void
    {
        $this->assertEquals(100000, $this->debt->remaining_amount);

        $this->debt->update(['paid_amount' => 30000]);
        $this->assertEquals(70000, $this->debt->fresh()->remaining_amount);

        $this->debt->update(['paid_amount' => 100000]);
        $this->assertEquals(0, $this->debt->fresh()->remaining_amount);
    }

    public function test_status_label_attribute(): void
    {
        $this->assertEquals('Belum Dibayar', $this->debt->status_label);

        $this->debt->update(['status' => 'partial']);
        $this->assertEquals('Sebagian', $this->debt->fresh()->status_label);

        $this->debt->update(['status' => 'paid']);
        $this->assertEquals('Lunas', $this->debt->fresh()->status_label);
    }

    public function test_status_badge_attribute(): void
    {
        $this->assertEquals('danger', $this->debt->status_badge);

        $this->debt->update(['status' => 'partial']);
        $this->assertEquals('warning', $this->debt->fresh()->status_badge);

        $this->debt->update(['status' => 'paid']);
        $this->assertEquals('success', $this->debt->fresh()->status_badge);
    }

    // ==================== PAYMENT RECORD TESTS ====================

    public function test_payment_records_user_id(): void
    {
        $this->actingAs($this->admin)
            ->post("/admin/supplier-debts/{$this->debt->id}/payments", [
                'amount' => 50000,
                'payment_date' => '2026-07-25',
            ]);

        $payment = SupplierDebtPayment::first();
        $this->assertEquals($this->admin->id, $payment->user_id);
    }

    public function test_payment_has_notes(): void
    {
        $this->actingAs($this->admin)
            ->post("/admin/supplier-debts/{$this->debt->id}/payments", [
                'amount' => 50000,
                'payment_date' => '2026-07-25',
                'notes' => 'Test payment notes',
            ]);

        $payment = SupplierDebtPayment::first();
        $this->assertEquals('Test payment notes', $payment->notes);
    }

    // ==================== DEBT EXPORT TESTS ====================

    public function test_debt_export_route_exists(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/supplier-debts/export');

        // Should return a file download (Excel)
        $response->assertStatus(200);
    }

    public function test_kasir_cannot_export_debts(): void
    {
        $response = $this->actingAs($this->kasir)
            ->get('/admin/supplier-debts/export');

        $response->assertStatus(403);
    }
}
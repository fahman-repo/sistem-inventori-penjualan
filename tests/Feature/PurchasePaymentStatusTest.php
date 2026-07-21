<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierDebt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchasePaymentStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $kasir;
    private Category $category;
    private Supplier $supplier;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin-purchasepay@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->kasir = User::create([
            'name' => 'Test Kasir',
            'email' => 'kasir-purchasepay@example.com',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        $this->category = Category::create(['name' => 'Makanan']);

        $this->supplier = Supplier::create([
            'name' => 'PT Supplier Payment',
            'phone' => '021-5551111',
            'address' => 'Jl. Test No. 1',
            'email' => 'payment@supplier.com',
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Test Product Payment',
            'sku' => 'PAYMENT-TEST-001',
            'unit' => 'pcs',
            'buy_price' => 5000,
            'sell_price' => 7500,
            'stock' => 10,
            'min_stock' => 5,
        ]);
    }

    // ==================== CASH PURCHASE TESTS ====================

    public function test_cash_purchase_does_not_create_debt(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/purchases', [
                'supplier_id' => $this->supplier->id,
                'purchase_date' => '2026-07-21',
                'payment_status' => 'cash',
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 5,
                        'buy_price' => 5000,
                    ],
                ],
                'notes' => 'Cash purchase test',
            ]);

        $response->assertRedirect('/admin/purchases')
            ->assertSessionHas('success');

        // Verify no debt record was created
        $this->assertEquals(0, SupplierDebt::count());
    }

    public function test_cash_purchase_still_increases_stock(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/purchases', [
                'supplier_id' => $this->supplier->id,
                'purchase_date' => '2026-07-21',
                'payment_status' => 'cash',
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 5,
                        'buy_price' => 5000,
                    ],
                ],
            ]);

        $this->product->refresh();
        $this->assertEquals(15, $this->product->stock); // 10 + 5
    }

    // ==================== CREDIT PURCHASE TESTS ====================

    public function test_credit_purchase_creates_debt_automatically(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/purchases', [
                'supplier_id' => $this->supplier->id,
                'purchase_date' => '2026-07-21',
                'payment_status' => 'credit',
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 10,
                        'buy_price' => 5000,
                    ],
                ],
                'notes' => 'Credit purchase test',
            ]);

        $response->assertRedirect('/admin/purchases')
            ->assertSessionHas('success');

        // Verify debt record was created
        $this->assertEquals(1, SupplierDebt::count());

        $debt = SupplierDebt::first();
        $this->assertEquals(50000, $debt->total_amount); // 10 x 5000
        $this->assertEquals(0, $debt->paid_amount);
        $this->assertEquals('unpaid', $debt->status);
        $this->assertEquals($this->supplier->id, $debt->supplier_id);
    }

    public function test_credit_purchase_also_increases_stock(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/purchases', [
                'supplier_id' => $this->supplier->id,
                'purchase_date' => '2026-07-21',
                'payment_status' => 'credit',
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 10,
                        'buy_price' => 5000,
                    ],
                ],
            ]);

        $this->product->refresh();
        $this->assertEquals(20, $this->product->stock); // 10 + 10
    }

    public function test_credit_purchase_debt_has_correct_amount(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/purchases', [
                'supplier_id' => $this->supplier->id,
                'purchase_date' => '2026-07-21',
                'payment_status' => 'credit',
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 3,
                        'buy_price' => 5000,
                    ],
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 2,
                        'buy_price' => 4500,
                    ],
                ],
            ]);

        $debt = SupplierDebt::first();
        $this->assertEquals(24000, $debt->total_amount); // (3 x 5000) + (2 x 4500)
    }

    public function test_credit_purchase_with_due_date(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/purchases', [
                'supplier_id' => $this->supplier->id,
                'purchase_date' => '2026-07-21',
                'payment_status' => 'credit',
                'due_date' => '2026-08-21',
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 5,
                        'buy_price' => 5000,
                    ],
                ],
            ]);

        $debt = SupplierDebt::first();
        $this->assertEquals('2026-08-21', $debt->due_date->format('Y-m-d'));
    }

    // ==================== TRANSACTION INTEGRITY TESTS ====================

    public function test_purchase_without_supplier_does_not_create_debt(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/purchases', [
                'supplier_id' => null,
                'purchase_date' => '2026-07-21',
                'payment_status' => 'credit', // Credit but no supplier
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 5,
                        'buy_price' => 5000,
                    ],
                ],
            ]);

        $response->assertRedirect('/admin/purchases');

        // No debt should be created since supplier is null
        $this->assertEquals(0, SupplierDebt::count());
    }

    public function test_payment_status_is_required(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/purchases', [
                'supplier_id' => $this->supplier->id,
                'purchase_date' => '2026-07-21',
                // payment_status omitted
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 5,
                        'buy_price' => 5000,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors('payment_status');
    }

    public function test_payment_status_must_be_valid(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/purchases', [
                'supplier_id' => $this->supplier->id,
                'purchase_date' => '2026-07-21',
                'payment_status' => 'invalid_status',
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 5,
                        'buy_price' => 5000,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors('payment_status');
    }

    // ==================== PURCHASE DISPLAY TESTS ====================

    public function test_purchase_index_shows_payment_status(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/purchases', [
                'supplier_id' => $this->supplier->id,
                'purchase_date' => '2026-07-21',
                'payment_status' => 'credit',
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 5,
                        'buy_price' => 5000,
                    ],
                ],
            ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/purchases');

        $response->assertStatus(200)
            ->assertSee('Credit')
            ->assertSee('Utang');
    }
}
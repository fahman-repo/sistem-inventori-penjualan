<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $kasir;
    private Category $category;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Test Admin',
            'email' => 'test-admin-purchase@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->kasir = User::create([
            'name' => 'Test Kasir',
            'email' => 'test-kasir-purchase@example.com',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        $this->category = Category::create(['name' => 'Makanan']);

        $this->supplier = Supplier::create([
            'name' => 'Test Supplier',
            'phone' => '021-5555555',
            'address' => 'Jl. Test No. 1',
        ]);
    }

    private function createTestProduct(string $sku, int $stock): Product
    {
        return Product::create([
            'category_id' => $this->category->id,
            'name' => 'Test Product ' . $sku,
            'sku' => $sku,
            'unit' => 'pcs',
            'buy_price' => 2500,
            'sell_price' => 3500,
            'stock' => $stock,
            'min_stock' => 10,
        ]);
    }

    // ==================== PURCHASE LISTING TESTS ====================

    public function test_purchase_index_can_be_rendered(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/purchases');

        $response->assertStatus(200);
    }

    public function test_purchases_can_be_searched_by_invoice(): void
    {
        $purchase = \App\Models\Purchase::create([
            'invoice_number' => 'PO-20260718-0001',
            'user_id' => $this->admin->id,
            'supplier_id' => $this->supplier->id,
            'purchase_date' => '2026-07-18',
            'total' => 100000,
            'notes' => 'Test purchase',
            'payment_status' => 'cash',
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/purchases?search=PO-20260718-0001');

        $response->assertStatus(200)
            ->assertSee('PO-20260718-0001');
    }

    // ==================== PURCHASE CREATION TESTS ====================

    public function test_admin_can_create_purchase(): void
    {
        $product = $this->createTestProduct('PUR-001', 50);

        $response = $this->actingAs($this->admin)
            ->post('/admin/purchases', [
                'supplier_id' => $this->supplier->id,
                'purchase_date' => '2026-07-18',
                'payment_status' => 'cash',
                'notes' => 'QA Test Purchase',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 20,
                        'buy_price' => 2500,
                    ],
                ],
            ]);

        $response->assertRedirect('/admin/purchases')
            ->assertSessionHas('success');

        // Verify purchase created
        $this->assertDatabaseHas('purchases', [
            'user_id' => $this->admin->id,
            'total' => 50000,
        ]);

        // Verify purchase item created
        $this->assertDatabaseHas('purchase_items', [
            'product_id' => $product->id,
            'quantity' => 20,
            'buy_price' => 2500,
        ]);

        // Verify stock increased
        $product->refresh();
        $this->assertEquals(70, $product->stock); // 50 + 20
    }

    public function test_purchase_increases_product_stock(): void
    {
        $product = $this->createTestProduct('PUR-STOCK-TEST', 100);

        $initialStock = $product->stock;

        $response = $this->actingAs($this->admin)
            ->post('/admin/purchases', [
                'supplier_id' => $this->supplier->id,
                'purchase_date' => '2026-07-18',
                'payment_status' => 'cash',
                'notes' => 'Stock increase test',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 50,
                        'buy_price' => 2000,
                    ],
                ],
            ]);

        $response->assertRedirect('/admin/purchases')
            ->assertSessionHas('success');

        $product->refresh();
        $this->assertEquals($initialStock + 50, $product->stock);
    }

    public function test_purchase_creates_multiple_items(): void
    {
        $product1 = $this->createTestProduct('PUR-MUL-A', 30);
        $product2 = $this->createTestProduct('PUR-MUL-B', 40);

        $response = $this->actingAs($this->admin)
            ->post('/admin/purchases', [
                'supplier_id' => $this->supplier->id,
                'purchase_date' => '2026-07-18',
                'payment_status' => 'cash',
                'notes' => 'Multiple items purchase',
                'items' => [
                    [
                        'product_id' => $product1->id,
                        'quantity' => 10,
                        'buy_price' => 2000,
                    ],
                    [
                        'product_id' => $product2->id,
                        'quantity' => 15,
                        'buy_price' => 3000,
                    ],
                ],
            ]);

        $response->assertRedirect('/admin/purchases');

        // Verify both items created
        $this->assertDatabaseHas('purchase_items', [
            'product_id' => $product1->id,
            'quantity' => 10,
            'subtotal' => 20000,
        ]);

        $this->assertDatabaseHas('purchase_items', [
            'product_id' => $product2->id,
            'quantity' => 15,
            'subtotal' => 45000,
        ]);

        // Verify total
        $purchase = \App\Models\Purchase::latest()->first();
        $this->assertEquals(65000, $purchase->total);
    }

    // ==================== PURCHASE EDIT TESTS ====================

    public function test_admin_can_edit_purchase(): void
    {
        $product = $this->createTestProduct('PUR-EDIT-001', 50);

        $purchase = \App\Models\Purchase::create([
            'invoice_number' => 'PO-20260717-0001',
            'user_id' => $this->admin->id,
            'supplier_id' => $this->supplier->id,
            'purchase_date' => '2026-07-17',
            'total' => 25000,
            'notes' => 'Original note',
            'payment_status' => 'cash',
        ]);

        $purchase->items()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'buy_price' => 2500,
            'subtotal' => 25000,
        ]);

        $originalStock = $product->stock;

        $response = $this->actingAs($this->admin)
            ->put("/admin/purchases/{$purchase->id}", [
                'supplier_id' => $this->supplier->id,
                'purchase_date' => '2026-07-17',
                'payment_status' => 'cash',
                'notes' => 'Updated note',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 15,
                        'buy_price' => 2500,
                    ],
                ],
            ]);

        $response->assertRedirect('/admin/purchases')
            ->assertSessionHas('success');

        $purchase->refresh();
        $this->assertEquals('Updated note', $purchase->notes);
        $this->assertEquals(37500, $purchase->total);

        // Note: Edit doesn't update stock in this implementation
    }

    // ==================== PURCHASE DELETION TESTS ====================

    public function test_admin_can_delete_purchase(): void
    {
        $product = $this->createTestProduct('PUR-DEL-001', 100);

        $purchase = \App\Models\Purchase::create([
            'invoice_number' => 'PO-20260716-0001',
            'user_id' => $this->admin->id,
            'supplier_id' => $this->supplier->id,
            'purchase_date' => '2026-07-16',
            'total' => 50000,
            'notes' => 'To delete',
            'payment_status' => 'cash',
        ]);

        $purchase->items()->create([
            'product_id' => $product->id,
            'quantity' => 20,
            'buy_price' => 2500,
            'subtotal' => 50000,
        ]);

        $stockBeforeDelete = $product->stock;

        $response = $this->actingAs($this->admin)
            ->delete("/admin/purchases/{$purchase->id}");

        $response->assertRedirect('/admin/purchases')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('purchases', ['id' => $purchase->id]);
        $this->assertDatabaseMissing('purchase_items', ['purchase_id' => $purchase->id]);

        // Verify stock decreased
        $product->refresh();
        $this->assertEquals($stockBeforeDelete - 20, $product->stock);
    }

    // ==================== ROLE ACCESS TESTS ====================

    public function test_kasir_cannot_access_purchase_management(): void
    {
        $response = $this->actingAs($this->kasir)
            ->get('/admin/purchases');

        $response->assertStatus(403);
    }

    public function test_kasir_cannot_create_purchase(): void
    {
        $response = $this->actingAs($this->kasir)
            ->post('/admin/purchases', [
                'supplier_id' => $this->supplier->id,
                'purchase_date' => '2026-07-18',
                'items' => [],
            ]);

        $response->assertStatus(403);
    }
}
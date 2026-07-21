<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $kasir1;
    private User $kasir2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Sale Admin',
            'email' => 'sale-admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->kasir1 = User::create([
            'name' => 'Sale Kasir 1',
            'email' => 'sale-kasir1@example.com',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        $this->kasir2 = User::create([
            'name' => 'Sale Kasir 2',
            'email' => 'sale-kasir2@example.com',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);
    }

    private function createTestProduct(string $sku, int $stock): Product
    {
        // Ensure category exists
        $category = Category::firstOrCreate(['name' => 'Test Category']);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Sale Test Product ' . $sku,
            'sku' => $sku,
            'unit' => 'pcs',
            'buy_price' => 2500,
            'sell_price' => 3500,
            'stock' => $stock,
            'min_stock' => 10,
        ]);
    }

    // ==================== SALE LISTING TESTS ====================

    public function test_sale_index_can_be_rendered(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/sales');

        $response->assertStatus(200);
    }

    public function test_kasir_sees_only_own_sales(): void
    {
        // Create sale for kasir1
        $product = $this->createTestProduct('KASIR-TEST', 100);

        Sale::create([
            'invoice_number' => 'INV-20260718-0001',
            'user_id' => $this->kasir1->id,
            'sale_date' => '2026-07-18',
            'total' => 35000,
        ]);

        // Admin should see all sales
        $adminResponse = $this->actingAs($this->admin)
            ->get('/sales');
        $adminResponse->assertStatus(200);

        // Kasir1 should see their own sale
        $kasir1Response = $this->actingAs($this->kasir1)
            ->get('/sales');
        $kasir1Response->assertStatus(200)
            ->assertSee('INV-20260718-0001');

        // Kasir2 should NOT see kasir1's sale
        $kasir2Response = $this->actingAs($this->kasir2)
            ->get('/sales');
        $kasir2Response->assertStatus(200)
            ->assertDontSee('INV-20260718-0001');
    }

    public function test_sales_can_be_filtered_by_date(): void
    {
        Sale::create([
            'invoice_number' => 'INV-20260710-0001',
            'user_id' => $this->admin->id,
            'sale_date' => '2026-07-10',
            'total' => 100000,
        ]);

        Sale::create([
            'invoice_number' => 'INV-20260718-0001',
            'user_id' => $this->admin->id,
            'sale_date' => '2026-07-18',
            'total' => 200000,
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/sales?date_from=2026-07-15&date_to=2026-07-20');

        $response->assertStatus(200)
            ->assertSee('INV-20260718-0001')
            ->assertDontSee('INV-20260710-0001');
    }

    // ==================== SALE CREATION TESTS ====================

    public function test_authenticated_user_can_create_sale(): void
    {
        $product = $this->createTestProduct('CREATE-001', 100);

        $response = $this->actingAs($this->kasir1)
            ->post('/sales', [
                'sale_date' => '2026-07-18',
                'notes' => 'QA Test Sale',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 5,
                        'sell_price' => 3500,
                    ],
                ],
            ]);

        $response->assertRedirect('/sales')
            ->assertSessionHas('success');

        // Verify sale created
        $sale = Sale::latest()->first();
        $this->assertNotNull($sale);
        $this->assertEquals(17500, $sale->total);
        $this->assertEquals($this->kasir1->id, $sale->user_id);
        $this->assertStringStartsWith('INV-', $sale->invoice_number);
    }

    public function test_sale_decreases_product_stock(): void
    {
        $product = $this->createTestProduct('STOCK-DEC-001', 100);

        $initialStock = $product->stock;

        $this->actingAs($this->kasir1)
            ->post('/sales', [
                'sale_date' => '2026-07-18',
                'notes' => 'Stock decrease test',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 20,
                        'sell_price' => 3500,
                    ],
                ],
            ]);

        $product->refresh();
        $this->assertEquals($initialStock - 20, $product->stock);
    }

    public function test_sale_stores_transaction_price(): void
    {
        $product = $this->createTestProduct('PRICE-001', 100);

        // Change product price after creation
        $product->update(['sell_price' => 5000]);

        $this->actingAs($this->kasir1)
            ->post('/sales', [
                'sale_date' => '2026-07-18',
                'notes' => 'Price history test',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                        'sell_price' => 3500, // Use old price
                    ],
                ],
            ]);

        $saleItem = \App\Models\SaleItem::latest()->first();
        $this->assertEquals(3500, $saleItem->sell_price); // Should be 3500, not 5000
    }

    public function test_sale_validates_sufficient_stock(): void
    {
        $product = $this->createTestProduct('STOCK-VALID', 10);

        $response = $this->actingAs($this->kasir1)
            ->post('/sales', [
                'sale_date' => '2026-07-18',
                'notes' => 'Insufficient stock test',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 50, // More than available (10)
                        'sell_price' => 3500,
                    ],
                ],
            ]);

        // Should fail with validation error
        $response->assertSessionHasErrors('items');
        // The sale should not be created because stock validation fails
        $this->assertEquals(10, $product->fresh()->stock); // Stock should not change
    }

    public function test_sale_cannot_create_with_zero_stock(): void
    {
        $product = $this->createTestProduct('ZERO-STOCK', 0);

        $response = $this->actingAs($this->kasir1)
            ->post('/sales', [
                'sale_date' => '2026-07-18',
                'notes' => 'Zero stock test',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                        'sell_price' => 3500,
                    ],
                ],
            ]);

        // Should fail - product has 0 stock
        $response->assertSessionHasErrors('items');
        $this->assertEquals(0, $product->fresh()->stock); // Stock should remain 0
    }

    public function test_sale_requires_at_least_one_item(): void
    {
        $response = $this->actingAs($this->kasir1)
            ->post('/sales', [
                'sale_date' => '2026-07-18',
                'notes' => 'No items test',
                'items' => [],
            ]);

        $response->assertSessionHasErrors('items');
    }

    public function test_sale_generates_unique_invoice_number(): void
    {
        $product = $this->createTestProduct('INV-TEST', 100);

        // Create multiple sales
        $this->actingAs($this->kasir1)
            ->post('/sales', [
                'sale_date' => '2026-07-18',
                'notes' => 'First sale',
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1, 'sell_price' => 3500],
                ],
            ]);

        $this->actingAs($this->kasir1)
            ->post('/sales', [
                'sale_date' => '2026-07-18',
                'notes' => 'Second sale',
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1, 'sell_price' => 3500],
                ],
            ]);

        $this->actingAs($this->admin)
            ->post('/sales', [
                'sale_date' => '2026-07-18',
                'notes' => 'Third sale',
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1, 'sell_price' => 3500],
                ],
            ]);

        $invoices = Sale::pluck('invoice_number')->toArray();

        $this->assertCount(3, $invoices);
        // Check sequential numbering pattern
        $today = now()->format('Ymd');
        $this->assertEquals("INV-{$today}-0001", $invoices[0]);
        $this->assertEquals("INV-{$today}-0002", $invoices[1]);
        $this->assertEquals("INV-{$today}-0003", $invoices[2]);
    }

    // ==================== SALE MULTIPLE ITEMS TESTS ====================

    public function test_sale_can_have_multiple_items(): void
    {
        $product1 = $this->createTestProduct('MULTI-001', 100);
        $product2 = $this->createTestProduct('MULTI-002', 50);

        $response = $this->actingAs($this->kasir1)
            ->post('/sales', [
                'sale_date' => '2026-07-18',
                'notes' => 'Multiple items sale',
                'items' => [
                    [
                        'product_id' => $product1->id,
                        'quantity' => 5,
                        'sell_price' => 3500,
                    ],
                    [
                        'product_id' => $product2->id,
                        'quantity' => 10,
                        'sell_price' => 1500,
                    ],
                ],
            ]);

        $response->assertRedirect('/sales');

        $sale = Sale::latest()->first();
        $this->assertEquals(5 * 3500 + 10 * 1500, $sale->total); // 32500

        $this->assertEquals(2, $sale->items()->count());
    }

    // ==================== SALE EDIT TESTS ====================

    public function test_admin_can_edit_sale(): void
    {
        $product = $this->createTestProduct('EDIT-TEST', 100);

        $sale = Sale::create([
            'invoice_number' => 'INV-20260717-0001',
            'user_id' => $this->admin->id,
            'sale_date' => '2026-07-17',
            'total' => 35000,
            'notes' => 'Original note',
        ]);

        $sale->items()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'buy_price' => 2500,
            'sell_price' => 3500,
            'subtotal' => 35000,
        ]);

        $response = $this->actingAs($this->admin)
            ->put("/sales/{$sale->id}", [
                'sale_date' => '2026-07-17',
                'notes' => 'Updated note',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 5,
                        'sell_price' => 3500,
                    ],
                ],
            ]);

        $response->assertRedirect('/sales')
            ->assertSessionHas('success');

        $sale->refresh();
        $this->assertEquals('Updated note', $sale->notes);
        $this->assertEquals(17500, $sale->total);
    }

    // ==================== SALE DELETION TESTS ====================

    public function test_admin_can_delete_sale(): void
    {
        $product = $this->createTestProduct('DEL-TEST', 100);

        $sale = Sale::create([
            'invoice_number' => 'INV-20260716-0001',
            'user_id' => $this->admin->id,
            'sale_date' => '2026-07-16',
            'total' => 35000,
            'notes' => 'To delete',
        ]);

        $saleItem = $sale->items()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'buy_price' => 2500,
            'sell_price' => 3500,
            'subtotal' => 35000,
        ]);

        $stockBeforeDelete = $product->stock;

        $response = $this->actingAs($this->admin)
            ->delete("/sales/{$sale->id}");

        $response->assertRedirect('/sales')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('sales', ['id' => $sale->id]);
        $this->assertDatabaseMissing('sale_items', ['id' => $saleItem->id]);

        // Verify stock restored
        $product->refresh();
        $this->assertEquals($stockBeforeDelete + 10, $product->stock);
    }

    public function test_kasir_can_only_delete_own_sale(): void
    {
        $product = $this->createTestProduct('DEL-OWN', 100);

        $sale = Sale::create([
            'invoice_number' => 'INV-20260715-0001',
            'user_id' => $this->kasir1->id,
            'sale_date' => '2026-07-15',
            'total' => 35000,
        ]);

        $sale->items()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'buy_price' => 2500,
            'sell_price' => 3500,
            'subtotal' => 35000,
        ]);

        // Kasir2 cannot delete Kasir1's sale
        $response = $this->actingAs($this->kasir2)
            ->delete("/sales/{$sale->id}");

        $response->assertStatus(403);
    }

    // ==================== ROLE ACCESS TESTS ====================

    public function test_kasir_cannot_access_sale_edit(): void
    {
        $product = $this->createTestProduct('EDIT-OWN', 100);

        $sale = Sale::create([
            'invoice_number' => 'INV-20260714-0001',
            'user_id' => $this->admin->id,
            'sale_date' => '2026-07-14',
            'total' => 35000,
        ]);

        $sale->items()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'buy_price' => 2500,
            'sell_price' => 3500,
            'subtotal' => 35000,
        ]);

        $response = $this->actingAs($this->kasir1)
            ->get("/sales/{$sale->id}/edit");

        $response->assertStatus(403);
    }

    public function test_guest_cannot_create_sale(): void
    {
        $response = $this->post('/sales', [
            'sale_date' => '2026-07-18',
            'items' => [],
        ]);

        $response->assertRedirect('/login');
    }
}
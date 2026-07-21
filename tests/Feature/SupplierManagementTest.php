<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $kasir;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin-supplier@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->kasir = User::create([
            'name' => 'Test Kasir',
            'email' => 'kasir-supplier@example.com',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        $this->category = Category::create(['name' => 'Makanan']);
    }

    // ==================== SUPPLIER LISTING TESTS ====================

    public function test_supplier_index_can_be_rendered(): void
    {
        Supplier::create([
            'name' => 'PT Test Supplier',
            'phone' => '021-5551111',
            'address' => 'Jl. Test No. 1',
            'email' => 'test@supplier.com',
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/suppliers');

        $response->assertStatus(200)
            ->assertSee('PT Test Supplier');
    }

    public function test_kasir_cannot_access_suppliers(): void
    {
        $response = $this->actingAs($this->kasir)
            ->get('/admin/suppliers');

        $response->assertStatus(403);
    }

    // ==================== SUPPLIER CREATION TESTS ====================

    public function test_admin_can_create_supplier(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/suppliers', [
                'name' => 'PT Supplier Baru',
                'phone' => '021-5552222',
                'address' => 'Jl. Merdeka No. 10',
                'email' => 'supplier@baru.com',
            ]);

        $response->assertRedirect('/admin/suppliers')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('suppliers', [
            'name' => 'PT Supplier Baru',
            'email' => 'supplier@baru.com',
        ]);
    }

    public function test_supplier_creation_requires_name(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/suppliers', [
                'name' => '',
                'phone' => '021-5553333',
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_supplier_creation_accepts_valid_email(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/suppliers', [
                'name' => 'Supplier Email Test',
                'phone' => '021-5554444',
                'email' => 'invalid-email', // Invalid email format
            ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_supplier_creation_without_email(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/suppliers', [
                'name' => 'Supplier No Email',
                'phone' => '021-5555555',
                'address' => 'Jl. Test',
                // email is optional
            ]);

        $response->assertRedirect('/admin/suppliers')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('suppliers', [
            'name' => 'Supplier No Email',
        ]);
    }

    // ==================== SUPPLIER EDIT TESTS ====================

    public function test_admin_can_edit_supplier(): void
    {
        $supplier = Supplier::create([
            'name' => 'PT Original',
            'phone' => '021-5556666',
            'address' => 'Jl. Lama No. 1',
            'email' => 'original@supplier.com',
        ]);

        $response = $this->actingAs($this->admin)
            ->put("/admin/suppliers/{$supplier->id}", [
                'name' => 'PT Updated',
                'phone' => '021-5557777',
                'address' => 'Jl. Baru No. 2',
                'email' => 'updated@supplier.com',
            ]);

        $response->assertRedirect('/admin/suppliers')
            ->assertSessionHas('success');

        $supplier->refresh();
        $this->assertEquals('PT Updated', $supplier->name);
        $this->assertEquals('updated@supplier.com', $supplier->email);
    }

    // ==================== SUPPLIER DELETION TESTS ====================

    public function test_admin_can_delete_supplier_without_purchases(): void
    {
        $supplier = Supplier::create([
            'name' => 'PT To Delete',
            'phone' => '021-5558888',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete("/admin/suppliers/{$supplier->id}");

        $response->assertRedirect('/admin/suppliers')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }

    public function test_admin_cannot_delete_supplier_with_purchases(): void
    {
        $supplier = Supplier::create([
            'name' => 'PT With Purchases',
            'phone' => '021-5559999',
        ]);

        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Test Product',
            'sku' => 'SUPPLIER-TEST-001',
            'unit' => 'pcs',
            'buy_price' => 1000,
            'sell_price' => 1500,
            'stock' => 50,
            'min_stock' => 5,
        ]);

        // Create a purchase with this supplier
        Purchase::create([
            'invoice_number' => 'PO-TEST-SUPPLIER-001',
            'user_id' => $this->admin->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => '2026-07-21',
            'total' => 50000,
            'payment_status' => 'cash',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete("/admin/suppliers/{$supplier->id}");

        $response->assertRedirect('/admin/suppliers')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id]);
    }

    // ==================== SUPPLIER DETAIL VIEW TESTS ====================

    public function test_supplier_show_displays_purchase_history(): void
    {
        $supplier = Supplier::create([
            'name' => 'PT Detail View',
            'phone' => '021-5550000',
        ]);

        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Detail Product',
            'sku' => 'DETAIL-001',
            'unit' => 'pcs',
            'buy_price' => 1000,
            'sell_price' => 1500,
            'stock' => 50,
            'min_stock' => 5,
        ]);

        $purchase = Purchase::create([
            'invoice_number' => 'PO-DETAIL-001',
            'user_id' => $this->admin->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => '2026-07-21',
            'total' => 50000,
            'payment_status' => 'cash',
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/suppliers/{$supplier->id}");

        $response->assertStatus(200)
            ->assertSee('PO-DETAIL-001')
            ->assertSee('50.000');
    }
}
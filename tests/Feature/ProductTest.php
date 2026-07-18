<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $kasir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Test Admin',
            'email' => 'test-admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->kasir = User::create([
            'name' => 'Test Kasir',
            'email' => 'test-kasir@example.com',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        // Create category for testing
        $this->category = Category::create(['name' => 'Makanan']);
    }

    // ==================== PRODUCT LISTING TESTS ====================

    public function test_product_index_can_be_rendered(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/products');

        $response->assertStatus(200);
    }

    public function test_products_can_be_searched(): void
    {
        Product::create([
            'category_id' => $this->category->id,
            'name' => 'Indomie Goreng',
            'sku' => 'TEST-001',
            'unit' => 'pcs',
            'buy_price' => 2500,
            'sell_price' => 3500,
            'stock' => 100,
            'min_stock' => 10,
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/products?search=Indomie');

        $response->assertStatus(200)
            ->assertSee('Indomie Goreng');
    }

    public function test_products_can_be_filtered_by_category(): void
    {
        $otherCategory = Category::create(['name' => 'Minuman']);

        Product::create([
            'category_id' => $this->category->id,
            'name' => 'Test Product',
            'sku' => 'TEST-FOOD',
            'unit' => 'pcs',
            'buy_price' => 1000,
            'sell_price' => 1500,
            'stock' => 50,
            'min_stock' => 5,
        ]);

        Product::create([
            'category_id' => $otherCategory->id,
            'name' => 'Test Minuman',
            'sku' => 'TEST-BEVR',
            'unit' => 'botol',
            'buy_price' => 2000,
            'sell_price' => 3000,
            'stock' => 30,
            'min_stock' => 3,
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/products?category_id=' . $this->category->id);

        $response->assertStatus(200)
            ->assertSee('Test Product')
            ->assertDontSee('Test Minuman');
    }

    // ==================== PRODUCT CREATION TESTS ====================

    public function test_admin_can_create_product(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/products', [
                'category_id' => $this->category->id,
                'name' => 'New Product',
                'sku' => 'NEW-001',
                'unit' => 'pcs',
                'buy_price' => 5000,
                'sell_price' => 7500,
                'stock' => 100,
                'min_stock' => 10,
            ]);

        $response->assertRedirect('/admin/products')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'sku' => 'NEW-001',
            'name' => 'New Product',
        ]);
    }

    public function test_product_sku_must_be_unique(): void
    {
        Product::create([
            'category_id' => $this->category->id,
            'name' => 'Existing Product',
            'sku' => 'UNIQUE-001',
            'unit' => 'pcs',
            'buy_price' => 1000,
            'sell_price' => 1500,
            'stock' => 50,
            'min_stock' => 5,
        ]);

        $response = $this->actingAs($this->admin)
            ->post('/admin/products', [
                'category_id' => $this->category->id,
                'name' => 'Duplicate SKU',
                'sku' => 'UNIQUE-001', // Same SKU
                'unit' => 'pcs',
                'buy_price' => 2000,
                'sell_price' => 3000,
                'stock' => 20,
                'min_stock' => 2,
            ]);

        $response->assertSessionHasErrors('sku');
    }

    public function test_product_requires_valid_data(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/products', [
                'name' => '', // Required
                'sku' => '', // Required
            ]);

        $response->assertSessionHasErrors(['name', 'sku']);
    }

    // ==================== PRODUCT EDIT TESTS ====================

    public function test_admin_can_edit_product(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Original Name',
            'sku' => 'EDIT-001',
            'unit' => 'pcs',
            'buy_price' => 5000,
            'sell_price' => 7500,
            'stock' => 100,
            'min_stock' => 10,
        ]);

        $response = $this->actingAs($this->admin)
            ->put("/admin/products/{$product->id}", [
                'category_id' => $this->category->id,
                'name' => 'Updated Name',
                'sku' => 'EDIT-001',
                'unit' => 'pcs',
                'buy_price' => 6000,
                'sell_price' => 9000,
                'stock' => 150,
                'min_stock' => 15,
            ]);

        $response->assertRedirect('/admin/products')
            ->assertSessionHas('success');

        $product->refresh();
        $this->assertEquals('Updated Name', $product->name);
        $this->assertEquals(6000, $product->buy_price);
        $this->assertEquals(9000, $product->sell_price);
    }

    // ==================== PRODUCT DELETION TESTS ====================

    public function test_admin_can_delete_product(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'To Delete',
            'sku' => 'DELETE-001',
            'unit' => 'pcs',
            'buy_price' => 1000,
            'sell_price' => 1500,
            'stock' => 10,
            'min_stock' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete("/admin/products/{$product->id}");

        $response->assertRedirect('/admin/products')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    // ==================== ROLE ACCESS TESTS ====================

    public function test_kasir_cannot_access_product_management(): void
    {
        $response = $this->actingAs($this->kasir)
            ->get('/admin/products');

        $response->assertStatus(403);
    }

    public function test_kasir_cannot_create_product(): void
    {
        $response = $this->actingAs($this->kasir)
            ->post('/admin/products', [
                'category_id' => $this->category->id,
                'name' => 'Unauthorized Product',
                'sku' => 'UNAUTH-001',
                'unit' => 'pcs',
                'buy_price' => 1000,
                'sell_price' => 1500,
                'stock' => 10,
                'min_stock' => 1,
            ]);

        $response->assertStatus(403);
    }
}
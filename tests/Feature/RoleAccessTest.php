<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $kasir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Role Admin',
            'email' => 'role-admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->kasir = User::create([
            'name' => 'Role Kasir',
            'email' => 'role-kasir@example.com',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);
    }

    // ==================== ADMIN ACCESS TESTS ====================

    public function test_admin_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_admin_can_access_products(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/products');

        $response->assertStatus(200);
    }

    public function test_admin_can_access_product_create(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/products/create');

        $response->assertStatus(200);
    }

    public function test_admin_can_access_categories(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/categories');

        $response->assertStatus(200);
    }

    public function test_admin_can_access_purchases(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/purchases');

        $response->assertStatus(200);
    }

    public function test_admin_can_access_purchase_create(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/purchases/create');

        $response->assertStatus(200);
    }

    public function test_admin_can_access_sales(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/sales');

        $response->assertStatus(200);
    }

    public function test_admin_can_access_sales_create(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/sales/create');

        $response->assertStatus(200);
    }

    public function test_admin_can_access_stock_report(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/reports/stock');

        $response->assertStatus(200);
    }

    public function test_admin_can_access_sales_report(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/reports/sales');

        $response->assertStatus(200);
    }

    public function test_admin_can_access_profit_report(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/reports/profit');

        $response->assertStatus(200);
    }

    public function test_admin_can_access_profile(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/profile');

        $response->assertStatus(200);
    }

    // ==================== KASIR ACCESS TESTS ====================

    public function test_kasir_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->kasir)
            ->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_kasir_can_access_sales(): void
    {
        $response = $this->actingAs($this->kasir)
            ->get('/sales');

        $response->assertStatus(200);
    }

    public function test_kasir_can_access_sales_create(): void
    {
        $response = $this->actingAs($this->kasir)
            ->get('/sales/create');

        $response->assertStatus(200);
    }

    public function test_kasir_can_access_profile(): void
    {
        $response = $this->actingAs($this->kasir)
            ->get('/profile');

        $response->assertStatus(200);
    }

    // ==================== ADMIN RESTRICTED TESTS ====================

    public function test_admin_cannot_access_product_create(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/products/create');

        $response->assertStatus(200);
    }

    public function test_admin_cannot_access_category_create(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/categories/create');

        $response->assertStatus(200);
    }

    // ==================== KASIR RESTRICTED TESTS ====================

    public function test_kasir_cannot_access_admin_products(): void
    {
        $response = $this->actingAs($this->kasir)
            ->get('/admin/products');

        $response->assertStatus(403);
    }

    public function test_kasir_cannot_access_product_create(): void
    {
        $response = $this->actingAs($this->kasir)
            ->get('/admin/products/create');

        $response->assertStatus(403);
    }

    public function test_kasir_cannot_access_categories(): void
    {
        $response = $this->actingAs($this->kasir)
            ->get('/admin/categories');

        $response->assertStatus(403);
    }

    public function test_kasir_cannot_access_purchases(): void
    {
        $response = $this->actingAs($this->kasir)
            ->get('/admin/purchases');

        $response->assertStatus(403);
    }

    public function test_kasir_cannot_access_purchase_create(): void
    {
        $response = $this->actingAs($this->kasir)
            ->get('/admin/purchases/create');

        $response->assertStatus(403);
    }

    public function test_kasir_cannot_access_stock_report(): void
    {
        $response = $this->actingAs($this->kasir)
            ->get('/admin/reports/stock');

        $response->assertStatus(403);
    }

    public function test_kasir_cannot_access_sales_report(): void
    {
        $response = $this->actingAs($this->kasir)
            ->get('/admin/reports/sales');

        $response->assertStatus(403);
    }

    public function test_kasir_cannot_access_profit_report(): void
    {
        $response = $this->actingAs($this->kasir)
            ->get('/admin/reports/profit');

        $response->assertStatus(403);
    }

    // ==================== GUEST RESTRICTED TESTS ====================

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_access_products(): void
    {
        $response = $this->get('/admin/products');

        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_access_sales(): void
    {
        $response = $this->get('/sales');

        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_access_purchases(): void
    {
        $response = $this->get('/admin/purchases');

        $response->assertRedirect('/login');
    }

    // ==================== USER MODEL TESTS ====================

    public function test_user_is_admin(): void
    {
        $this->assertTrue($this->admin->isAdmin());
        $this->assertFalse($this->kasir->isAdmin());
    }

    public function test_user_is_kasir(): void
    {
        $this->assertTrue($this->kasir->isKasir());
        $this->assertFalse($this->admin->isKasir());
    }
}
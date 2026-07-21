<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $kasir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin-usermgmt@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->kasir = User::create([
            'name' => 'Test Kasir',
            'email' => 'kasir-usermgmt@example.com',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);
    }

    // ==================== USER LISTING TESTS ====================

    public function test_user_index_can_be_rendered(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/users');

        $response->assertStatus(200);
    }

    public function test_kasir_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->kasir)
            ->get('/admin/users');

        $response->assertStatus(403);
    }

    public function test_user_index_can_be_searched(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/users?search=Test Admin');

        $response->assertStatus(200)
            ->assertSee('Test Admin');
    }

    public function test_user_index_can_be_filtered_by_role(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/users?role=kasir');

        $response->assertStatus(200)
            ->assertSee('Test Kasir');
    }

    // ==================== USER CREATION TESTS ====================

    public function test_admin_can_create_user(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/users', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password123',
                'role' => 'kasir',
            ]);

        $response->assertRedirect('/admin/users')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'role' => 'kasir',
        ]);
    }

    public function test_user_creation_requires_valid_role(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/users', [
                'name' => 'Invalid Role User',
                'email' => 'invalid@example.com',
                'password' => 'password123',
                'role' => 'superadmin', // Invalid role
            ]);

        $response->assertSessionHasErrors('role');
    }

    public function test_user_creation_requires_password_min_8_chars(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/users', [
                'name' => 'Short Password',
                'email' => 'short@example.com',
                'password' => 'short', // Only 5 chars
                'role' => 'kasir',
            ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_user_creation_requires_unique_email(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/users', [
                'name' => 'Duplicate Email',
                'email' => 'admin-usermgmt@example.com', // Already exists
                'password' => 'password123',
                'role' => 'kasir',
            ]);

        $response->assertSessionHasErrors('email');
    }

    // ==================== USER EDIT TESTS ====================

    public function test_admin_can_edit_user(): void
    {
        $user = User::create([
            'name' => 'To Edit',
            'email' => 'toedit@example.com',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        $response = $this->actingAs($this->admin)
            ->put("/admin/users/{$user->id}", [
                'name' => 'Edited Name',
                'email' => 'toedit@example.com',
                'role' => 'kasir',
                // Password not filled = unchanged
            ]);

        $response->assertRedirect('/admin/users')
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('Edited Name', $user->name);
    }

    public function test_admin_can_edit_user_with_new_password(): void
    {
        $user = User::create([
            'name' => 'Password Change',
            'email' => 'passchange@example.com',
            'password' => bcrypt('oldpassword'),
            'role' => 'kasir',
        ]);

        $response = $this->actingAs($this->admin)
            ->put("/admin/users/{$user->id}", [
                'name' => 'Password Change',
                'email' => 'passchange@example.com',
                'password' => 'newpassword123',
                'role' => 'kasir',
            ]);

        $response->assertRedirect('/admin/users')
            ->assertSessionHas('success');
    }

    public function test_user_edit_can_change_role(): void
    {
        $user = User::create([
            'name' => 'Role Change',
            'email' => 'rolechange@example.com',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        $response = $this->actingAs($this->admin)
            ->put("/admin/users/{$user->id}", [
                'name' => 'Role Change',
                'email' => 'rolechange@example.com',
                'role' => 'admin',
            ]);

        $response->assertRedirect('/admin/users')
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('admin', $user->role);
    }

    // ==================== USER DELETION TESTS (GUARD) ====================

    public function test_admin_can_delete_other_user(): void
    {
        $user = User::create([
            'name' => 'To Delete',
            'email' => 'todelete@example.com',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete("/admin/users/{$user->id}");

        $response->assertRedirect('/admin/users')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $response = $this->actingAs($this->admin)
            ->delete("/admin/users/{$this->admin->id}");

        $response->assertRedirect('/admin/users')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    // ==================== ACTIVITY LOG TESTS ====================

    public function test_user_creation_logs_activity(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/users', [
                'name' => 'Activity Log User',
                'email' => 'activitylog@example.com',
                'password' => 'password123',
                'role' => 'kasir',
            ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'create',
            'model_type' => 'User',
        ]);
    }

    public function test_user_update_logs_activity(): void
    {
        $user = User::create([
            'name' => 'Activity Edit',
            'email' => 'activityedit@example.com',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        $this->actingAs($this->admin)
            ->put("/admin/users/{$user->id}", [
                'name' => 'Activity Edited',
                'email' => 'activityedit@example.com',
                'role' => 'kasir',
            ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'update',
            'model_type' => 'User',
        ]);
    }

    public function test_user_deletion_logs_activity(): void
    {
        $user = User::create([
            'name' => 'Activity Delete',
            'email' => 'activitydelete@example.com',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        $this->actingAs($this->admin)
            ->delete("/admin/users/{$user->id}");

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'delete',
            'model_type' => 'User',
        ]);
    }

    // ==================== VIEW ACCESS TESTS ====================

    public function test_user_create_view_can_be_rendered(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/users/create');

        $response->assertStatus(200);
    }

    public function test_user_edit_view_can_be_rendered(): void
    {
        $response = $this->actingAs($this->admin)
            ->get("/admin/users/{$this->kasir->id}/edit");

        $response->assertStatus(200);
    }
}
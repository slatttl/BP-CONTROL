<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_user_management_page(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('users.index'));

        $response->assertOk();
        $response->assertSee('Sprava pouzivatelov');
    }

    public function test_non_admin_cannot_view_user_management_page(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $response = $this->actingAs($user)->get(route('users.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_create_user(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Test Managed User',
            'email' => 'managed@example.com',
            'password' => 'secret123',
            'is_admin' => '1',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'managed@example.com',
            'is_admin' => true,
        ]);
    }
}
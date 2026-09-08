<?php

namespace Tests\Feature\Settings;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_a_new_account_with_a_role(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('users.store'), [
            'name' => 'New Admin',
            'email' => 'new-admin@example.com',
            'role' => UserRole::ContentCreator->value,
            'password' => 'password123!',
            'password_confirmation' => 'password123!',
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'new-admin@example.com',
            'name' => 'New Admin',
            'role' => UserRole::ContentCreator->value,
        ]);
    }

    public function test_create_account_requires_matching_password_confirmation(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('users.store'), [
            'name' => 'New Admin',
            'email' => 'new-admin@example.com',
            'role' => UserRole::ContentCreator->value,
            'password' => 'password123!',
            'password_confirmation' => 'not-matching',
        ]);

        $response->assertSessionHasErrors(['password']);

        $this->assertDatabaseMissing('users', [
            'email' => 'new-admin@example.com',
        ]);
    }

    public function test_non_super_admin_roles_cannot_create_accounts(): void
    {
        $viewOnly = User::factory()->viewOnly()->create();
        $bankDataAdmin = User::factory()->bankDataAdmin()->create();
        $contentCreator = User::factory()->contentCreator()->create();

        $payload = [
            'name' => 'Sneaky User',
            'email' => 'sneaky@example.com',
            'role' => UserRole::SuperAdmin->value,
            'password' => 'password123!',
            'password_confirmation' => 'password123!',
        ];

        $this->actingAs($viewOnly)->post(route('users.store'), $payload)->assertForbidden();
        $this->actingAs($bankDataAdmin)->post(route('users.store'), $payload)->assertForbidden();
        $this->actingAs($contentCreator)->post(route('users.store'), $payload)->assertForbidden();
    }
}

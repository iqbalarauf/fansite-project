<?php

namespace Tests\Feature\Settings;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AddAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_add_account_page(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('add-account.edit'))
            ->assertOk()
            ->assertSee('Add New Account');
    }

    public function test_non_super_admin_roles_cannot_access_add_account_page(): void
    {
        $this->actingAs(User::factory()->viewOnly()->create());
        $this->get(route('add-account.edit'))->assertForbidden();

        $this->actingAs(User::factory()->bankDataAdmin()->create());
        $this->get(route('add-account.edit'))->assertForbidden();

        $this->actingAs(User::factory()->contentCreator()->create());
        $this->get(route('add-account.edit'))->assertForbidden();
    }

    public function test_super_admin_can_create_a_new_account_with_a_role(): void
    {
        $this->actingAs(User::factory()->create());

        $response = Livewire::test('pages::settings.add-account')
            ->set('name', 'New Admin')
            ->set('email', 'new-admin@example.com')
            ->set('role', UserRole::ContentCreator->value)
            ->set('password', 'password123!')
            ->set('password_confirmation', 'password123!')
            ->call('save');

        $response->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'new-admin@example.com',
            'name' => 'New Admin',
            'role' => UserRole::ContentCreator->value,
        ]);
    }

    public function test_add_account_requires_matching_password_confirmation(): void
    {
        $this->actingAs(User::factory()->create());

        $response = Livewire::test('pages::settings.add-account')
            ->set('name', 'New Admin')
            ->set('email', 'new-admin@example.com')
            ->set('role', UserRole::ContentCreator->value)
            ->set('password', 'password123!')
            ->set('password_confirmation', 'not-matching')
            ->call('save');

        $response->assertHasErrors(['password']);

        $this->assertDatabaseMissing('users', [
            'email' => 'new-admin@example.com',
        ]);
    }
}

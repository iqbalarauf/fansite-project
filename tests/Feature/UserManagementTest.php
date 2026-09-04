<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_super_admin_can_view_users_list(): void
    {
        $superAdmin = User::factory()->create();
        $targetUser = User::factory()->viewOnly()->create([
            'name' => 'Target View Only',
            'email' => 'targetviewonly@example.com',
        ]);

        $response = $this->actingAs($superAdmin)->get(route('users.index'));

        $response->assertOk();
        $response->assertSee('Daftar User');
        $response->assertSee($targetUser->name);
        $response->assertSee($targetUser->email);
    }

    public function test_super_admin_can_search_and_filter_users(): void
    {
        $superAdmin = User::factory()->create();
        $userA = User::factory()->create([
            'name' => 'Alfa Romeo User',
            'email' => 'alfa@example.com',
            'role' => UserRole::BankDataAdmin,
        ]);
        $userB = User::factory()->create([
            'name' => 'Bravo Delta User',
            'email' => 'bravo@example.com',
            'role' => UserRole::ContentCreator,
        ]);

        $response = $this->actingAs($superAdmin)->get(route('users.index', [
            'search' => 'Alfa',
        ]));

        $response->assertOk();
        $response->assertSee('Alfa Romeo User');
        $response->assertDontSee('Bravo Delta User');

        $responseRole = $this->actingAs($superAdmin)->get(route('users.index', [
            'role' => UserRole::ContentCreator->value,
        ]));

        $responseRole->assertOk();
        $responseRole->assertSee('Bravo Delta User');
        $responseRole->assertDontSee('Alfa Romeo User');
    }

    public function test_super_admin_can_update_user_information(): void
    {
        $superAdmin = User::factory()->create();
        $user = User::factory()->viewOnly()->create([
            'name' => 'Old Name',
            'email' => 'oldemail@example.com',
        ]);

        $response = $this->actingAs($superAdmin)->put(route('users.update', $user), [
            'name' => 'New Name',
            'email' => 'newemail@example.com',
            'role' => UserRole::BankDataAdmin->value,
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('New Name', $user->name);
        $this->assertSame('newemail@example.com', $user->email);
        $this->assertSame(UserRole::BankDataAdmin, $user->role);
    }

    public function test_super_admin_can_update_user_password(): void
    {
        $superAdmin = User::factory()->create();
        $user = User::factory()->create([
            'password' => Hash::make('old-password-123'),
        ]);

        $response = $this->actingAs($superAdmin)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'password' => 'new-secret-password-123',
            'password_confirmation' => 'new-secret-password-123',
        ]);

        $response->assertRedirect(route('users.index'));

        $this->assertTrue(Hash::check('new-secret-password-123', $user->refresh()->password));
    }

    public function test_super_admin_cannot_demote_own_role(): void
    {
        $superAdmin = User::factory()->create([
            'role' => UserRole::SuperAdmin,
        ]);

        $response = $this->actingAs($superAdmin)->put(route('users.update', $superAdmin), [
            'name' => $superAdmin->name,
            'email' => $superAdmin->email,
            'role' => UserRole::ViewOnly->value,
        ]);

        $response->assertSessionHasErrors(['role']);

        $this->assertSame(UserRole::SuperAdmin, $superAdmin->refresh()->role);
    }

    public function test_super_admin_can_delete_other_user(): void
    {
        $superAdmin = User::factory()->create();
        $userToDelete = User::factory()->create();

        $response = $this->actingAs($superAdmin)->delete(route('users.destroy', $userToDelete));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', [
            'id' => $userToDelete->id,
        ]);
    }

    public function test_super_admin_cannot_delete_own_account(): void
    {
        $superAdmin = User::factory()->create();

        $response = $this->actingAs($superAdmin)->delete(route('users.destroy', $superAdmin));

        $response->assertSessionHasErrors(['error']);

        $this->assertDatabaseHas('users', [
            'id' => $superAdmin->id,
        ]);
    }

    public function test_non_super_admin_roles_cannot_access_user_management(): void
    {
        $viewOnlyUser = User::factory()->viewOnly()->create();
        $bankDataAdminUser = User::factory()->bankDataAdmin()->create();
        $contentCreatorUser = User::factory()->contentCreator()->create();
        $dummyUser = User::factory()->create();

        // View-only user
        $this->actingAs($viewOnlyUser)->get(route('users.index'))->assertForbidden();
        $this->actingAs($viewOnlyUser)->put(route('users.update', $dummyUser), [
            'name' => 'Hacked Name',
            'email' => $dummyUser->email,
            'role' => UserRole::SuperAdmin->value,
        ])->assertForbidden();
        $this->actingAs($viewOnlyUser)->delete(route('users.destroy', $dummyUser))->assertForbidden();

        // Bank data admin user
        $this->actingAs($bankDataAdminUser)->get(route('users.index'))->assertForbidden();
        $this->actingAs($bankDataAdminUser)->put(route('users.update', $dummyUser), [
            'name' => 'Hacked Name',
            'email' => $dummyUser->email,
            'role' => UserRole::SuperAdmin->value,
        ])->assertForbidden();
        $this->actingAs($bankDataAdminUser)->delete(route('users.destroy', $dummyUser))->assertForbidden();

        // Content creator user
        $this->actingAs($contentCreatorUser)->get(route('users.index'))->assertForbidden();
        $this->actingAs($contentCreatorUser)->put(route('users.update', $dummyUser), [
            'name' => 'Hacked Name',
            'email' => $dummyUser->email,
            'role' => UserRole::SuperAdmin->value,
        ])->assertForbidden();
        $this->actingAs($contentCreatorUser)->delete(route('users.destroy', $dummyUser))->assertForbidden();
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get(route('users.index'))->assertRedirect(route('login'));
    }
}

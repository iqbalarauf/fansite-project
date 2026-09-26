<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Support\ListingQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $filters = ListingQuery::from($request, ['name', 'email', 'role', 'created_at'], 'created_at', [
            'role' => '',
        ]);

        $users = User::query()
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $query->where(function ($nestedQuery) use ($filters): void {
                    $nestedQuery->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('email', 'like', "%{$filters['search']}%");
                });
            })
            ->when($filters['role'] !== '', function ($query) use ($filters): void {
                $query->where('role', $filters['role']);
            })
            ->orderBy($filters['sort_by'], $filters['sort_dir'])
            ->paginate($filters['per_page'])
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'filters' => $filters,
            'roles' => UserRole::cases(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
        ]);

        $user->sendEmailVerificationNotification();

        return redirect()->route('users.index')
            ->with('success', 'User '.$validated['name'].' berhasil ditambahkan. Verifikasi email telah dikirim ke '.$validated['email'].'.');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);
        Gate::authorize('changeRole', $user);

        $validated = $request->validated();

        $isSelf = $user->is(auth()->user());

        if ($isSelf && $validated['role'] !== UserRole::SuperAdmin->value) {
            return back()->withErrors(['role' => 'Anda tidak dapat mengubah role akun Anda sendiri.']);
        }

        if ($this->isLastSuperAdmin($user) && $validated['role'] !== UserRole::SuperAdmin->value) {
            return back()->withErrors(['role' => 'Super Admin terakhir tidak dapat diturunkan rolenya.']);
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = UserRole::from($validated['role']);

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'User '.$user->name.' berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        if ($this->isLastSuperAdmin($user)) {
            return back()->withErrors(['error' => 'Super Admin terakhir tidak dapat dihapus.']);
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User '.$userName.' berhasil dihapus.');
    }

    /**
     * Cegah hilangnya akses Super Admin terakhir.
     */
    private function isLastSuperAdmin(User $user): bool
    {
        if (! $user->isSuperAdmin()) {
            return false;
        }

        return User::query()->where('role', UserRole::SuperAdmin)->count() <= 1;
    }
}

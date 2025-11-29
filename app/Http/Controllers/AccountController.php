<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Actions\Fortify\CreateNewUser;

class AccountController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->orderByDesc('created_at')
            ->paginate(20)
            ->through(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'created_at' => $u->created_at,
            ]);

        return Inertia::render('Accounts/Manage', [
            'users' => $users,
        ]);
    }

    public function destroy(User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return back()->with('flash', ['banner' => 'You cannot delete your own account.', 'bannerStyle' => 'danger']);
        }

        $user->delete();

        return back()->with('flash', ['banner' => 'Account deleted.', 'bannerStyle' => 'success']);
    }

    public function register(Request $request, CreateNewUser $creator): RedirectResponse
    {
        // Use Fortify's CreateNewUser action to validate and create the user
        $user = $creator->create($request->all());

        // Stay logged in as current user and go back to Accounts page
        return redirect()
            ->route('accounts.manage')
            ->with('flash', ['banner' => 'Account created successfully.', 'bannerStyle' => 'success']);
    }
}

<?php

namespace App\Policies;

use App\Models\User;

class MerchandiseProductPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, mixed $product): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, mixed $product): bool
    {
        return $user->isSuperAdmin();
    }
}

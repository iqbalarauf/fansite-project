<?php

namespace App\Policies;

use App\Models\User;

/**
 * Otorisasi manajemen user (hanya Super Admin) + proteksi akun sendiri
 * dan perlindungan terhadap penghapusan/penurunan Super Admin terakhir.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, User $target): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, User $target): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, User $target): bool
    {
        return $user->isSuperAdmin() && ! $user->is($target);
    }

    /**
     * Apakah user boleh menurunkan/mengubah role target dari Super Admin?
     */
    public function changeRole(User $user, User $target): bool
    {
        return $user->isSuperAdmin();
    }
}

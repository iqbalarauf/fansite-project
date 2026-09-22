<?php

namespace App\Policies\Concerns;

use App\Models\User;

/**
 * Aturan otorisasi bersama untuk konten yang dikelola Content Management
 * (Pages, Majalah, News/Blog) sesuai matriks peran di PRD.
 */
trait AuthorizesContent
{
    public function before(User $user): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->canAccessPages();
    }

    public function view(User $user): bool
    {
        return $user->canAccessPages();
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, mixed $content): bool
    {
        return $this->canManage($user) && $this->ownsOrIsUnclaimed($user, $content);
    }

    public function delete(User $user, mixed $content): bool
    {
        return $this->update($user, $content);
    }

    public function restore(User $user, mixed $content): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, mixed $content): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Peran yang boleh membaca tidak otomatis boleh menulis (View Only).
     */
    protected function canManage(User $user): bool
    {
        return $user->canAccessPages() && ! $user->isReadOnly();
    }

    /**
     * Content Creator hanya dapat memodifikasi konten miliknya sendiri
     * (konten tanpa pemilik tetap boleh diubah untuk data lama).
     */
    protected function ownsOrIsUnclaimed(User $user, mixed $content): bool
    {
        if (! $user->isContentCreator()) {
            return true;
        }

        $owner = data_get($content, 'created_by');

        return $owner === null || (int) $owner === $user->id;
    }
}

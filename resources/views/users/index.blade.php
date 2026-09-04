<x-layouts::app :title="__('Daftar User')">
    <div class="admin-page">
        <div class="admin-page-header">
            <div>
                <flux:heading size="xl" class="font-bold">{{ __('Daftar User') }}</flux:heading>
                <flux:subheading>{{ __('Kelola akun pengguna, peran akses (role), dan pengaturan keamanan') }}</flux:subheading>
            </div>
            <div class="admin-page-actions">
                <flux:button variant="primary" icon="plus" :href="route('add-account.edit')" wire:navigate>
                    {{ __('Tambah User') }}
                </flux:button>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-950 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-950 dark:text-red-300">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="GET" action="{{ route('users.index') }}" id="filter-form">
            <div class="admin-filter">
                <div class="admin-filter-search">
                    <flux:input
                        name="search"
                        value="{{ $filters['search'] }}"
                        placeholder="{{ __('Cari nama atau email user...') }}"
                        icon="magnifying-glass"
                    />
                </div>

                <select
                    name="role"
                    onchange="this.form.submit()"
                    class="admin-filter-select"
                >
                    <option value="">{{ __('Semua Role') }}</option>
                    @foreach ($roles as $userRole)
                        <option value="{{ $userRole->value }}" {{ $filters['role'] === $userRole->value ? 'selected' : '' }}>
                            {{ $userRole->label() }}
                        </option>
                    @endforeach
                </select>

                <select
                    name="sort_by"
                    onchange="this.form.submit()"
                    class="admin-filter-select"
                >
                    <option value="created_at" {{ $filters['sort_by'] === 'created_at' ? 'selected' : '' }}>{{ __('Tanggal Daftar') }}</option>
                    <option value="name" {{ $filters['sort_by'] === 'name' ? 'selected' : '' }}>{{ __('Nama') }}</option>
                    <option value="email" {{ $filters['sort_by'] === 'email' ? 'selected' : '' }}>{{ __('Email') }}</option>
                    <option value="role" {{ $filters['sort_by'] === 'role' ? 'selected' : '' }}>{{ __('Role') }}</option>
                </select>

                <input type="hidden" name="sort_dir" value="{{ $filters['sort_dir'] }}" id="sort-dir-input" />
                <button
                    type="button"
                    title="{{ $filters['sort_dir'] === 'asc' ? 'Ascending' : 'Descending' }}"
                    onclick="document.getElementById('sort-dir-input').value = '{{ $filters['sort_dir'] === 'asc' ? 'desc' : 'asc' }}'; document.getElementById('filter-form').submit();"
                    class="admin-filter-sort"
                >
                    @if ($filters['sort_dir'] === 'asc')
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4"/></svg>
                    @endif
                </button>

                <input type="hidden" name="per_page" value="{{ $filters['per_page'] }}" />

                <flux:button type="submit" variant="outline">{{ __('Cari') }}</flux:button>

                @if ($filters['search'] || $filters['role'])
                    <a href="{{ route('users.index') }}" class="admin-filter-reset">{{ __('Reset') }}</a>
                @endif
            </div>
        </form>

        <div class="admin-table-shell">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead class="admin-table-head">
                        <tr>
                            <th class="px-4 py-3 text-start">{{ __('User') }}</th>
                            <th class="px-4 py-3 text-start">{{ __('Role') }}</th>
                            <th class="px-4 py-3 text-start">{{ __('Status Email') }}</th>
                            <th class="px-4 py-3 text-start">{{ __('Terdaftar') }}</th>
                            <th class="px-4 py-3 text-end">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($users as $user)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <flux:avatar
                                            :name="$user->name"
                                            :initials="$user->initials()"
                                            class="size-9 shrink-0"
                                        />
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $user->name }}</p>
                                                @if ($user->id === auth()->id())
                                                    <span class="rounded bg-blue-50 px-1.5 py-0.5 text-[10px] font-semibold text-blue-600 dark:bg-blue-950 dark:text-blue-400">
                                                        {{ __('Anda') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $badgeColor = match ($user->role) {
                                            \App\Enums\UserRole::SuperAdmin => 'indigo',
                                            \App\Enums\UserRole::BankDataAdmin => 'emerald',
                                            \App\Enums\UserRole::ContentCreator => 'amber',
                                            \App\Enums\UserRole::ViewOnly => 'zinc',
                                            default => 'zinc',
                                        };
                                    @endphp
                                    <flux:badge size="sm" :color="$badgeColor">
                                        {{ $user->role?->label() ?? $user->role?->value }}
                                    </flux:badge>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($user->email_verified_at)
                                        <flux:badge size="sm" color="green" icon="check">
                                            {{ __('Terverifikasi') }}
                                        </flux:badge>
                                    @else
                                        <flux:badge size="sm" color="zinc">
                                            {{ __('Belum Verifikasi') }}
                                        </flux:badge>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $user->created_at?->format('d M Y, H:i') ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            type="button"
                                            class="admin-action-link"
                                            data-id="{{ $user->id }}"
                                            data-name="{{ $user->name }}"
                                            data-email="{{ $user->email }}"
                                            data-role="{{ $user->role?->value }}"
                                            data-is-self="{{ $user->id === auth()->id() ? '1' : '0' }}"
                                            onclick="openEditUserModal(this)"
                                        >
                                            {{ __('Edit') }}
                                        </button>

                                        @if ($user->id === auth()->id())
                                            <span class="text-xs text-zinc-400 dark:text-zinc-600" title="{{ __('Anda tidak dapat menghapus akun Anda sendiri') }}">
                                                -
                                            </span>
                                        @else
                                            <button
                                                type="button"
                                                class="admin-action-danger"
                                                data-id="{{ $user->id }}"
                                                data-name="{{ $user->name }}"
                                                data-email="{{ $user->email }}"
                                                onclick="openDeleteUserModal(this)"
                                            >
                                                {{ __('Delete') }}
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-16 text-center text-zinc-500 dark:text-zinc-400">
                                    <div class="flex flex-col items-center gap-3">
                                        <flux:icon.users class="size-10 text-zinc-300 dark:text-zinc-600" />
                                        <p class="font-medium">{{ __('Tidak ada user ditemukan') }}</p>
                                        <p class="text-xs">{{ __('Coba ubah kata kunci pencarian atau filter role.') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('show-teater.partials.pagination', ['paginator' => $users, 'perPage' => $filters['per_page'], 'pageParam' => 'page'])
        </div>
    </div>

    {{-- Modal Edit User --}}
    <flux:modal name="modal-edit-user" class="md:w-[560px]" variant="flyout">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Edit User') }}</flux:heading>
                <flux:subheading>{{ __('Perbarui informasi akun, role, atau kata sandi') }}</flux:subheading>
            </div>

            <form method="POST" id="edit-user-form" action="" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <flux:label for="edit-user-name">{{ __('Nama') }}</flux:label>
                    <flux:input id="edit-user-name" name="name" required class="mt-1" />
                </div>

                <div>
                    <flux:label for="edit-user-email">{{ __('Email') }}</flux:label>
                    <flux:input id="edit-user-email" name="email" type="email" required class="mt-1" />
                </div>

                <div>
                    <flux:label for="edit-user-role">{{ __('Role') }}</flux:label>
                    <select id="edit-user-role" name="role" required class="admin-filter-select w-full mt-1">
                        @foreach ($roles as $userRole)
                            <option value="{{ $userRole->value }}">{{ $userRole->label() }}</option>
                        @endforeach
                    </select>
                    <p id="edit-self-warning" class="mt-1 text-xs text-amber-600 dark:text-amber-400 hidden">
                        {{ __('Role akun Anda sendiri harus tetap Super Admin.') }}
                    </p>
                </div>

                <flux:separator class="my-4" />

                <div>
                    <flux:heading size="sm">{{ __('Ganti Password (Opsional)') }}</flux:heading>
                    <flux:subheading class="text-xs">{{ __('Biarkan kosong jika tidak ingin mengubah password user') }}</flux:subheading>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <flux:label for="edit-user-password">{{ __('Password Baru') }}</flux:label>
                        <flux:input id="edit-user-password" name="password" type="password" class="mt-1" autocomplete="new-password" viewable />
                    </div>
                    <div>
                        <flux:label for="edit-user-password-confirmation">{{ __('Konfirmasi Password') }}</flux:label>
                        <flux:input id="edit-user-password-confirmation" name="password_confirmation" type="password" class="mt-1" autocomplete="new-password" viewable />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Batal') }}</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">{{ __('Simpan Perubahan') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- Modal Delete User --}}
    <flux:modal name="modal-delete-user" class="max-w-md md:min-w-md">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Hapus User') }}</flux:heading>
                <flux:text>
                    {{ __('Apakah Anda yakin ingin menghapus user') }} <span id="delete-user-name" class="font-semibold text-zinc-900 dark:text-zinc-100"></span> (<span id="delete-user-email"></span>)?
                </flux:text>
                <flux:text class="text-xs text-red-500">
                    {{ __('Tindakan ini tidak dapat dibatalkan dan seluruh data terkait akan dihapus.') }}
                </flux:text>
            </div>

            <form method="POST" id="delete-user-form" action="" class="flex gap-3 justify-end">
                @csrf
                @method('DELETE')
                <flux:modal.close>
                    <flux:button variant="outline">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="danger">{{ __('Hapus User') }}</flux:button>
            </form>
        </div>
    </flux:modal>

    <script>
        function openEditUserModal(target) {
            const userData = target?.dataset ? {
                id: target.dataset.id,
                name: target.dataset.name,
                email: target.dataset.email,
                role: target.dataset.role,
                isSelf: target.dataset.isSelf === '1',
            } : target;

            document.getElementById('edit-user-form').action = `{{ url('users') }}/${userData.id}`;
            document.getElementById('edit-user-name').value = userData.name || '';
            document.getElementById('edit-user-email').value = userData.email || '';
            document.getElementById('edit-user-role').value = userData.role || '';
            document.getElementById('edit-user-password').value = '';
            document.getElementById('edit-user-password-confirmation').value = '';

            const warning = document.getElementById('edit-self-warning');
            const roleSelect = document.getElementById('edit-user-role');
            if (userData.isSelf) {
                warning.classList.remove('hidden');
                roleSelect.disabled = true;
                // Add hidden input if disabled so form submission still sends role
                let hiddenRole = document.getElementById('edit-user-role-hidden');
                if (!hiddenRole) {
                    hiddenRole = document.createElement('input');
                    hiddenRole.type = 'hidden';
                    hiddenRole.name = 'role';
                    hiddenRole.id = 'edit-user-role-hidden';
                    document.getElementById('edit-user-form').appendChild(hiddenRole);
                }
                hiddenRole.value = userData.role;
            } else {
                warning.classList.add('hidden');
                roleSelect.disabled = false;
                const hiddenRole = document.getElementById('edit-user-role-hidden');
                if (hiddenRole) {
                    hiddenRole.remove();
                }
            }

            Flux.modal('modal-edit-user').show();
        }

        function openDeleteUserModal(target) {
            const userData = target?.dataset ? {
                id: target.dataset.id,
                name: target.dataset.name,
                email: target.dataset.email,
            } : target;

            document.getElementById('delete-user-form').action = `{{ url('users') }}/${userData.id}`;
            document.getElementById('delete-user-name').textContent = userData.name || '';
            document.getElementById('delete-user-email').textContent = userData.email || '';

            Flux.modal('modal-delete-user').show();
        }
    </script>
</x-layouts::app>

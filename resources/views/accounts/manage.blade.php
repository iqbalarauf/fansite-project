<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Accounts</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('flash'))
                <div class="rounded-3xl bg-white p-4 border border-gray-200 text-sm text-gray-700">
                    {{ session('flash.banner') }}
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-3xl border border-gray-200 p-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Name</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Email</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Created</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($users as $user)
                            <tr>
                                <td class="px-4 py-4">{{ $user['name'] }}</td>
                                <td class="px-4 py-4">{{ $user['email'] }}</td>
                                <td class="px-4 py-4">{{ optional($user['created_at'])->diffForHumans() }}</td>
                                <td class="px-4 py-4 space-x-2">
                                    @if (auth()->id() !== $user['id'])
                                        <form action="{{ route('accounts.destroy', $user['id']) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                        </form>
                                    @else
                                        <span class="text-gray-500">Current user</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-6 text-center text-gray-500" colspan="4">No accounts found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white shadow-sm rounded-3xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900">Register New Account</h3>
                <form action="{{ route('register') }}" method="POST" class="mt-6 grid gap-6 lg:grid-cols-3">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input name="name" value="{{ old('name') }}" type="text" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input name="email" value="{{ old('email') }}" type="email" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password</label>
                        <input name="password" type="password" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" autocomplete="new-password" />
                        @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <input name="password_confirmation" type="password" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" autocomplete="new-password" />
                    </div>
                    <div class="lg:col-span-3 text-right">
                        <x-primary-button>Register</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

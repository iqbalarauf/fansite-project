<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Show Teater Categories</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="rounded-3xl bg-green-50 border border-green-200 p-4 text-green-800">{{ session('success') }}</div>
            @endif

            <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Type</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Name</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Setlist</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($categories as $category)
                            <tr>
                                <td class="px-4 py-4 capitalize">{{ $category->type }}</td>
                                <td class="px-4 py-4">{{ $category->display_name }}</td>
                                <td class="px-4 py-4">{{ $category->setlist_name ?? '-' }}</td>
                                <td class="px-4 py-4">
                                    <form action="{{ route('show-teater.categories.destroy', $category->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900">Add Category</h3>
                <form action="{{ route('show-teater.categories.store') }}" method="POST" class="mt-6 grid gap-6 lg:grid-cols-3">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Type</label>
                        <select name="type" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
                            <option value="setlist">Setlist</option>
                            <option value="unit_song">Unit Song</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input name="name" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Setlist</label>
                        <select name="setlist_id" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
                            <option value="">None</option>
                            @foreach ($setlists as $setlist)
                                <option value="{{ $setlist->id }}">{{ $setlist->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="lg:col-span-3 text-right">
                        <x-primary-button>Add Category</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

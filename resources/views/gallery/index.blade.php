<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage Gallery</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="rounded-3xl bg-green-50 border border-green-200 p-4 text-green-800">{{ session('success') }}</div>
            @endif

            <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900">Add New Gallery Item</h3>
                <form action="{{ route('gallery.store') }}" method="POST" enctype="multipart/form-data" class="mt-6 grid gap-6 lg:grid-cols-2">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Type</label>
                        <select name="type" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
                            <option value="photo">Photo</option>
                            <option value="video">Video</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Title</label>
                        <input name="title" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="3" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Credit</label>
                        <input name="credit" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Photo</label>
                        <input type="file" name="image" class="mt-1 block w-full text-gray-700" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Video URL</label>
                        <input name="video_url" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Order</label>
                        <input name="order" type="number" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="is_published" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm" />
                            Publish now
                        </label>
                    </div>
                    <div class="lg:col-span-2 text-right">
                        <x-primary-button>Add Item</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 overflow-x-auto">
                <h3 class="text-lg font-semibold text-gray-900">Gallery Items</h3>
                <table class="min-w-full divide-y divide-gray-200 text-sm mt-5">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Title</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Type</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Published</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($galleries as $item)
                            <tr>
                                <td class="px-4 py-4">{{ $item['title'] }}</td>
                                <td class="px-4 py-4 capitalize">{{ $item['type'] }}</td>
                                <td class="px-4 py-4">{{ $item['is_published'] ? 'Yes' : 'No' }}</td>
                                <td class="px-4 py-4">
                                    <form action="{{ route('gallery.destroy', $item['id']) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-6 text-center text-gray-500" colspan="4">No gallery items found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

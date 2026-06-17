<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage Pages</h2>
            <a href="{{ route('pages.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700">New Page</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm rounded-3xl border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Title</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Updated</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($pages as $page)
                            <tr>
                                <td class="px-4 py-4">{{ $page->title }}</td>
                                <td class="px-4 py-4 capitalize">{{ $page->status }}</td>
                                <td class="px-4 py-4">{{ optional($page->updated_at)->diffForHumans() }}</td>
                                <td class="px-4 py-4 space-x-2">
                                    <a href="{{ route('pages.edit', $page->id) }}" class="text-indigo-600 hover:underline">Edit</a>
                                    <form action="{{ route('pages.destroy', $page->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-6 text-center text-gray-500" colspan="4">No pages available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-3xl border border-gray-200 p-6">
                {{ $pages->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

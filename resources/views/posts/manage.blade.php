<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage Posts</h2>
            <a href="{{ route('posts.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700">New Post</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('flash'))
                <div class="rounded-3xl bg-white p-4 border border-gray-200 text-sm text-gray-700">
                    {{ session('flash.banner') }}
                </div>
            @endif

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
                        @forelse ($posts as $post)
                            <tr>
                                <td class="px-4 py-4">{{ $post->title }}</td>
                                <td class="px-4 py-4 capitalize">{{ $post->status }}</td>
                                <td class="px-4 py-4">{{ optional($post->updated_at)->diffForHumans() }}</td>
                                <td class="px-4 py-4 space-x-2">
                                    <a href="{{ route('blog.show', $post->id) }}" class="text-indigo-600 hover:underline">View</a>
                                    <a href="{{ route('posts.edit', $post->id) }}" class="text-indigo-600 hover:underline">Edit</a>
                                    <form action="{{ route('posts.destroy', $post->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-6 text-center text-gray-500" colspan="4">No posts available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-3xl border border-gray-200 p-6">
                {{ $posts->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

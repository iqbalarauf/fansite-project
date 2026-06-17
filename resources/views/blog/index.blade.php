<x-public-layout>
    <div class="space-y-8">
        <div class="space-y-2">
            <h1 class="text-4xl font-bold text-gray-900">Blog</h1>
            <p class="text-gray-600">Latest published posts from the fansite.</p>
        </div>

        @forelse ($posts as $post)
            <article class="bg-white border border-gray-200 rounded-3xl p-6 shadow-sm">
                <div class="md:flex md:items-start md:gap-6">
                    @if ($post['thumbnail'])
                        <img src="{{ $post['thumbnail'] }}" alt="{{ $post['title'] }}" class="w-full max-w-sm rounded-2xl object-cover mb-4 md:mb-0" />
                    @endif

                    <div class="flex-1">
                        <a href="{{ route('blog.show', $post['slug']) }}" class="text-2xl font-semibold text-gray-900 hover:text-indigo-600">{{ $post['title'] }}</a>
                        <p class="mt-2 text-sm text-gray-500">{{ optional($post['published_at'])->format('d M Y') }}</p>
                        <p class="mt-4 text-gray-700">{{ $post['excerpt'] }}</p>
                        <div class="mt-4">
                            <a href="{{ route('blog.show', $post['slug']) }}" class="text-indigo-600 hover:underline">Read more</a>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="bg-white border border-gray-200 rounded-3xl p-6 text-gray-700">No blog posts found.</div>
        @endforelse

        <div class="mt-6">
            {{ $posts->links() }}
        </div>
    </div>
</x-public-layout>

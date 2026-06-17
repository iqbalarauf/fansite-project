<x-public-layout>
    <article class="prose prose-lg prose-slate max-w-none dark:prose-invert">
        <div class="space-y-4">
            <div>
                <h1 class="text-4xl font-bold text-gray-900">{{ $post['title'] }}</h1>
                <p class="text-sm text-gray-500">By {{ $post['user']['name'] }} • {{ optional($post['published_at'])->format('d M Y') }}</p>
            </div>

            @if ($post['featured_image'])
                <img src="{{ $post['featured_image'] }}" alt="{{ $post['title'] }}" class="rounded-3xl shadow-lg" />
            @endif

            <div class="text-gray-700">
                {!! $post['body_html'] !!}
            </div>
        </div>
    </article>
</x-public-layout>

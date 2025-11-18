@extends('app')

@section('content')
    <div class="container mx-auto py-8">
        <h1 class="text-3xl font-bold mb-6">Blog</h1>

        @foreach($posts as $post)
            <article class="mb-6 border-b pb-4">
                <h2 class="text-xl font-semibold"><a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a></h2>
                <p class="text-sm text-gray-600">Published: {{ $post->published_at?->toFormattedDateString() }}</p>
                @if($post->excerpt)
                    <p class="mt-2">{{ $post->excerpt }}</p>
                @endif
            </article>
        @endforeach

        <div class="mt-6">
            {{ $posts->links() }}
        </div>
    </div>
@endsection

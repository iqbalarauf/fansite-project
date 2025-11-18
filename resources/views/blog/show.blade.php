@extends('app')

@section('content')
    <div class="container mx-auto py-8">
        <article>
            <h1 class="text-3xl font-bold">{{ $post->title }}</h1>
            <p class="text-sm text-gray-600">By {{ $post->user->name }} | {{ $post->published_at?->toFormattedDateString() }}</p>

            @if($post->featured_image)
                <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="w-full mt-4 mb-4" />
            @endif

            <div class="prose">{!! nl2br(e($post->body)) !!}</div>
        </article>

        @can('update', $post)
            <div class="mt-6">
                <a href="{{ route('posts.edit', $post) }}" class="btn">Edit</a>
                <form action="{{ route('posts.destroy', $post) }}" method="POST" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger" onclick="return confirm('Delete post?')">Delete</button>
                </form>
            </div>
        @endcan
    </div>
@endsection

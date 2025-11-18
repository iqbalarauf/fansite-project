@extends('app')

@section('content')
    <div class="container mx-auto py-8">
        <h1 class="text-2xl font-bold mb-4">Edit Post</h1>

        <form action="{{ route('posts.update', $post) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block">Title</label>
                <input name="title" value="{{ old('title', $post->title) }}" class="w-full" />
            </div>

            <div class="mb-4">
                <label class="block">Excerpt</label>
                <input name="excerpt" value="{{ old('excerpt', $post->excerpt) }}" class="w-full" />
            </div>

            <div class="mb-4">
                <label class="block">Body</label>
                <textarea name="body" rows="8" class="w-full">{{ old('body', $post->body) }}</textarea>
            </div>

            <div class="mb-4">
                <label class="block">Current featured image</label>
                @if($post->featured_image)
                    <div class="mb-2"><img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="w-64" /></div>
                @else
                    <div class="text-sm text-gray-500">No image uploaded.</div>
                @endif
                <label class="block mt-2">Replace featured image</label>
                <input type="file" name="featured_image" />
            </div>

            <div class="mb-4">
                <label class="block">Category</label>
                <input name="category" value="{{ old('category', $post->category) }}" class="w-full" />
            </div>

            <div class="mb-4">
                <label class="block">Tags (comma separated)</label>
                <input name="tags" value="{{ old('tags', is_array($post->tags) ? implode(',', $post->tags) : $post->tags) }}" class="w-full" />
            </div>

            <div class="mb-4">
                <label class="block">Status</label>
                <select name="status">
                    <option value="draft" {{ old('status', $post->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ old('status', $post->status) === 'published' ? 'selected' : '' }}>Published</option>
                </select>
            </div>

            <div class="mb-4">
                <button class="btn">Save changes</button>
            </div>
        </form>
    </div>
@endsection
@extends('app')

@section('content')
    <div class="container mx-auto py-8">
        <h1 class="text-2xl font-bold mb-4">Edit Post</h1>

        <form action="{{ route('posts.update', $post) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block">Title</label>
                <input name="title" value="{{ old('title', $post->title) }}" class="w-full" />
            </div>

            <div class="mb-4">
                <label class="block">Excerpt</label>
                <input name="excerpt" value="{{ old('excerpt', $post->excerpt) }}" class="w-full" />
            </div>

            <div class="mb-4">
                <label class="block">Body</label>
                <textarea name="body" rows="8" class="w-full">{{ old('body', $post->body) }}</textarea>
            </div>

            <div class="mb-4">
                <label class="block">Featured image</label>
                @if($post->featured_image)
                    <img src="{{ Storage::url($post->featured_image) }}" class="w-48 mb-2" />
                @endif
                <input type="file" name="featured_image" />
            </div>

            <div class="mb-4">
                <label class="block">Category</label>
                <input name="category" value="{{ old('category', $post->category) }}" class="w-full" />
            </div>

            <div class="mb-4">
                <label class="block">Tags (comma separated)</label>
                <input name="tags" value="{{ old('tags', is_array($post->tags)?implode(',', $post->tags):$post->tags) }}" class="w-full" />
            </div>

            <div class="mb-4">
                <label class="block">Status</label>
                <select name="status">
                    <option value="draft" {{ $post->status === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ $post->status === 'published' ? 'selected' : '' }}>Published</option>
                </select>
            </div>

            <div class="mb-4">
                <button class="btn">Update</button>
            </div>
        </form>
    </div>
@endsection

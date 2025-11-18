@extends('app')

@section('content')
    <div class="container mx-auto py-8">
        <h1 class="text-2xl font-bold mb-4">Create Post</h1>

        <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label class="block">Title</label>
                <input name="title" value="{{ old('title') }}" class="w-full" />
            </div>

            <div class="mb-4">
                <label class="block">Excerpt</label>
                <input name="excerpt" value="{{ old('excerpt') }}" class="w-full" />
            </div>

            <div class="mb-4">
                <label class="block">Body</label>
                <textarea name="body" rows="8" class="w-full">{{ old('body') }}</textarea>
            </div>

            <div class="mb-4">
                <label class="block">Featured image</label>
                <input type="file" name="featured_image" />
            </div>

            <div class="mb-4">
                <label class="block">Category</label>
                <input name="category" value="{{ old('category') }}" class="w-full" />
            </div>

            <div class="mb-4">
                <label class="block">Tags (comma separated)</label>
                <input name="tags" value="{{ old('tags') }}" class="w-full" />
            </div>

            <div class="mb-4">
                <label class="block">Status</label>
                <select name="status">
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                </select>
            </div>

            <div class="mb-4">
                <button class="btn">Save</button>
            </div>
        </form>
    </div>
@endsection

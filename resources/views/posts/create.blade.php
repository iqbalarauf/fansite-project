<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create New Post</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 bg-white rounded-3xl shadow-sm border border-gray-200 p-6">
            <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Title</label>
                        <input name="title" value="{{ old('title') }}" type="text" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                        @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Slug</label>
                        <input name="slug" value="{{ old('slug') }}" type="text" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                        @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Excerpt</label>
                    <textarea name="excerpt" rows="3" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">{{ old('excerpt') }}</textarea>
                    @error('excerpt')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Body</label>
                    <textarea name="body" rows="8" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">{{ old('body') }}</textarea>
                    @error('body')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Category</label>
                        <input name="category" value="{{ old('category') }}" type="text" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                        @error('category')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tags (comma separated)</label>
                        <input name="tags" value="{{ old('tags') }}" type="text" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                        @error('tags')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
                            <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                        </select>
                        @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Published At</label>
                        <input name="published_at" value="{{ old('published_at') }}" type="datetime-local" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                        @error('published_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Featured Image</label>
                        <input name="featured_image" type="file" class="mt-1 block w-full text-gray-700" />
                        @error('featured_image')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Meta Title</label>
                        <input name="meta_title" value="{{ old('meta_title') }}" type="text" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Meta Description</label>
                        <input name="meta_description" value="{{ old('meta_description') }}" type="text" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Meta Keywords</label>
                        <input name="meta_keywords" value="{{ old('meta_keywords') }}" type="text" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('posts.manage') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-xl text-gray-700">Cancel</a>
                    <x-primary-button>Save Post</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

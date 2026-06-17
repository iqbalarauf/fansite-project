<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Custom Page</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 bg-white rounded-3xl shadow-sm border border-gray-200 p-6">
            <form action="{{ route('pages.update', $page['id']) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700">Title</label>
                    <input name="title" value="{{ old('title', $page['title']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Hero Image</label>
                    @if ($page['hero_image'])
                        <div class="mt-2">
                            <img src="{{ $page['hero_image'] }}" alt="Hero image" class="h-40 w-full rounded-3xl object-cover" />
                        </div>
                    @endif
                    <input type="file" name="hero_image" class="mt-2 block w-full text-gray-700" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Body</label>
                    <textarea name="body" rows="8" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">{{ old('body', $page['body']) }}</textarea>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="has_cta_section" value="1" {{ old('has_cta_section', $page['has_cta_section']) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600" />
                        <label class="text-sm text-gray-700">Enable CTA Section</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="show_in_menu" value="1" {{ old('show_in_menu', $page['show_in_menu']) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600" />
                        <label class="text-sm text-gray-700">Show in menu</label>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">CTA Background Image</label>
                        @if ($page['cta_bg_image'])
                            <div class="mt-2">
                                <img src="{{ $page['cta_bg_image'] }}" alt="CTA background" class="h-40 w-full rounded-3xl object-cover" />
                            </div>
                        @endif
                        <input type="file" name="cta_bg_image" class="mt-2 block w-full text-gray-700" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Menu Order</label>
                        <input type="number" name="menu_order" value="{{ old('menu_order', $page['menu_order']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">CTA Title</label>
                        <input name="cta_title" value="{{ old('cta_title', $page['cta_title']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">CTA Button URL</label>
                        <input name="cta_button_url" value="{{ old('cta_button_url', $page['cta_button_url']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">CTA Description</label>
                        <textarea name="cta_description" rows="3" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">{{ old('cta_description', $page['cta_description']) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">CTA Button Text</label>
                        <input name="cta_button_text" value="{{ old('cta_button_text', $page['cta_button_text']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">CTA Button 2 Text</label>
                        <input name="cta_button_2_text" value="{{ old('cta_button_2_text', $page['cta_button2_text'] ?? $page['cta_button_text']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
                            <option value="draft" {{ old('status', $page['status']) === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status', $page['status']) === 'published' ? 'selected' : '' }}>Published</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Meta Title</label>
                        <input name="meta_title" value="{{ old('meta_title', $page['meta_title']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Meta Description</label>
                        <input name="meta_description" value="{{ old('meta_description', $page['meta_description']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Meta Keywords</label>
                    <input name="meta_keywords" value="{{ old('meta_keywords', $page['meta_keywords']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('pages.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-xl text-gray-700">Cancel</a>
                    <x-primary-button>Update Page</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

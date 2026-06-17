<x-public-layout>
    <div class="space-y-8">
        <div class="space-y-2">
            <h1 class="text-4xl font-bold text-gray-900">Gallery</h1>
            <p class="text-gray-600">Browse published images and videos from the fansite.</p>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($galleries as $item)
                <div class="overflow-hidden rounded-3xl bg-white shadow-sm border border-gray-200">
                    @if ($item['type'] === 'photo' && $item['image_path'])
                        <img src="{{ $item['image_path'] }}" alt="{{ $item['title'] }}" class="h-72 w-full object-cover" />
                    @elseif ($item['type'] === 'video' && $item['video_url'])
                        <div class="relative aspect-[16/9] bg-black">
                            <iframe src="https://www.youtube.com/embed/{{ preg_match('/(?:youtube\.com\/(?:.*v=|embed\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/', $item['video_url'], $m) ? $m[1] : '' }}"
                                class="absolute inset-0 h-full w-full"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen>
                            </iframe>
                        </div>
                    @else
                        <div class="flex h-72 items-center justify-center bg-gray-100 text-gray-500">No preview available</div>
                    @endif

                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-gray-900">{{ $item['title'] }}</h2>
                        @if ($item['credit'])
                            <p class="mt-2 text-sm text-gray-500">Credit: {{ $item['credit'] }}</p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-3xl bg-white border border-gray-200 p-8 text-center text-gray-700">
                    No gallery items available.
                </div>
            @endforelse
        </div>
    </div>
</x-public-layout>

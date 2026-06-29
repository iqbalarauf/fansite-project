<x-layouts::app :title="__('Concerts Events')">
    <div class="flex flex-col gap-6">
        <div class="flex items-start justify-between">
            <div>
                <flux:heading size="xl" class="font-bold">Concerts Events</flux:heading>
                <flux:subheading>Kelola jadwal konser, status on-air/off-air, dan purchase link</flux:subheading>
            </div>
            <flux:modal.trigger name="modal-create-concert-event">
                <flux:button variant="primary" icon="plus">Tambah Event</flux:button>
            </flux:modal.trigger>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-950 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-950 dark:text-red-300">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="GET" action="{{ route('concert-events.index') }}" id="filter-form">
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex-1 min-w-60">
                    <flux:input
                        name="search"
                        value="{{ $filters['search'] }}"
                        placeholder="Search event name, location, atau purchase link..."
                        icon="magnifying-glass"
                    />
                </div>

                <flux:input
                    name="date_from"
                    type="date"
                    value="{{ $filters['date_from'] }}"
                    class="w-40"
                />
                <flux:input
                    name="date_to"
                    type="date"
                    value="{{ $filters['date_to'] }}"
                    class="w-40"
                />

                <select
                    name="status"
                    onchange="this.form.submit()"
                    class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-700 shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300"
                >
                    <option value="">Semua Status</option>
                    <option value="on-air" {{ $filters['status'] === 'on-air' ? 'selected' : '' }}>On-Air</option>
                    <option value="off-air" {{ $filters['status'] === 'off-air' ? 'selected' : '' }}>Off-Air</option>
                </select>

                <select
                    name="sort_by"
                    onchange="this.form.submit()"
                    class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-700 shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300"
                >
                    <option value="event_date" {{ $filters['sort_by'] === 'event_date' ? 'selected' : '' }}>Event Date</option>
                    <option value="event_name" {{ $filters['sort_by'] === 'event_name' ? 'selected' : '' }}>Event Name</option>
                    <option value="purchase_link" {{ $filters['sort_by'] === 'purchase_link' ? 'selected' : '' }}>Purchase Link</option>
                </select>

                <input type="hidden" name="sort_dir" value="{{ $filters['sort_dir'] }}" id="sort-dir-input" />
                <button
                    type="button"
                    title="{{ $filters['sort_dir'] === 'asc' ? 'Ascending' : 'Descending' }}"
                    onclick="document.getElementById('sort-dir-input').value = '{{ $filters['sort_dir'] === 'asc' ? 'desc' : 'asc' }}'; document.getElementById('filter-form').submit();"
                    class="flex items-center justify-center rounded-lg border border-zinc-200 bg-white p-2 text-zinc-600 shadow-xs hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800"
                >
                    @if ($filters['sort_dir'] === 'asc')
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4"/></svg>
                    @endif
                </button>

                <input type="hidden" name="per_page" value="{{ $filters['per_page'] }}" />

                <flux:button type="submit" variant="outline">Cari</flux:button>

                @if ($filters['search'] || $filters['status'] || $filters['date_from'] || $filters['date_to'])
                    <a href="{{ route('concert-events.index') }}" class="text-sm text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300">Reset</a>
                @endif
            </div>
        </form>

        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">EVENT NAME</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">DATE</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">LOCATION</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">STATUS</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">PURCHASE LINK</th>
                            <th class="px-4 py-3 text-right font-medium text-zinc-500 dark:text-zinc-400">ACTION</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($events as $event)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                <td class="px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200">{{ $event->event_name }}</td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $event->event_date?->translatedFormat('d F Y') }}</td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $event->location }}</td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                    @if ($event->status === 'on-air')
                                        <flux:badge color="lime" size="sm">On-Air</flux:badge>
                                    @else
                                        <flux:badge color="zinc" size="sm">Off-Air</flux:badge>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                    @if ($event->purchase_link)
                                        <a href="{{ $event->purchase_link }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">Link</a>
                                    @else
                                        <span>–</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <button
                                            type="button"
                                            class="text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                            data-id="{{ $event->id }}"
                                            data-event-name="{{ $event->event_name }}"
                                            data-event-date="{{ $event->event_date?->format('Y-m-d') }}"
                                            data-location="{{ $event->location }}"
                                            data-status="{{ $event->status }}"
                                            data-purchase-link="{{ $event->purchase_link }}"
                                            onclick="openEditConcertModal(this)"
                                        >
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('concert-events.destroy', $event) }}" onsubmit="return confirm('Hapus event ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm font-medium text-red-500 hover:text-red-700">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-16 text-center text-zinc-500 dark:text-zinc-400">
                                    <div class="flex flex-col items-center gap-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-10 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5M8.25 12h7.5m-7.5 5.25h7.5M3 4.5A2.25 2.25 0 015.25 2.25h13.5A2.25 2.25 0 0121 4.5v15A2.25 2.25 0 0118.75 21.75H5.25A2.25 2.25 0 013 19.5v-15z"/></svg>
                                        <p class="font-medium">Belum ada concert event</p>
                                        <p class="text-xs">Tambah event baru dari tombol di kanan atas.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('show-teater.partials.pagination', ['paginator' => $events, 'perPage' => $filters['per_page'], 'pageParam' => 'page'])
        </div>
    </div>

    <flux:modal name="modal-create-concert-event" class="md:w-[620px]" variant="flyout">
        <div class="space-y-6">
            <flux:heading size="lg">Tambah Concert Event</flux:heading>

            <form method="POST" action="{{ route('concert-events.store') }}" class="space-y-5">
                @csrf

                <div>
                    <flux:label for="create-event-name">Event Name</flux:label>
                    <flux:input id="create-event-name" name="event_name" required class="mt-1" />
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <flux:label for="create-event-date">Event Date</flux:label>
                        <flux:input id="create-event-date" name="event_date" type="date" required class="mt-1" />
                    </div>
                    <div>
                        <flux:label for="create-location">Location</flux:label>
                        <flux:input id="create-location" name="location" required class="mt-1" />
                    </div>
                </div>

                <div>
                    <flux:label>Status</flux:label>
                    <div class="mt-2 flex flex-wrap gap-4">
                        <label class="inline-flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-200">
                            <input type="radio" name="status" value="off-air" class="rounded border-zinc-300 text-blue-600" checked>
                            <span>Off-Air</span>
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-200">
                            <input type="radio" name="status" value="on-air" class="rounded border-zinc-300 text-blue-600">
                            <span>On-Air</span>
                        </label>
                    </div>
                </div>

                <div>
                    <flux:label for="create-purchase-link">Purchase Link</flux:label>
                    <flux:input id="create-purchase-link" name="purchase_link" type="url" class="mt-1" placeholder="https://" />
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">Simpan</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <flux:modal name="modal-edit-concert-event" class="md:w-[620px]" variant="flyout">
        <div class="space-y-6" id="edit-concert-event-root">
            <flux:heading size="lg">Edit Concert Event</flux:heading>

            <form method="POST" id="edit-concert-event-form" action="" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <flux:label for="edit-event-name">Event Name</flux:label>
                    <flux:input id="edit-event-name" name="event_name" required class="mt-1" />
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <flux:label for="edit-event-date">Event Date</flux:label>
                        <flux:input id="edit-event-date" name="event_date" type="date" required class="mt-1" />
                    </div>
                    <div>
                        <flux:label for="edit-location">Location</flux:label>
                        <flux:input id="edit-location" name="location" required class="mt-1" />
                    </div>
                </div>

                <div>
                    <flux:label>Status</flux:label>
                    <div class="mt-2 flex flex-wrap gap-4">
                        <label class="inline-flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-200">
                            <input type="radio" name="status" value="off-air" class="rounded border-zinc-300 text-blue-600">
                            <span>Off-Air</span>
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-200">
                            <input type="radio" name="status" value="on-air" class="rounded border-zinc-300 text-blue-600">
                            <span>On-Air</span>
                        </label>
                    </div>
                </div>

                <div>
                    <flux:label for="edit-purchase-link">Purchase Link</flux:label>
                    <flux:input id="edit-purchase-link" name="purchase_link" type="url" class="mt-1" placeholder="https://" />
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">Update</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <script>
        function openEditConcertModal(target) {
            const eventData = {
                id: target.dataset.id,
                event_name: target.dataset.eventName,
                event_date: target.dataset.eventDate,
                location: target.dataset.location,
                status: target.dataset.status,
                purchase_link: target.dataset.purchaseLink,
            };

            document.getElementById('edit-concert-event-form').action = `{{ url('concert-events') }}/${eventData.id}`;
            document.getElementById('edit-event-name').value = eventData.event_name || '';
            document.getElementById('edit-event-date').value = eventData.event_date || '';
            document.getElementById('edit-location').value = eventData.location || '';
            document.getElementById('edit-purchase-link').value = eventData.purchase_link || '';

            document.querySelectorAll('#edit-concert-event-form input[name="status"]').forEach((radio) => {
                radio.checked = radio.value === eventData.status;
            });

            Flux.modal('modal-edit-concert-event').show();
        }
    </script>
</x-layouts::app>

@once
    <div id="live-stream-detail-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-4">
        <div class="max-h-[90vh] w-full max-w-6xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl dark:bg-zinc-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Detail Live Streaming</h3>
                    <p id="lsd-subtitle" class="text-sm text-zinc-500 dark:text-zinc-400">–</p>
                </div>
                <button type="button" data-lsd-close class="rounded-lg p-1.5 text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200" aria-label="{{ __('Tutup') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-3">
                {{-- Kolom kiri: info sesi --}}
                <div class="space-y-3 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">Informasi Sesi</p>

                    <dl class="space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-zinc-500 dark:text-zinc-400">Platform</dt>
                            <dd id="lsd-platform" class="font-semibold text-zinc-800 dark:text-zinc-200">–</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-zinc-500 dark:text-zinc-400">Start Time</dt>
                            <dd id="lsd-start" class="font-semibold text-zinc-800 dark:text-zinc-200">–</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-zinc-500 dark:text-zinc-400">End Time</dt>
                            <dd id="lsd-end" class="font-semibold text-zinc-800 dark:text-zinc-200">–</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-zinc-500 dark:text-zinc-400">Duration</dt>
                            <dd id="lsd-duration" class="font-semibold text-zinc-800 dark:text-zinc-200">–</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-zinc-500 dark:text-zinc-400">Jumlah Penonton</dt>
                            <dd id="lsd-viewers" class="font-semibold text-zinc-800 dark:text-zinc-200">–</dd>
                        </div>
                    </dl>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-lg bg-amber-50 p-3 text-center dark:bg-amber-950/30">
                            <p id="lsd-gift-count" class="text-2xl font-black text-amber-600 dark:text-amber-400">0</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Gift</p>
                        </div>
                        <div class="rounded-lg bg-emerald-50 p-3 text-center dark:bg-emerald-950/30">
                            <p id="lsd-total-gold" class="text-2xl font-black text-emerald-600 dark:text-emerald-400">0</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Total Gold</p>
                        </div>
                        <div class="col-span-2 rounded-lg bg-sky-50 p-3 text-center dark:bg-sky-950/30">
                            <p id="lsd-comment-count" class="text-2xl font-black text-sky-600 dark:text-sky-400">0</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Komentar</p>
                        </div>
                    </div>
                </div>

                {{-- Kolom tengah: gift list --}}
                <div class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <div>
                        <p class="mb-2 text-sm font-semibold text-zinc-700 dark:text-zinc-200">Gift List</p>
                        <div id="lsd-gifts" class="grid max-h-72 grid-cols-2 gap-2 overflow-y-auto pr-1"></div>
                    </div>
                </div>

                {{-- Kolom kanan: top gifter --}}
                <div class="space-y-3 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">Top Gifter</p>
                    <div class="grid grid-cols-2 gap-2">
                        <div id="lsd-senders-left" class="space-y-2"></div>
                        <div id="lsd-senders-right" class="space-y-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            if (window.__liveStreamDetailModalReady) {
                return;
            }

            window.__liveStreamDetailModalReady = true;

            function el(id) {
                return document.getElementById(id);
            }

            function escapeHtml(value) {
                var div = document.createElement('div');
                div.textContent = value == null ? '' : value;

                return div.innerHTML;
            }

            function formatDuration(minutes) {
                if (minutes === null || minutes === undefined || minutes === '' || isNaN(minutes)) {
                    return '–';
                }

                var total = Number(minutes);
                var h = Math.floor(total / 60);
                var m = total % 60;

                return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
            }

            function getModal() {
                return el('live-stream-detail-modal');
            }

            function close() {
                var modal = getModal();

                if (!modal) {
                    return;
                }

                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = '';
            }

            window.openLiveStreamDetailModal = function (trigger) {
                var modal = getModal();

                if (!modal) {
                    return;
                }

                var data = {};

                try {
                    data = JSON.parse(trigger.dataset.detail || '{}');
                } catch (error) {
                    data = {};
                }

                el('lsd-subtitle').textContent = (data.platform || '–') + ' • ' + (data.live_date || '–');
                el('lsd-platform').textContent = data.platform || '–';
                el('lsd-start').textContent = data.start_time || '–';
                el('lsd-end').textContent = data.end_time || '–';
                el('lsd-duration').textContent = formatDuration(data.duration);
                el('lsd-viewers').textContent = Number(data.max_viewers || 0).toLocaleString('id-ID');
                el('lsd-gift-count').textContent = Number(data.gift_count || 0).toLocaleString('id-ID');
                el('lsd-total-gold').textContent = Number(data.total_gold || 0).toLocaleString('id-ID');
                el('lsd-comment-count').textContent = Number(data.comment_count || 0).toLocaleString('id-ID');

                var gifts = Array.isArray(data.gifts) ? data.gifts : [];
                var giftsEl = el('lsd-gifts');

                giftsEl.innerHTML = gifts.length === 0
                    ? '<p class="col-span-2 py-6 text-center text-xs text-zinc-400">Tidak ada gift.</p>'
                    : gifts.map(function (gift) {
                        var image = gift.image_url
                            ? '<img src="' + escapeHtml(gift.image_url) + '" alt="' + escapeHtml(gift.name) + '" class="size-8 shrink-0 rounded object-contain" loading="lazy">'
                            : '<span class="flex size-8 shrink-0 items-center justify-center rounded bg-zinc-200 text-xs dark:bg-zinc-700">?</span>';

                        return '<div class="flex items-center gap-2 rounded-lg border border-zinc-100 bg-zinc-50 p-2 dark:border-zinc-800 dark:bg-zinc-800/50">'
                            + image
                            + '<div class="min-w-0 flex-1">'
                            + '<p class="truncate text-xs font-semibold text-zinc-800 dark:text-zinc-200">' + escapeHtml(gift.name) + '</p>'
                            + '<p class="text-[11px] text-zinc-500 dark:text-zinc-400">' + Number(gift.gold_per_unit || 0).toLocaleString('id-ID') + ' gold × ' + Number(gift.count || 0).toLocaleString('id-ID') + '</p>'
                            + '<p class="text-[11px] font-semibold text-amber-600 dark:text-amber-400">' + Number(gift.total_gold || 0).toLocaleString('id-ID') + ' gold</p>'
                            + '</div></div>';
                    }).join('');

                var columns = [el('lsd-senders-left'), el('lsd-senders-right')];
                columns.forEach(function (column) { column.innerHTML = ''; });

                var senders = Array.isArray(data.top_senders) ? data.top_senders.slice(0, 10) : [];

                if (senders.length === 0) {
                    columns[0].innerHTML = '<p class="col-span-2 py-6 text-center text-xs text-zinc-400">Tidak ada data.</p>';
                } else {
                    senders.forEach(function (sender, index) {
                        var card = document.createElement('div');
                        card.className = 'flex items-center gap-2 rounded-lg border border-zinc-100 bg-zinc-50 p-2 dark:border-zinc-800 dark:bg-zinc-800/50';
                        card.innerHTML = (sender.avatar
                            ? '<img src="' + escapeHtml(sender.avatar) + '" alt="' + escapeHtml(sender.name) + '" class="size-8 shrink-0 rounded-full object-cover" loading="lazy">'
                            : '<span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-zinc-200 text-xs dark:bg-zinc-700">?</span>')
                            + '<div class="min-w-0 flex-1">'
                            + '<p class="truncate text-xs font-semibold text-zinc-800 dark:text-zinc-200">' + escapeHtml(sender.name) + '</p>'
                            + '<p class="text-[11px] font-semibold text-amber-600 dark:text-amber-400">' + Number(sender.total_gold || 0).toLocaleString('id-ID') + ' gold</p>'
                            + '</div>';
                        columns[index < 5 ? 0 : 1].appendChild(card);
                    });
                }

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            };

            document.addEventListener('click', function (event) {
                if (event.target.closest('[data-lsd-close]')) {
                    close();

                    return;
                }

                var modal = getModal();

                if (modal && event.target === modal) {
                    close();
                }
            });

            document.addEventListener('keydown', function (event) {
                var modal = getModal();

                if (event.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
                    close();
                }
            });
        })();
    </script>
@endonce

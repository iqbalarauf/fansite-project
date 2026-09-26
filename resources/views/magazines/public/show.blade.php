@extends('layouts.public', ['title' => $magazine->title, 'active' => 'magazine'])

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;

        $fileUrl = Storage::url($magazine->file_path);
        $cover = $magazine->cover ? Storage::url($magazine->cover) : null;
    @endphp

    <section class="mx-auto max-w-7xl px-4 pt-12 sm:px-6 lg:px-8">
        <a href="{{ route('magazine.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Semua Majalah
        </a>
        <h1 class="mt-6 max-w-3xl text-3xl font-black leading-tight text-slate-900 dark:text-white sm:text-5xl">{{ $magazine->title }}</h1>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-8">
            <div class="flex flex-col overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                {{-- PDF.js reader: dua halaman (spread) per baris, tanpa toolbar download/print bawaan. --}}
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                    <a href="{{ route('magazine.download', $magazine) }}" class="rounded-full px-3 py-1 font-semibold bg-indigo-600 text-white hover:text-indigo-600 transition hover:bg-indigo-100 dark:hover:bg-slate-700">Download PDF</a>
                    <div class="flex flex-wrap items-center gap-3 text-sm">
                        <div class="inline-flex items-center rounded-full border border-slate-200 p-0.5 dark:border-slate-700">
                            <button type="button" id="pdf-mode-1" class="rounded-full px-3 py-1 font-semibold transition">1 Halaman</button>
                            <button type="button" id="pdf-mode-2" class="rounded-full px-3 py-1 font-semibold transition">2 Halaman</button>
                        </div>
                        <button type="button" id="pdf-prev" class="rounded-full border border-slate-200 px-3 py-1.5 font-semibold text-slate-600 transition hover:border-indigo-300 hover:text-indigo-600 disabled:opacity-40 dark:border-slate-700 dark:text-slate-300 dark:hover:text-indigo-400">&laquo; Sebelumnya</button>
                        <span class="font-semibold text-slate-500 dark:text-slate-400"><span id="pdf-page-label">1</span> / <span id="pdf-page-count">…</span></span>
                        <button type="button" id="pdf-next" class="rounded-full border border-slate-200 px-3 py-1.5 font-semibold text-slate-600 transition hover:border-indigo-300 hover:text-indigo-600 disabled:opacity-40 dark:border-slate-700 dark:text-slate-300 dark:hover:text-indigo-400">Berikutnya &raquo;</button>
                        <button type="button" id="pdf-fullscreen" class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 px-3 py-1.5 font-semibold text-slate-600 transition hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-700 dark:text-slate-300 dark:hover:text-indigo-400">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-4" data-fullscreen-icon>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"/>
                            </svg>
                            <span data-fullscreen-label>Full Screen</span>
                        </button>
                    </div>
                </div>

                <div id="pdf-spread" data-pdf-url="{{ $fileUrl }}" class="flex flex-nowrap items-start justify-center gap-3 overflow-x-auto bg-slate-100 p-4 dark:bg-slate-800">
                    <p id="pdf-loading" class="py-16 text-sm text-slate-500 dark:text-slate-400">Memuat PDF…</p>
                </div>
            </div>
        </div>
    </section>

    <script type="module">
        import * as pdfjsLib from 'https://cdn.jsdelivr.net/npm/pdfjs-dist@4.6.82/build/pdf.min.mjs';

        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@4.6.82/build/pdf.worker.min.mjs';

        (function () {
            var container = document.getElementById('pdf-spread');

            if (!container) {
                return;
            }

            var url = container.dataset.pdfUrl;
            var loading = document.getElementById('pdf-loading');
            var prevBtn = document.getElementById('pdf-prev');
            var nextBtn = document.getElementById('pdf-next');
            var pageLabel = document.getElementById('pdf-page-label');
            var pageCountLabel = document.getElementById('pdf-page-count');
            var mode1Btn = document.getElementById('pdf-mode-1');
            var mode2Btn = document.getElementById('pdf-mode-2');
            var fullscreenBtn = document.getElementById('pdf-fullscreen');
            var fullscreenLabel = fullscreenBtn.querySelector('[data-fullscreen-label]');
            var fullscreenIcon = fullscreenBtn.querySelector('[data-fullscreen-icon]');
            // Elemen yang dibuat fullscreen (pembungkus panel PDF).
            var panel = container.closest('div.overflow-hidden') || container;

            var doc = null;
            // Halaman yang sedang tampil (1-based).
            var current = 1;
            // 1 = satu halaman, 2 = dua halaman bersebelahan.
            var perSpread = 2;

            // Halaman pertama dari satu "spread".
            // - Mode 2: halaman 1 selalu sendiri, halaman terakhir selalu sendiri,
            //   sehingga spread berikutnya mulai dari genap (2, 4, 6, ...).
            // - Mode 1: tiap halaman berdiri sendiri.
            function spreadStart(page) {
                if (perSpread === 1) {
                    return page;
                }

                if (page <= 1) {
                    return 1;
                }

                return page % 2 === 0 ? page : page - 1;
            }

            // Berapa halaman yang tampil mulai dari spreadStart.
            function spreadCount(start) {
                if (perSpread === 1) {
                    return 1;
                }

                if (start === 1) {
                    return doc.numPages >= 2 ? 1 : doc.numPages;
                }

                // Jangan gabungkan halaman genap terakhir dengan halaman berikutnya
                // bila halaman berikutnya adalah halaman terakhir (harus sendiri).
                var last = start + 1;

                return last <= doc.numPages ? 2 : 1;
            }

            function spreadEnd(start) {
                return start + spreadCount(start) - 1;
            }

            function renderSpread() {
                if (!doc) {
                    return;
                }

                container.querySelectorAll('[data-pdf-page]').forEach(function (node) { node.remove(); });

                var start = spreadStart(current);
                var end = spreadEnd(start);

                pageLabel.textContent = start + (end > start ? '–' + end : '');
                pageCountLabel.textContent = doc.numPages;
                prevBtn.disabled = start <= 1;
                nextBtn.disabled = end >= doc.numPages;

                for (var pageNumber = start; pageNumber <= end; pageNumber++) {
                    renderPage(pageNumber);
                }
            }

            // Lebar area konten yang tersedia untuk kanvas (dikurangi padding & gap
            // agar 2 halaman benar-benar muat berdampingan kiri-kanan).
            function availableWidth() {
                var styles = window.getComputedStyle(container);
                var padding = parseFloat(styles.paddingLeft) + parseFloat(styles.paddingRight);
                var gap = parseFloat(styles.columnGap || styles.gap) || 0;

                return container.clientWidth - padding - gap;
            }

            function renderPage(pageNumber) {
                doc.getPage(pageNumber).then(function (page) {
                    var baseViewport = page.getViewport({ scale: 1 });
                    var perRow = Math.min(perSpread, doc.numPages);
                    var maxWidth = Math.min(availableWidth() / perRow, 820);
                    var scale = maxWidth / baseViewport.width;
                    var viewport = page.getViewport({ scale: scale });

                    var canvas = document.createElement('canvas');
                    canvas.dataset.pdfPage = String(pageNumber);
                    canvas.className = 'min-w-0 shrink-0 rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700';
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;

                    container.appendChild(canvas);

                    page.render({ canvasContext: canvas.getContext('2d'), viewport: viewport });
                });
            }

            function goTo(page) {
                if (!doc) {
                    return;
                }

                current = Math.min(Math.max(1, page), doc.numPages);
                renderSpread();
            }

            function setMode(mode) {
                perSpread = mode;
                syncModeButtons();
                goTo(current);
            }

            function syncModeButtons() {
                [mode1Btn, mode2Btn].forEach(function (button) {
                    var isActive = (button === mode1Btn && perSpread === 1) || (button === mode2Btn && perSpread === 2);

                    button.classList.toggle('bg-indigo-600', isActive);
                    button.classList.toggle('text-white', isActive);
                    button.classList.toggle('text-slate-600', !isActive);
                    button.classList.toggle('dark:text-slate-300', !isActive);
                });
            }

            prevBtn.addEventListener('click', function () {
                if (doc) {
                    goTo(spreadStart(current) - 1);
                }
            });

            nextBtn.addEventListener('click', function () {
                if (doc) {
                    goTo(spreadEnd(spreadStart(current)) + 1);
                }
            });

            mode1Btn.addEventListener('click', function () { setMode(1); });
            mode2Btn.addEventListener('click', function () { setMode(2); });

            function isFullscreen() {
                return document.fullscreenElement === panel;
            }

            function syncFullscreenButton() {
                var active = isFullscreen();

                fullscreenLabel.textContent = active ? 'Keluar Full Screen' : 'Full Screen';
                fullscreenIcon.innerHTML = active
                    ? '<path stroke-linecap="round" stroke-linejoin="round" d="M9 9V4.5M9 9H4.5M9 9 3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5 5.25 5.25"/>'
                    : '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"/>';
            }

            function toggleFullscreen() {
                if (! document.fullscreenEnabled) {
                    // Fallback: mode layar penuh via CSS.
                    panel.classList.toggle('fixed');
                    panel.classList.toggle('inset-0');
                    panel.classList.toggle('z-50');

                    setTimeout(function () { renderSpread(); }, 50);

                    return;
                }

                if (isFullscreen()) {
                    document.exitFullscreen();
                } else {
                    panel.requestFullscreen().catch(function () {
                        panel.classList.add('fixed', 'inset-0', 'z-50');
                    });
                }
            }

            fullscreenBtn.addEventListener('click', toggleFullscreen);

            document.addEventListener('fullscreenchange', function () {
                syncFullscreenButton();
                // Beri waktu browser menyesuaikan ukuran sebelum render ulang.
                setTimeout(function () { if (doc) { renderSpread(); } }, 100);
            });

            syncFullscreenButton();

            pdfjsLib.getDocument(url).promise.then(function (pdf) {
                doc = pdf;

                if (loading) {
                    loading.remove();
                }

                syncModeButtons();
                renderSpread();
            }).catch(function () {
                if (loading) {
                    loading.textContent = 'Gagal memuat PDF. Silakan unduh berkas untuk membacanya.';
                }
            });

            var resizeTimer = null;
            window.addEventListener('resize', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function () {
                    if (doc) {
                        renderSpread();
                    }
                }, 250);
            });
        })();
    </script>
@endsection

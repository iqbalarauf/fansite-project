@extends('layouts.photobooth', ['title' => 'Photobooth'])

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;
    @endphp

    @if ($schedule['state'] !== 'open')
        <div class="flex min-h-dvh items-center justify-center px-4 py-16">
            <div class="w-full max-w-md rounded-3xl border border-slate-800 bg-slate-900 p-8 text-center shadow-xl sm:p-12">
                @if ($schedule['state'] === 'scheduled')
                    <p class="text-sm font-bold uppercase tracking-[0.22em] text-indigo-400">Segera Dibuka</p>
                    <h2 class="mt-3 text-2xl font-black text-white">Photobooth akan dibuka</h2>
                    @if ($schedule['opens_at'])
                        <p class="mt-2 text-sm text-slate-400">{{ $schedule['opens_at']->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</p>
                    @endif
                    <div id="photobooth-countdown" class="mt-6 text-4xl font-black text-indigo-400">—</div>
                    <div data-countdown-target="{{ $schedule['opens_at']?->toIso8601String() }}" class="hidden"></div>
                @elseif ($schedule['state'] === 'closed')
                    <p class="text-sm font-bold uppercase tracking-[0.22em] text-red-400">Mohon Maaf</p>
                    <h2 class="mt-3 text-2xl font-black text-white">Photobooth sudah ditutup, sampai bertemu di event berikutnya!</h2>
                @else
                    <p class="text-sm font-bold uppercase tracking-[0.22em] text-slate-400">Mohon Maaf</p>
                    <h2 class="mt-3 text-2xl font-black text-white">Photobooth saat ini tidak aktif, sampai bertemu di event berikutnya!</h2>
                @endif
            </div>
        </div>

        @if ($schedule['state'] === 'scheduled' && $schedule['opens_at'])
            <script>
                (function () {
                    var target = document.querySelector('[data-countdown-target]');
                    var output = document.getElementById('photobooth-countdown');

                    if (!target || !output) {
                        return;
                    }

                    var targetTime = new Date(target.dataset.countdownTarget).getTime();

                    function tick() {
                        var diff = targetTime - Date.now();

                        if (diff <= 0) {
                            output.textContent = 'Membuka...';
                            location.reload();

                            return;
                        }

                        var days = Math.floor(diff / 86400000);
                        var hours = Math.floor((diff % 86400000) / 3600000);
                        var minutes = Math.floor((diff % 3600000) / 60000);
                        var seconds = Math.floor((diff % 60000) / 1000);

                        output.textContent = days + 'h ' + hours + 'j ' + minutes + 'm ' + seconds + 's';
                    }

                    setInterval(tick, 1000);
                    tick();
                })();
            </script>
        @endif
    @else
        <div id="photobooth-app"
             data-frame="{{ Storage::url($photobooth->frame) }}"
             data-columns="{{ $photobooth->columns }}"
             data-rows="{{ $photobooth->rows }}"
             data-count="{{ $photobooth->poseCount() }}"
             data-slots="{{ json_encode($photobooth->resolvedSlots()) }}"
             data-overlay="{{ $photobooth->frame_overlay ? '1' : '0' }}"
             class="relative min-h-dvh w-full">
            <div id="pb-camera-stage" class="mx-auto flex min-h-dvh w-full max-w-xl flex-col items-center justify-center gap-6 px-4 py-10">
                <div class="relative w-full overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-2xl">
                    <video id="pb-video" autoplay playsinline muted class="h-full w-full object-cover"></video>
                    <div id="pb-flash" class="pointer-events-none absolute inset-0 bg-white opacity-0 transition-opacity duration-200"></div>
                    <div id="pb-countdown" class="pointer-events-none absolute inset-0 flex items-center justify-center text-7xl font-black text-white drop-shadow-lg"></div>
                    <div id="pb-pose" class="pointer-events-none absolute right-4 top-4 hidden rounded-full bg-slate-900/70 px-3 py-1 text-sm font-bold text-white"></div>
                </div>

                <p id="pb-status" class="text-center text-sm text-slate-400">Menyiapkan kamera...</p>

                <button type="button" id="pb-start" disabled class="inline-flex items-center justify-center gap-2 rounded-full bg-indigo-600 px-8 py-4 text-base font-bold text-white shadow-lg shadow-indigo-600/30 transition hover:bg-indigo-500 disabled:opacity-50">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" aria-hidden="true">
                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                        <circle cx="12" cy="13" r="4"></circle>
                    </svg>
                    Take a Photo
                </button>
            </div>

            <div id="pb-result-stage" style="display: none" class="mx-auto flex min-h-dvh w-full max-w-xl flex-col items-center justify-center gap-6 px-4 py-10">
                <canvas id="pb-canvas" style="aspect-ratio: {{ $photobooth->columns }} / {{ $photobooth->rows }}" class="w-full rounded-3xl border border-slate-800 bg-white shadow-2xl"></canvas>

                <div class="flex w-full flex-col gap-3 sm:flex-row">
                    <button type="button" id="pb-download" class="inline-flex flex-1 items-center justify-center gap-2 rounded-full bg-green-600 px-6 py-4 text-base font-bold text-white shadow-lg shadow-green-600/30 transition hover:bg-green-500">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" aria-hidden="true">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        Unduh
                    </button>
                    <button type="button" id="pb-retake" class="inline-flex flex-1 items-center justify-center gap-2 rounded-full border border-slate-700 bg-slate-900 px-6 py-4 text-base font-bold text-white transition hover:border-indigo-400 hover:text-indigo-300">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" aria-hidden="true">
                            <polyline points="1 4 1 10 7 10"></polyline>
                            <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                        </svg>
                        Ulangi
                    </button>
                </div>
            </div>

            <div id="pb-welcome" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4">
                <div class="w-full max-w-sm rounded-2xl border border-slate-800 bg-slate-900 p-6 text-center shadow-xl">
                    <h2 class="text-lg font-black text-white">Informasi Photobooth</h2>
                    <p class="mt-4 text-sm text-slate-300">
                        Photobooth ini akan mengambil {{ $photobooth->poseCount() }} foto. Hasil foto bisa diunduh setelah sesi selesai.
                    </p>
                    <p class="mt-3 text-sm text-slate-300">
                        Foto <b>tidak akan disimpan di server</b>. Semua proses hanya terjadi di perangkat kamu.
                    </p>
                    <button type="button" id="pb-welcome-ok" class="mt-6 w-full rounded-full bg-indigo-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-indigo-500">OK</button>
                </div>
            </div>
        </div>

        <script>
            (function () {
                var root = document.getElementById('photobooth-app');

                if (!root) {
                    return;
                }

                var columns = parseInt(root.dataset.columns, 10) || 2;
                var rows = parseInt(root.dataset.rows, 10) || 3;
                var total = parseInt(root.dataset.count, 10) || (columns * rows);
                var frameSrc = root.dataset.frame;
                var cell = 720;
                var frameOverlay = root.dataset.overlay === '1';
                var slots = [];

                try {
                    slots = JSON.parse(root.dataset.slots || '[]') || [];
                } catch (error) {
                    slots = [];
                }

                var video = document.getElementById('pb-video');
                var flash = document.getElementById('pb-flash');
                var canvas = document.getElementById('pb-canvas');
                var context = canvas.getContext('2d');
                var startButton = document.getElementById('pb-start');
                var retakeButton = document.getElementById('pb-retake');
                var downloadButton = document.getElementById('pb-download');
                var countdown = document.getElementById('pb-countdown');
                var poseBadge = document.getElementById('pb-pose');
                var status = document.getElementById('pb-status');
                var cameraStage = document.getElementById('pb-camera-stage');
                var resultStage = document.getElementById('pb-result-stage');
                var welcome = document.getElementById('pb-welcome');
                var welcomeOk = document.getElementById('pb-welcome-ok');

                canvas.width = columns * cell;
                canvas.height = rows * cell;

                var shots = [];
                var frameImage = new Image();
                var frameReady = false;
                var mediaStream = null;
                var cameraActive = false;

                frameImage.onload = function () {
                    frameReady = true;
                };
                frameImage.src = frameSrc;

                function showCameraStage() {
                    cameraStage.style.display = '';
                    resultStage.style.display = 'none';
                }

                function showResultStage() {
                    cameraStage.style.display = 'none';
                    resultStage.style.display = '';
                }

                function diagnostics() {
                    var ua = navigator.userAgent || '';

                    return {
                        ua: ua,
                        isSafari: /safari/i.test(ua) && !/chrome|chromium|android/i.test(ua),
                        hasMediaDevices: !!navigator.mediaDevices,
                        hasGetUserMedia: !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia),
                        isSecureContext: window.isSecureContext,
                    };
                }

                function cameraErrorMessage(error, info) {
                    var name = (error && error.name) || 'UnknownError';

                    if (!info.isSecureContext) {
                        return 'Kamera hanya bisa diakses lewat HTTPS atau localhost.';
                    }

                    if (!info.hasGetUserMedia) {
                        return 'Peramban ini belum mendukung akses kamera.';
                    }

                    if (name === 'NotAllowedError' || name === 'SecurityError') {
                        return 'Akses kamera ditolak. Izinkan kamera untuk situs ini lalu muat ulang halaman.';
                    }

                    if (name === 'NotReadableError' || name === 'TrackStartError') {
                        return 'Kamera sedang dipakai aplikasi lain. Tutup aplikasi lain lalu coba lagi.';
                    }

                    if (name === 'OverconstrainedError' || name === 'ConstraintNotSatisfiedError') {
                        return 'Konfigurasi kamera tidak didukung perangkat ini.';
                    }

                    if (name === 'NotFoundError' || name === 'DevicesNotFoundError') {
                        return 'Tidak ada kamera yang terdeteksi pada perangkat ini.';
                    }

                    return 'Tidak dapat mengakses kamera. Pastikan izin kamera aktif dan kamera tidak dipakai aplikasi lain.';
                }

                function requestStream(info) {
                    var fallback = [
                        { video: { facingMode: { ideal: 'user' } }, audio: false },
                        { video: true, audio: false },
                    ];
                    var preferred = {
                        video: { width: { ideal: 1080 }, height: { ideal: 1080 }, facingMode: 'user' },
                        audio: false,
                    };
                    var candidates = info.isSafari ? fallback.concat([preferred]) : [preferred].concat(fallback);

                    function attempt(index) {
                        if (index >= candidates.length) {
                            return Promise.reject(new Error('Semua percobaan akses kamera gagal.'));
                        }

                        return navigator.mediaDevices.getUserMedia(candidates[index]).catch(function () {
                            return attempt(index + 1);
                        });
                    }

                    return attempt(0);
                }

                function wait(milliseconds) {
                    return new Promise(function (resolve) { setTimeout(resolve, milliseconds); });
                }

                function waitForMetadata() {
                    return new Promise(function (resolve) {
                        if (video.readyState >= 1) {
                            resolve();
                            return;
                        }

                        var done = false;
                        function finish() {
                            if (done) {
                                return;
                            }

                            done = true;
                            video.onloadedmetadata = null;
                            resolve();
                        }

                        video.onloadedmetadata = finish;
                        setTimeout(finish, 3000);
                    });
                }

                function startCamera() {
                    var info = diagnostics();

                    if (!info.hasGetUserMedia) {
                        status.textContent = cameraErrorMessage(null, info);
                        startButton.disabled = true;

                        return;
                    }

                    if (mediaStream && mediaStream.getTracks().some(function (track) { return track.readyState === 'live'; })) {
                        video.srcObject = mediaStream;
                        cameraActive = true;
                        startButton.disabled = false;
                        status.textContent = 'Kamera siap. Tekan Take a Photo.';

                        return;
                    }

                    requestStream(info).then(function (stream) {
                        mediaStream = stream;
                        video.srcObject = stream;

                        return video.play().catch(function () {}).then(waitForMetadata);
                    }).then(function () {
                        cameraActive = true;
                        startButton.disabled = false;
                        status.textContent = 'Kamera siap. Tekan Take a Photo.';
                    }).catch(function (error) {
                        cameraActive = false;
                        startButton.disabled = true;
                        status.textContent = cameraErrorMessage(error, info);
                    });
                }

                function stopCamera() {
                    if (mediaStream) {
                        mediaStream.getTracks().forEach(function (track) { track.stop(); });
                        mediaStream = null;
                    }

                    video.srcObject = null;
                    cameraActive = false;
                }

                function triggerFlash() {
                    flash.style.opacity = '0.85';
                    setTimeout(function () { flash.style.opacity = '0'; }, 180);
                }

                function drawCover(image, x, y, width, height) {
                    var scale = Math.max(width / image.width, height / image.height);
                    var drawWidth = image.width * scale;
                    var drawHeight = image.height * scale;

                    context.drawImage(image, x + (width - drawWidth) / 2, y + (height - drawHeight) / 2, drawWidth, drawHeight);
                }

                function placeShot(image, index) {
                    var slot = slots[index];

                    if (slot) {
                        drawCover(
                            image,
                            canvas.width * (parseFloat(slot.x) / 100),
                            canvas.height * (parseFloat(slot.y) / 100),
                            canvas.width * (parseFloat(slot.width) / 100),
                            canvas.height * (parseFloat(slot.height) / 100)
                        );

                        return;
                    }

                    var column = index % columns;
                    var row = Math.floor(index / columns);
                    drawCover(image, column * cell, row * cell, cell, cell);
                }

                function compose() {
                    context.clearRect(0, 0, canvas.width, canvas.height);

                    if (frameOverlay) {
                        shots.forEach(placeShot);

                        if (frameReady) {
                            context.drawImage(frameImage, 0, 0, canvas.width, canvas.height);
                        }

                        return;
                    }

                    context.fillStyle = '#ffffff';
                    context.fillRect(0, 0, canvas.width, canvas.height);

                    if (frameReady) {
                        context.drawImage(frameImage, 0, 0, canvas.width, canvas.height);
                    }

                    shots.forEach(placeShot);
                }

                function captureSquare() {
                    var sourceWidth = video.videoWidth || cell;
                    var sourceHeight = video.videoHeight || cell;
                    var size = Math.min(sourceWidth, sourceHeight);
                    var snapshot = document.createElement('canvas');

                    snapshot.width = size;
                    snapshot.height = size;
                    snapshot.getContext('2d').drawImage(
                        video,
                        (sourceWidth - size) / 2,
                        (sourceHeight - size) / 2,
                        size,
                        size,
                        0,
                        0,
                        size,
                        size
                    );

                    return snapshot.toDataURL('image/png');
                }

                async function capture() {
                    if (!cameraActive) {
                        status.textContent = 'Kamera belum siap. Izinkan akses kamera lalu muat ulang halaman.';

                        return;
                    }

                    startButton.disabled = true;
                    shots = [];

                    for (var index = 0; index < total; index++) {
                        poseBadge.textContent = 'Pose ' + (index + 1) + ' dari ' + total;
                        poseBadge.classList.remove('hidden');
                        status.textContent = 'Bersiap... foto ke-' + (index + 1);

                        for (var tick = 3; tick >= 1; tick--) {
                            countdown.textContent = tick;
                            await wait(1000);
                        }

                        countdown.textContent = '';

                        if (video.readyState < 2) {
                            await wait(150);
                        }

                        triggerFlash();

                        var image = new Image();
                        image.src = captureSquare();
                        await new Promise(function (resolve) { image.onload = resolve; });

                        shots.push(image);
                        await wait(300);
                    }

                    poseBadge.classList.add('hidden');
                    compose();
                    stopCamera();
                    showResultStage();
                }

                function retake() {
                    shots = [];
                    showCameraStage();
                    status.textContent = 'Menyiapkan kamera...';
                    startButton.disabled = true;
                    startCamera();
                }

                function download() {
                    if (!shots.length) {
                        return;
                    }

                    canvas.toBlob(function (blob) {
                        if (!blob) {
                            return;
                        }

                        var filename = 'photobooth-' + Date.now() + '.png';

                        if (typeof navigator.msSaveOrOpenBlob === 'function') {
                            navigator.msSaveOrOpenBlob(blob, filename);

                            return;
                        }

                        var objectUrl = URL.createObjectURL(blob);
                        var link = document.createElement('a');
                        link.href = objectUrl;
                        link.download = filename;
                        link.click();
                        setTimeout(function () { URL.revokeObjectURL(objectUrl); }, 1000);
                    }, 'image/png');
                }

                startButton.addEventListener('click', capture);
                retakeButton.addEventListener('click', retake);
                downloadButton.addEventListener('click', download);

                welcomeOk.addEventListener('click', function () {
                    welcome.classList.add('hidden');
                    startCamera();
                });

                window.addEventListener('pagehide', stopCamera);
                window.addEventListener('beforeunload', stopCamera);
            })();
        </script>
    @endif
@endsection

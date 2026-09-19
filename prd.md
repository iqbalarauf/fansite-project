# PRD — FANSIGHT (Fansite Platform)

**Dokumen:** Product Requirements Document
**Versi:** 1.2
**Tanggal:** 19 September 2026
**Author:** Analisis Otomatis dari Codebase

---

## 1. Ringkasan Produk

**FANSIGHT** adalah aplikasi web fansite/idol data platform yang dirancang untuk mengumpulkan, mengelola, dan membagikan informasi serta konten penggemar (fanbase) yang didedikasikan untuk seorang idola — dalam implementasi saat ini difokuskan pada **JKT48**.

Aplikasi dibangun berbasis web dengan arsitektur terpusat, sehingga seluruh data dan rekap dapat dikelola secara **real-time dan terintegrasi**, dan dapat diakses dari banyak perangkat (PC maupun mobile).

### Tujuan Utama
1. Mengelola data statistik penampilan idola (pertunjukan teater, konser, meet & greet, live streaming).
2. Mengelola informasi fanbase dan data profil idola.
3. Mengelola konten publik: halaman custom (page builder), **Majalah digital**, **News & Blog**, **Timeline**, **Trivia**, dan **Galeri**.
4. Membangun situs publik (welcome, profil idola/fanbase, artikel, majalah, jadwal, galeri, timeline, trivia, photobooth) untuk membagikan data/konten kepada publik.
5. Mempermudah pengelolaan data (multi-user dengan manajemen peran), memberi **kontrol aktivasi fitur** konten, dan **kustomisasi branding** (warna, gambar, tombol hero, embed YouTube).

### Informasi Umum
- **Situs produksi:** `https://fansight.labqitech.my.id`
- **Pengembang:** IqbalARauf (LabqiTech)
- **Konteks:** Eksperimen/proyek portofolio, sebagian besar dikembangkan dengan bantuan AI (vibe coding).
- **Lisensi:** Terbuka untuk publik, tanpa tujuan komersialisasi.

---

## 2. Target Pengguna & Persona

| Persona | Deskripsi | Peran dalam Sistem |
|---|---|---|
| **Super Admin** | Pemilik/pengelola utama sistem | Akses penuh: dashboard, master data, seluruh Content Management, pengaturan (About, Appearance, Features Activation, Header Menu, Photobooth), manajemen pengguna |
| **Bank Data Admin** | Anggota fanbase pengelola data statistik | Dashboard + master data (teater, konser, meet & greet, live streaming) |
| **Content Creator** | Anggota fanbase pengelola konten | Content Management: Pages, Majalah, News, Blog, Kategori, Galeri, Timeline, Trivia |
| **View Only** | Pengamat/kontributor pasif | Akses baca-saja ke dashboard, master data, dan content management (tanpa kemampuan menulis) |
| **Publik** | Pengunjung umum | Membaca halaman publik (welcome, about, news, blog, majalah, custom pages, jadwal, galeri, timeline, trivia, photobooth) |

---

## 3. Arsitektur Teknologi

### Stack Utama
- **Backend:** PHP ^8.3, Laravel Framework ^13
- **Frontend:** Blade, Livewire ^4 (Volt single-file components), Flux UI ^2, Tailwind CSS ^4, Vite
- **Rich Text Editor:** **Tiptap v3** untuk konten News & Blog
- **Autentikasi:** Laravel Fortify (login, 2FA, reset password, verifikasi email). **Registrasi publik dinonaktifkan** — pengguna baru hanya dibuat oleh Super Admin.
- **Database:** SQLite (default) / MySQL (produksi)
- **Ekosistem:** Laravel Boost, Pint, Larastan/PHPStan, PHPUnit ^12, Sail, Pail, MCP
- **Bahasa/Lokalisasi:** Indonesia (`id`)

### Pola Arsitektur
- **MVC Laravel** untuk CRUD domain data (controllers + Form Requests + blade views).
- **Livewire Volt** single-file components untuk fitur interaktif (page builder, pengaturan, form).
- **Enums + role-based authorization** (`UserRole`, `ContentSection`, `ConcertStatus`, `MeetGreetEventType`, `LiveStreamingPlatform`) + middleware kustom.
- **Feature flags** berbasis setting (`SettingBag::featureEnabled()`) untuk mengaktifkan/menonaktifkan News, Blog, Majalah, Trivia, dan Photobooth.
- **Support/Service classes** (`app/Support`): `DashboardAssembler`, `WelcomePageData`, `AboutPageData`, `EventTimeline`, `SettingBag`, `SettingsStore`, `BrandPalette`, `HeroLink`, `YoutubePlaylist`, `YoutubeRss`, `YoutubePlaylistPage`, `ListingQuery`, `ShowDate`, `TextLines`, `HeaderMenu`, `CheckMemberLive`, `IdolTheaterStats`, `MonthlySchedule`, `PhotoboothSchedule`, `CustomPageStatistic`, `GalleryVideoEmbed`.
- **Soft deletes** pada `show_teater`, `meet_greet_events`, `concert_events`, `custom_pages`, dan `news_posts`/`blog_posts`.
- **Pipeline fetch data** dari API eksternal (JKT48 public API) via Artisan command.

---

## 4. Definisi Peran & Otorisasi

Enums: `App\Enums\UserRole`

| Role | Dashboard & Master Data | Content Management | Manajemen User & Settings |
|---|---|---|---|
| `super_admin` | ✅ | ✅ | ✅ |
| `bank_data_admin` | ✅ | ❌ | ❌ |
| `content_creator` | ❌ | ✅ | ❌ |
| `view_only` | ✅ (baca) | ✅ (baca) | ❌ |

> Content Management mencakup: Pages, Majalah, News, Blog, Kategori, Galeri, Timeline, dan Trivia.

**Middleware kustom:**
- `EnsureUserHasRole` (alias `role`) — membatasi akses route per peran.
- `PreventViewOnlyWrites` (alias `block-view-only-writes`) — mencegah peran view-only melakukan operasi tulis.
- `EnsureNotViewOnly` (alias `block-view-only`) — memblokir akses view-only ke halaman tertentu.
- `EnsureFeatureEnabled` (alias `feature`) — menolak request (404) bila fitur konten dinonaktifkan.
- Middleware `verified` Laravel kini aktif (User mengimplementasikan `MustVerifyEmail`).

**Aturan khusus:**
- Pengguna tidak dapat mengubah role dirinya sendiri.
- Pengguna tidak dapat menghapus dirinya sendiri.
- `UserRole::masterDataRoles()` = SuperAdmin, ViewOnly, BankDataAdmin.
- `UserRole::pagesRoles()` = SuperAdmin, ViewOnly, ContentCreator.
- Fitur konten yang dinonaktifkan menyembunyikan menu sekaligus menonaktifkan route terkait.

---

## 5. Fitur-Fitur Utama

### 5.1 Autentikasi & Manajemen Akun (Fortify)
- **Login & logout** (custom `LoginResponse` mengarahkan ContentCreator ke halaman `/pages`).
- **Registrasi publik dihapus** — pengguna baru hanya ditambahkan Super Admin melalui halaman **User Management**; sistem otomatis **mengirim email verifikasi** ke pengguna baru.
- Verifikasi email, reset & konfirmasi password.
- **Two-Factor Authentication (2FA)** dengan recovery codes — diaktifkan di halaman **Profile** dan **Security** (komponen bersama `pages::settings.two-factor`).
- Halaman pengaturan akun: profil (nama, email), keamanan (password, 2FA), appearance, hapus akun.
- **Halaman login split-screen** dengan panel gambar (`login_image`).

### 5.2 Dashboard (Analitik & Statistik)
- Filter periode: semua / 7 hari / bulanan / kuartal / 6 bulan / tahunan / **kustom** + **mode perbandingan**.
- **Card statistik Show Teater**: jumlah show, setlist, unit song, center unit song, global center.
- **Card statistik Event Concert**: Off-Air, On-Air, Brand, Media, OFC/JKT48 Concert (gabungan), mengikuti periode & perbandingan.
- Aggregasi chart aktivitas per tahun/bulan/minggu/hari (Show Teater, Event, Meet & Greet, Live Streaming).
- Hitung mundur ulang tahun + reminder; **Milestone Show** (kelipatan 100).
- Panel Live Streaming dan timeline event masa lalu & mendatang (gabungan semua tipe data).
- Fitur **Capture** dashboard ke PNG (html2canvas + Chart.js); statistik di-cache.

### 5.3 Master Data — Show Teater
- CRUD pertunjukan teater (PK `show_id` integer non-increment).
- **Soft delete** (`deleted_at`) — `show_id` dipertahankan; store dengan `show_id` ter-soft delete memulihkan baris.
- Join dengan kategori setlist & unit song; konversi tanggal `YYYY-MM-DD` → `YYYY/MM/DD`.
- **Konfirmasi / reject** pertunjukan member; **fetch manual** dari API JKT48 (`app:fetch-theater-shows`).
- Atribut: `is_global_center`, `is_us_center`, `is_the_show_has_event`, `is_scraped_data`, `is_member_show`, `last_fetch_at`.

### 5.4 Master Data — Kategori Setlist & Unit Song
- CRUD kategori setlist & unit song (tabbed), **toggle status aktif**, relasi self-FK `setlist_id`.

### 5.5 Master Data — Meet & Greet Events
- CRUD; tipe **meet-greet** / **video-call** (`event_date_2`); field nama, tipe, tanggal, jadwal penjualan, link pembelian, lokasi; soft deletes.

### 5.6 Master Data — Concert Events
- CRUD; status enum `ConcertStatus` (`off-air`, `on-air`, `jkt48-event`, `media`, `ofc-event`, `brand`); soft deletes.

### 5.7 Master Data — Live Streaming
- CRUD; platform enum `LiveStreamingPlatform` (IDN App / Showroom); field platform, tanggal, durasi, info tambahan.

### 5.8 Content Management — Page Builder (Custom Pages)
- Editor Livewire Volt untuk membangun halaman publik; blok: container, text, statistic, image, video (YouTube), button, embed (raw HTML).
- Blok image mendukung URL eksternal **atau upload file**; mode `full`/`welcome`; background preset + hex; drag-and-drop nested sorting; validasi rekursif.
- Render publik di `/{customPage:slug}` (404 jika belum published); `CustomPageStatistic` untuk blok statistik; soft deletes.

### 5.9 Content Management — Majalah Digital
- Admin: list (cover, judul, slug, deskripsi, viewers, downloads, badge Main), upload PDF + cover, **Set Main**, delete (hapus file).
- Publik `/majalah`: majalah utama + arsip; `/majalah/{slug}` menambah viewers; download menambah downloads.
- Dikontrol feature flag `magazines`.

### 5.10 Content Management — News & Blog
- Menu terpisah, fitur identik, tabel berbeda (`news_posts` & `blog_posts`) via `ContentSection` + base model `Post`.
- CRUD artikel: judul, slug, ringkasan, konten WYSIWYG (Tiptap), cover, kategori, status Draft/Published + jadwal (`published_at`), Featured/Pin, SEO meta.
- Kategori (`categories`, tipe `news`/`blog`); publik `/news` & `/blog` dengan featured + grid + detail.
- Dikontrol feature flag `news` dan `blog`.

### 5.11 Content Management — Galeri, Timeline, Trivia
- **Galeri** (`content/gallery`): foto & video (mode `photos`/`videos`/`both` dari setting `gallery_mode`).
- **Timeline** (`content/timeline`): CRUD entri timeline.
- **Trivia** (`content/trivia`): CRUD trivia, dikontrol feature flag `trivia`.

### 5.12 Setting — Info Idola & Fanbase (About)
- Halaman `content/about` (komponen `pages::about.manage`, tab **Idol Information** & **Fansite Information**).
- **Idol:** nama, shortname, slug, foto, deskripsi, achievements, discography, jikoshoukai, tanggal/tempat lahir, golongan darah, zodiak, sosmed, tampil di welcome, **versi profil** (JKT48/General).
  - **Kabesha**: toggle tampil, **default judul**, multi-foto dengan judul + durasi, drag-and-drop reorder.
  - **Profile Details versi**: `JKT48` (Jikoshoukai & Golongan Darah; sebutan "Oshimen"; hero menampilkan `idol_name` ↔ `idol_shortname JKT48` bergantian dengan animasi skew) atau `General` (tanpa Jikoshoukai/Golongan Darah; sebutan "Idol/Bias").
- **Fansite:** nama, logo, deskripsi, **struktur organisasi** (toggle), **kegiatan** (toggle), **galeri** (maks 20, caption), **Sejarah Fansite** (toggle; sumber Custom Page / Default Page dengan gambar+deskripsi), CTA (judul + 2 tombol + background).
- Cache di-invalidate via `SettingsStore` (`Cache::forget('about_settings')`).

### 5.13 Setting — Appearance (Branding Aplikasi)
- **3 warna brand**: Primer, Sekunder, Tersier (masing-masing memetakan palet `indigo`/`violet`/`yellow` di seluruh UI, light & dark).
- **Identitas**: app name / sidebar name, deskripsi, logo, **hero image**, **login image**.
- **Hero Buttons**: 2 tombol hero welcome dapat di-custom label & tautan (Direct Custom Link / Custom Page / List Page bawaan, termasuk `/about/{idol_slug}` dan `/about/{fanbase_slug}`).
- **Youtube Playlist**: toggle tampil + link playlist + mode tampilan **Cards (Carousel)** atau **Embed**.
- Disimpan ke `app_settings` via `SettingsStore`.

### 5.14 Setting — Features Activation, Header Menu, Photobooth, Security
- **Features Activation** (`/settings/features`): toggle **News, Blog, Majalah, Trivia, Photobooth** + `gallery_mode` (photos/videos/both) + `welcome_feed_source` (news/blog/magazines/trivia). Default aktif.
- **Header Menu** (`/settings/header-menu`): kelola menu header publik (default/custom), item + relasi parent, sort, hide sesuai feature flag.
- **Photobooth** (`/settings/photobooth`): slug, jadwal (full-open/rentang), layout kolom/baris, frame, posisi slot foto, overlay transparan.
- **Security** (`/settings/security`): update password + 2FA + passkeys (jika tersedia).

### 5.15 Situs Publik
- **Layout publik**: body `flex min-h-dvh flex-col`, `<main class="flex-1">` (footer menempel dasar), header sticky translucent + dark mode toggle (localStorage + `prefers-color-scheme`), ikon sosial.
- **Header**: dropdown **About** (idola → `/about/{idol_slug}`, fanbase → `/about/{fanbase_slug}`) dan **Artikel** (News & Blog), plus Home, Majalah, Galeri, Trivia, Data, Schedule (sesuai fitur aktif).
- **Welcome** (`/`): hero dengan **hero buttons** custom, **About idola** (tombol "Berkenalan dengan {shortname}"), **Berita Terbaru** (sumber dari `welcome_feed_source`), **Statistik**, **Schedule Event Mendatang**, **Status Live**, **Galeri foto**, dan **Youtube Playlist** (cards carousel / embed).
- **About**: `/about/{slug}` menyajikan halaman idola atau fanbase (profil, kabesha, show teater, unit song, centers, sosmed / kegiatan, struktur, galeri, sejarah, CTA).
- **Lainnya**: `/news`, `/blog`, `/majalah`, `/schedule`, `/galeri`, `/timeline`, `/trivia`, `/photobooth/{slug?}`, dan catch-all `/{customPage:slug}`.
- **Halaman error** (403/404/500) bergaya fullscreen dengan gambar.

---

## 6. Skema Data Utama

| Tabel | Model | Catatan |
|---|---|---|
| `users` | `User` | `role` (enum), 2FA, `email_verified_at`, password-hash |
| `show_teater` | `ShowTeater` | PK `show_id` non-increment, tanpa timestamp, soft deletes |
| `show_teater_categories` | — (raw `DB::table`) | Self-FK `setlist_id`, tab setlist/unit song |
| `meet_greet_events` | `MeetGreetEvents` | Soft deletes |
| `concert_events` | `ConcertEvents` | Soft deletes, status enum |
| `live_streaming` | `LiveStreaming` | — |
| `categories` | `Category` | `type` = `news`/`blog`, slug unik per tipe |
| `news_posts` | `NewsPost` (extends `Post`) | Soft deletes, SEO, status, jadwal, featured |
| `blog_posts` | `BlogPost` (extends `Post`) | Soft deletes, SEO, status, jadwal, featured |
| `magazines` | `Magazine` | PDF + cover, `is_main`, `views`, `downloads` |
| `gallery_photos` / `gallery_videos` | `GalleryPhoto` / `GalleryVideo` | Foto & video galeri |
| `timeline` | `Timeline` | Entri timeline |
| `trivia` | `Trivia` | Konten trivia |
| `menu_items` | `MenuItem` | Menu header publik (custom) |
| `photobooths` | `Photobooth` | Slug, frame, kolom/baris, slots, jadwal, aktif |
| `about_settings` | `AboutSettings` | KV |
| `app_settings` | `AppSettings` | KV (branding + feature flags + hero + youtube) |
| `custom_pages` | `CustomPage` | JSON `blocks`, soft deletes |
| `theater_references` | `TheaterReference` | Tracking kode referensi API |
| `cache`, `cache_locks`, `jobs`, `sessions` | — | Infrastruktur |

---

## 7. Desain & UI

- **Warna brand:** Primer `#6C7CE8`, Sekunder `#A5B4FC`, Tersier `#FFD166` (dapat di-custom; dipetakan ke palet indigo/violet/yellow secara runtime).
- **Tipografi:** Poppins (heading) / Plus Jakarta Sans (body).
- **Komponen UI:** Livewire Flux (v2).
- **Layout admin:** sidebar + header, pola `admin-page`, `admin-table`, filter form, pagination, modal flyout (Flux).
- **Layout publik:** header sticky translucent, dark mode, kartu `rounded-[2rem]`, aksen tersier pada hero.
- **Editor konten:** `x-rich-text-editor` (Tiptap).
- **Referensi penuh:** `docs/pages-feature-reference.md`, `docs/scheduler.md`, `docs/hostinger-cron-scheduler.md`.

---

## 8. Fitur Non-Fungsional

- **Performa:** statistik dashboard & setting di-cache; data eksternal di-fetch via command terjadwal.
- **Keamanan:** 2FA, verifikasi email, RBAC, hash password, soft deletes, feature flags, API key server-side (YouTube tanpa API key).
- **Idempotensi:** `TheaterReference` mencegah duplikasi fetch; soft delete menjaga `show_id`.
- **Testing:** ±360 test PHPUnit (auth, settings, role access, feature toggle, soft delete, setiap domain data, seeder, integrasi YouTube).

---

## 9. Backlog & Area Perbaikan Potensial

Selesai (September 2026):
- [x] Page builder (drag-and-drop nested, blok, background).
- [x] Majalah digital, News & Blog, Galeri, Timeline, Trivia.
- [x] Features Activation + middleware `feature`.
- [x] Soft delete `show_teater`.
- [x] Redesign publik + error page.
- [x] Kustomisasi 3 warna brand, hero buttons, youtube playlist, login image.
- [x] Sejarah Fansite, versi profil Idol (JKT48/General), kabesha toggle + default judul.
- [x] Registrasi publik dihapus; pembuatan user via Super Admin + email verifikasi.

Sisa backlog:
1. **HTML sanitizer** untuk blok Embed (raw HTML) dan konten artikel (XSS).
2. **Unifikasi** `block-preview.blade.php` vs `render-block.blade.php`.
3. Model `ShowTeaterCategories` masih raw `DB::table` — kandidat refactor Eloquent.
4. Konsistensi akses DB: campuran Eloquent & raw `DB::table`.
5. Penerapan Policy untuk kepemilikan/modifikasi konten.
6. Feed YouTube berbasis RSS tidak andal (fallback scrape `ytInitialData` tanpa API key; deskripsi hanya tersedia saat RSS hidup).

---

## 10. Keterangan

- **Route admin/master data:** `/dashboard`, `/show-teater`, `/show-teater/categories`, `/meet-greet-events`, `/concert-events`, `/live-streaming`, `/users`.
- **Route Content Management:** `/pages`, `/magazines`, `/content/news`, `/content/blog`, `/content/categories`, `/content/gallery`, `/content/timeline`, `/content/trivia`.
- **Route pengaturan:** `/settings/appearance`, `/settings/features`, `/settings/header-menu`, `/settings/photobooth`, `/settings/security`, `/content/about`, `/profile`.
- **Route publik:** `/`, `/about/{slug}`, `/majalah`, `/news`, `/blog`, `/schedule`, `/galeri`, `/timeline`, `/trivia`, `/photobooth/{slug?}`, catch-all `/{customPage:slug}`.
- **Console:** `app:fetch-theater-shows`, `app:fetch-streaming-info`, `app:check-member-live`, `app:backfill-live-ids`.
- **Seeder:** `AppSettingsSeeder`, `AboutSeeder`, `ShowTeaterCategoriesSeeder`, `DatabaseSeeder`.
- **Sumber API eksternal:** `https://jkt48.com/api/v1/schedules?lang=id&month=...&year=...&type=SHOW`.

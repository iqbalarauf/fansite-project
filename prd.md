# PRD — FANSIGHT (Fansite Platform)

**Dokumen:** Product Requirements Document
**Versi:** 1.1
**Tanggal:** 11 September 2026
**Author:** Analisis Otomatis dari Codebase

---

## 1. Ringkasan Produk

**FANSIGHT** adalah aplikasi web fansite/idol data platform yang dirancang untuk mengumpulkan, mengelola, dan membagikan informasi serta konten penggemar (fanbase) yang didedikasikan untuk seorang idola — dalam implementasi saat ini difokuskan pada **JKT48**.

Aplikasi dibangun berbasis web dengan arsitektur terpusat, sehingga seluruh data dan rekap dapat dikelola secara **real-time dan terintegrasi**, dan dapat diakses dari banyak perangkat (PC maupun mobile).

### Tujuan Utama
1. Mengelola data statistik penampilan idola (pertunjukan teater, konser, meet & greet, live streaming).
2. Mengelola informasi fanbase dan data profil idola.
3. Mengelola konten publik: halaman custom (page builder), **Majalah digital**, serta **News & Blog**.
4. Membangun situs publik (welcome, profil, artikel, majalah) untuk membagikan data/konten kepada publik.
5. Mempermudah pengelolaan data (multi-user dengan manajemen peran) dan memberi **kontrol aktivasi fitur** konten.

### Informasi Umum
- **Situs produksi:** `https://fansight.labqitech.my.id`
- **Pengembang:** IqbalARauf (LabqiTech)
- **Konteks:** Eksperimen/proyek portofolio, sebagian besar dikembangkan dengan bantuan AI (vibe coding).
- **Lisensi:** Terbuka untuk publik, tanpa tujuan komersialisasi.

---

## 2. Target Pengguna & Persona

| Persona | Deskripsi | Peran dalam Sistem |
|---|---|---|
| **Super Admin** | Pemilik/pengelola utama sistem | Akses penuh: dashboard, master data, seluruh Content Management (Pages, Majalah, News, Blog, Kategori), pengaturan (About, App Settings, **Features Activation**), manajemen pengguna |
| **Bank Data Admin** | Anggota fanbase pengelola data statistik | Dashboard + master data (teater, konser, meet & greet, live streaming) |
| **Content Creator** | Anggota fanbase pengelola konten | Content Management: Pages, Majalah, News, Blog, Kategori |
| **View Only** | Pengamat/kontributor pasif | Akses baca-saja ke dashboard, master data, dan content management (tanpa kemampuan menulis) |
| **Publik** | Pengunjung umum | Membaca halaman publik (welcome, about, news, blog, majalah, custom pages) |

---

## 3. Arsitektur Teknologi

### Stack Utama
- **Backend:** PHP ^8.3, Laravel Framework ^13.7
- **Frontend:** Blade, Livewire ^4.1, Flux UI ^2.13, Tailwind CSS ^4.0.7, Vite ^8
- **Rich Text Editor:** **Tiptap v3** (`@tiptap/core`, `starter-kit`, `extension-link`, `extension-image`, `extension-placeholder`, `@tiptap/pm`) untuk konten News & Blog
- **Autentikasi:** Laravel Fortify ^1.37 (registrasi, login, 2FA, reset password, verifikasi email)
- **Database:** SQLite (default via `DB_CONNECTION`) / MySQL (produksi) / dapat dikonfigurasi
- **Ekosistem:** Laravel Boost, Pint (formatter), Larastan/PHPStan (static analysis), PHPUnit ^12 (testing), Sail (Docker), Pail, MCP
- **Bahasa/Lokalisasi:** Indonesia (`id`)

### Pola Arsitektur
- **MVC Laravel** konvensional untuk CRUD domain data (controllers + Form Requests + blade views).
- **Livewire Volt** single-file components untuk fitur interaktif (page builder, pengaturan, form pengaturan).
- **Enums + role-based authorization** (`UserRole`, `ContentSection`, `ConcertStatus`, `MeetGreetEventType`, `LiveStreamingPlatform`) + middleware kustom.
- **Feature flags** berbasis setting (`SettingBag::featureEnabled()`) untuk mengaktifkan/menonaktifkan News, Blog, dan Majalah.
- **Support/Service classes** (`app/Support`): `DashboardAssembler`, `WelcomePageData`, `AboutPageData`, `EventTimeline`, `SettingBag`, `ListingQuery`, `ShowDate`, `CustomPageStatistic`.
- **Soft deletes** pada `show_teater`, `meet_greet_events`, `concert_events`, `custom_pages`, dan `news_posts`/`blog_posts`, dengan penyaringan `deleted_at` di seluruh query (termasuk raw `DB::table`).
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

> Content Management mencakup: Pages (page builder), **Majalah**, **News**, **Blog**, dan **Kategori**.

**Middleware kustom:**
- `EnsureUserHasRole` (alias `role`) — membatasi akses route per peran.
- `PreventViewOnlyWrites` (alias `block-view-only-writes`) — mencegah peran view-only melakukan operasi tulis.
- `EnsureNotViewOnly` (alias `block-view-only`) — memblokir akses peran view-only ke halaman tertentu (settings super admin).
- `EnsureFeatureEnabled` (alias `feature`) — menolak request (404) bila fitur konten (`news`/`blog`/`magazines`) dinonaktifkan; menerima beberapa fitur (lolos bila salah satu aktif).

**Aturan khusus:**
- Pengguna tidak dapat mengubah role dirinya sendiri.
- Pengguna tidak dapat menghapus dirinya sendiri.
- `UserRole::masterDataRoles()` = SuperAdmin, ViewOnly, BankDataAdmin.
- `UserRole::pagesRoles()` = SuperAdmin, ViewOnly, ContentCreator.
- Fitur konten yang dinonaktifkan menyembunyikan menu (sidebar & header publik) sekaligus menonaktifkan route terkait.

---

## 5. Fitur-Fitur Utama

### 5.1 Autentikasi & Manajemen Akun (Fortify)
- Registrasi pengguna baru.
- Login & logout (custom `LoginResponse` mengarahkan ContentCreator ke halaman `/pages`).
- Verifikasi email.
- Reset & konfirmasi password.
- **Two-Factor Authentication (2FA)** dengan tombol pemulihan (recovery codes).
- Halaman pengaturan akun: profil (nama, email), keamanan (password, 2FA), appearance, hapus akun.

### 5.2 Dashboard (Analitik & Statistik)
- Filter periode: semua / 7 hari / bulanan / kuartal / 6 bulan / tahunan / **kustom**.
- **Mode perbandingan** antar periode.
- **Card statistik Show Teater**: jumlah show, setlist, unit song, center unit song, global center.
- **Card statistik Event Concert** (baris terpisah): **Off-Air**, **On-Air**, **Brand**, **Media**, dan **OFC/JKT48 Concert** (gabungan `ofc-event` + `jkt48-event`), mengikuti periode & mode perbandingan.
- Aggregasi chart aktivitas per tahun/bulan/minggu/hari (Show Teater, Event, Meet & Greet, Live Streaming).
- Hitung mundur ulang tahun (birthday countdown) + reminder.
- **Milestone Show** (kelipatan 100 show, progress bar & sisa show).
- Panel Live Streaming dan timeline event masa lalu & mendatang (gabungan semua tipe data).
- Fitur **Capture** dashboard ke PNG (html2canvas + Chart.js).
- Statistik **cached** untuk performa.

### 5.3 Master Data — Show Teater
- CRUD data pertunjukan teater (PK `show_id` integer non-increment).
- **Soft delete** (`deleted_at`) — penghapusan tidak menghilangkan baris, `show_id` dipertahankan; store dengan `show_id` yang sudah ter-soft delete akan memulihkan baris tersebut.
- Join dengan kategori setlist & unit song (nama JP).
- Konversi format tanggal `YYYY-MM-DD` → `YYYY/MM/DD`.
- **Konfirmasi / reject** pertunjukan member (via JSON; reject = soft delete).
- **Fetch manual** dari API JKT48 via Artisan command `app:fetch-theater-shows` (mencegah reuse `show_id` yang ter-soft delete & memulihkan baris yang cocok).
- Atribut: `is_global_center`, `is_us_center`, `is_the_show_has_event`, `is_scraped_data`, `is_member_show`, `last_fetch_at`.

### 5.4 Master Data — Kategori Setlist & Unit Song
- CRUD kategori setlist & unit song.
- Tampilan tabbed (setlist / unit song).
- **Toggle status aktif** (menonaktifkan setlist menurun ke unit song anaknya).
- Relasi self-FK `setlist_id` (hierarki setlist → unit song).

### 5.5 Master Data — Meet & Greet Events
- CRUD event meet & greet.
- Tipe event: **meet-greet** / **video-call** (untuk video-call `event_date_2` dapat diisi).
- Field: nama event, tipe, tanggal, tanggal 2, jadwal penjualan tiket, link pembelian, lokasi.
- Soft deletes, pencarian/filter/sort.

### 5.6 Master Data — Concert Events
- CRUD event konser.
- Status enum `ConcertStatus`: `off-air`, `on-air`, `jkt48-event`, `media`, `ofc-event`, `brand`.
- Field: nama, tanggal, lokasi, status, link pembelian.
- Soft deletes.

### 5.7 Master Data — Live Streaming
- CRUD live streaming.
- Platform enum `LiveStreamingPlatform`: **IDN App** / **Showroom**.
- Field: platform, tanggal, durasi, info tambahan.

### 5.8 Content Management — Page Builder (Custom Pages)
- Editor Livewire Volt untuk membangun halaman publik secara visual.
- **Blok pendukung:** container, text, statistic, image, video (YouTube), button, embed (raw HTML).
- **Blok image** mendukung URL eksternal **atau upload file** (disk `public`, folder `pages/`, dilacak `data.storage_path`).
- Mode tampilan: `full` / `welcome`; opsi background (preset + color picker + hex), layout multi-kolom, drag-and-drop nested sorting, validasi rekursif.
- Render publik di route `/{customPage:slug}` (404 jika belum dipublikasikan).
- Class `CustomPageStatistic` untuk blok statistik terhitung.
- Soft deletes.

### 5.9 Content Management — Majalah Digital
- **Admin** (`/magazines`): list majalah (cover, judul, slug, deskripsi, **jumlah viewers**, **jumlah downloads**, badge **Main Magazine**).
- Tambah majalah: upload **file PDF**, **cover**, deskripsi, **slug**, dan opsi **Choose as Main Magazine** (slug auto-generate dari judul).
- Aksi: **Edit** (slug & deskripsi), **Set Main** (menonaktifkan main lama), **Delete** (termasuk hapus file).
- **Publik** (`/majalah`): menampilkan **majalah terbaru/utama** (cover, deskripsi, tombol Baca & Download) dan **arsip majalah dalam bentuk tabel**.
- Halaman baca (`/majalah/{slug}`) menambah **viewers**; route download menambah **downloads** dan mengirim file PDF dengan nama asli.
- Dikontrol oleh feature flag `magazines`.

### 5.10 Content Management — News & Blog
- Dua menu terpisah dengan **fitur identik namun tabel berbeda** (`news_posts` & `blog_posts`), diimplementasikan via `ContentSection` enum + base model `Post` (DRY).
- CRUD artikel: judul, slug, ringkasan, **konten WYSIWYG (Tiptap)**, cover, **kategori**, **status Draft/Published + jadwal terbit** (`published_at`), **Featured/Pin**, dan **SEO meta** (meta title, meta description, OG image).
- List admin: filter (cari/status/kategori/sort), thumbnail, badge featured/status, link publik, aksi edit & delete.
- **Kategori** (`categories`, tipe `news`/`blog`, slug unik per tipe) dikelola di satu halaman dengan tab.
- **Publik** `/news` & `/blog`: list (sorotan **Featured** + grid + pagination) dan detail artikel (cover, konten, kategori, tanggal, artikel terkait), dengan meta description & Open Graph dari field SEO.
- Dikontrol feature flag `news` dan `blog` secara terpisah.

### 5.11 Setting — Info Idola & Fanbase
- Halaman pengaturan About dengan tab **Idol Information** dan **Fansite Information** (KV `about_settings`).
- Idol: nama, foto, deskripsi, achievements, discography, jikoshoukai, tanggal/tempat lahir, golongan darah, zodiak, sosmed.
- Fansite: nama, logo, deskripsi, aktivitas, galeri (maks. 5), dan CTA (judul + 2 tombol + background).
- Cache setting di-invalidate (`Cache::forget('about_settings')`) saat disimpan.

### 5.12 Setting — Branding Aplikasi
- `app_settings` (KV) untuk branding: nama app, sidebar name, deskripsi, logo, **hero image**.
- Cache di-invalidate (`Cache::forget('app_settings')`) saat disimpan.
- Pengelolaan via Volt component `⚡app-settings`.

### 5.13 Setting — Features Activation
- Halaman khusus **Features Activation** (`/settings/features`) untuk mengaktifkan/menonaktifkan fitur konten:
  - **News** (`news_enabled`)
  - **Blog** (`blog_enabled`)
  - **Majalah** (`magazines_enabled`)
- Default aktif. Menonaktifkan fitur → route publik & admin 404 serta menu terkait disembunyikan (sidebar & header publik). `SettingBag::featureEnabled()` membaca nilai ini (default `true`).

### 5.14 Situs Publik
- **Layout publik** (`layouts/public.blade.php`): body flex kolom `min-h-dvh` + `<main class="flex-1">` sehingga **footer otomatis menempel dasar layar** saat konten pendek; header sticky, dark mode toggle (localStorage + `prefers-color-scheme`), ikon sosial media.
- **Header** memiliki dropdown **About** (nama idola & nama fanbase → `/about/idol`, `/about/fansite`) dan **Artikel** (News & Blog), plus link Home, Majalah, Data, Schedule (menyesuaikan fitur yang aktif).
- **Welcome** (`/`): hero foto produksi, bagian **About idola**, **Berita Terbaru** (list news terbaru yang sudah published, tampil bila fitur News aktif), **Statistik** (jumlah show/setlist/partisipasi), **Schedule Event Mendatang** (dengan link pembelian), dan **Status Live**.
- **About** (`/about/idol`, `/about/fansite`): halaman profil idola (foto, detail, pencapaian, diskografi, jikoshoukai, sosmed) dan profil fanbase (logo, deskripsi, kegiatan, galeri, CTA).
- **News & Blog** publik (`/news`, `/blog`) serta **Majalah** (`/majalah`).

---

## 6. Skema Data Utama

| Tabel | Model | Catatan |
|---|---|---|
| `users` | `User` | `role` (enum), 2FA columns, password-hash |
| `show_teater` | `ShowTeater` | PK `show_id` non-increment, tanpa timestamp, **soft deletes** |
| `show_teater_categories` | — (raw `DB::table`) | Self-FK `setlist_id`, tab setlist/unit song |
| `meet_greet_events` | `MeetGreetEvents` | Soft deletes |
| `concert_events` | `ConcertEvents` | Soft deletes, status enum |
| `live_streaming` | `LiveStreaming` | — |
| `categories` | `Category` | `type` = `news`/`blog`, slug unik per tipe |
| `news_posts` | `NewsPost` (extends `Post`) | Soft deletes, SEO, status, jadwal, featured |
| `blog_posts` | `BlogPost` (extends `Post`) | Soft deletes, SEO, status, jadwal, featured |
| `magazines` | `Magazine` | File PDF + cover, `is_main`, `views`, `downloads` |
| `about_settings` | `AboutSettings` | KV |
| `app_settings` | `AppSettings` | KV (branding + feature flags) |
| `custom_pages` | `CustomPage` | JSON `blocks`, soft deletes |
| `theater_references` | `TheaterReference` | Tracking kode referensi API |
| `cache`, `cache_locks` | — | Cache store default `database` |
| `jobs` | — | Queue |
| `password_reset_tokens`, `sessions` | — | — |

---

## 7. Desain & UI

- **Primary color:** Indigo `#6C7CE8`; **Accent:** Emas `#FFD166`.
- **Tipografi:** Poppins (heading) / Plus Jakarta Sans (body).
- **Komponen UI:** Livewire Flux (v2).
- **Layout admin:** sidebar + header, pola `admin-page`, `admin-page-header`, `admin-table`, filter form, pagination partial, flyout modal (Flux).
- **Layout publik:** header sticky translucent, dark mode, card `rounded-[2rem]`, aksen kuning pada hero.
- **Editor konten:** komponen Blade `x-rich-text-editor` (Tiptap) dengan toolbar (bold/italic/underline/strike, H2/H3, list, quote, code, link, image, undo/redo).
- **Referensi penuh:** `docs/pages-feature-reference.md`

---

## 8. Fitur Non-Fungsional

- **Performa:** Statistik dashboard & setting di-cache; pipeline fetch data mengurangi beban manual.
- **Keamanan:** 2FA, verifikasi email, RBAC, hash password, **soft deletes** untuk recovery data, feature flags berbasis setting.
- **Idempotensi:** `TheaterReference` mencegah duplikasi saat fetch dari API JKT48; soft delete pada `show_teater` menjaga `show_id` tetap stabil.
- **Testing:** ±33 file test PHPUnit (feature) yang mencakup auth, settings, role access, feature toggle, soft delete, dan setiap domain data.

---

## 9. Backlog & Area Perbaikan Potensial

Status selesai (September 2026):

- [x] Drag-and-drop nested sorting & validasi rekursif pada page builder.
- [x] Redesign halaman publik (welcome, about, header/footer, dark mode, footer sticky).
- [x] **Majalah digital** (admin + publik, viewers/downloads, main magazine).
- [x] **News & Blog** (implementasi bersama, tabel terpisah, kategori, SEO, penjadwalan, WYSIWYG Tiptap).
- [x] **Features Activation** (toggle News/Blog/Majalah) + middleware `feature`.
- [x] **Soft delete** pada `show_teater` tanpa mengubah `show_id`, termasuk penyesuaian scraper.
- [x] Card statistik **Event Concert** di dashboard.

Sisa backlog yang belum dikerjakan:

1. **HTML sanitizer** untuk blok Embed (raw HTML) dan konten artikel (XSS).
2. **Unifikasi** markup `block-preview.blade.php` vs `render-block.blade.php`.
3. **Model `ShowTeaterCategories`** belum ada (masih `DB::table` raw) — kandidat refactor ke Eloquent.
4. Konsistensi akses DB: campuran Eloquent model & raw `DB::table` antar controller/support.
5. Test tambahan untuk mode tampilan (full/welcome) & background color rendering page builder.
6. Otorisasi tingkat-kebijakan: penerapan Policy untuk kepemilikan/modifikasi konten.

---

## 10. Keterangan

- **Route admin/master data:** `/dashboard`, `/show-teater`, `/show-teater/categories`, `/meet-greet-events`, `/concert-events`, `/live-streaming`, `/users`.
- **Route Content Management:** `/pages`, `/magazines`, `/content/news`, `/content/blog`, `/content/categories`.
- **Route pengaturan:** `/settings/appearance`, `/settings/about`, `/settings/app-settings`, `/settings/features`, `/settings/security`, `/profile`.
- **Route publik:** `/`, `/about/idol`, `/about/fansite`, `/majalah`, `/majalah/{slug}`, `/majalah/{slug}/download`, `/news`, `/news/{slug}`, `/blog`, `/blog/{slug}`, serta catch-all `/{customPage:slug}`.
- **Console:** `app:fetch-theater-shows` (fetch jadwal dari API JKT48) + `inspire`.
- **Seeder:** `AboutSeeder`, `AppSettingsSeeder`, `ShowTeaterCategories`, `DatabaseSeeder`.
- **Sumber API eksternal:** `https://jkt48.com/api/v1/schedules?lang=id&month=...&year=...&type=SHOW`.

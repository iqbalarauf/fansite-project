# PRD — FANSIGHT (Fansite Platform)

**Dokumen:** Product Requirements Document
**Versi:** 1.2
**Tanggal:** 20 September 2026
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
6. Menyinkronkan master data dengan **Google Sheets** (manual & auto-sync) serta **ekspor/impor Excel (XLSX)**.

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
- **Autentikasi:** Laravel Fortify ^1.37 (login, 2FA, reset password, verifikasi email — **registrasi publik dinonaktifkan**)
- **Integrasi Google:** `google/apiclient` ^2.19 (Google Sheets API untuk Sheet Integration)
- **Database:** SQLite (default via `DB_CONNECTION`) / MySQL (produksi) / dapat dikonfigurasi
- **Ekosistem:** Laravel Boost, Pint (formatter), Larastan/PHPStan (static analysis), PHPUnit ^12 (testing), Sail (Docker), Pail, MCP
- **Bahasa/Lokalisasi:** Indonesia (`id`)

### Pola Arsitektur
- **MVC Laravel** konvensional untuk CRUD domain data (controllers + Form Requests + blade views).
- **Livewire Volt** single-file components untuk fitur interaktif (page builder, pengaturan, form pengaturan).
- **Enums + role-based authorization** (`UserRole`, `ContentSection`, `ConcertStatus`, `MeetGreetEventType`, `LiveStreamingPlatform`, `MasterData`, `SyncMode`, `DiffStatus`) + middleware kustom.
- **Feature flags** berbasis setting (`SettingBag::featureEnabled()`) untuk mengaktifkan/menonaktifkan News, Blog, Majalah, Trivia, Photobooth, dan Sheet Integration.
- **Support/Service classes** (`app/Support`): `DashboardAssembler`, `WelcomePageData`, `AboutPageData`, `EventTimeline`, `SettingBag`, `SettingsStore`, `ListingQuery`, `ShowDate`, `CustomPageStatistic`, `BrandPalette`, `HeroLink`, `HeaderMenu`, `YoutubeEmbed`/`YoutubeRss`/`YoutubePlaylist`, `Spreadsheet`, `Csv`.
- **Soft deletes** pada `show_teater`, `meet_greet_events`, `concert_events`, `custom_pages`, `news_posts`/`blog_posts`, `magazines`, `gallery_photos`/`gallery_videos`, `timelines`, dan `trivias`, dengan penyaringan `deleted_at` di seluruh query (termasuk raw `DB::table`).
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
- `EnsureSheetIntegrationEnabled` (alias `sheet-integration`) — menolak request (404) bila fitur Sheet Integration dinonaktifkan.

**Aturan khusus:**
- Pengguna tidak dapat mengubah role dirinya sendiri.
- Pengguna tidak dapat menghapus dirinya sendiri.
- `UserRole::masterDataRoles()` = SuperAdmin, ViewOnly, BankDataAdmin.
- `UserRole::pagesRoles()` = SuperAdmin, ViewOnly, ContentCreator.
- Fitur konten yang dinonaktifkan menyembunyikan menu (sidebar & header publik) sekaligus menonaktifkan route terkait.

---

## 5. Fitur-Fitur Utama

### 5.1 Autentikasi & Manajemen Akun (Fortify)
- **Registrasi publik dihapus** — pengguna baru dibuat oleh Super Admin (`UserController`) dan dikirimi email verifikasi (`User implements MustVerifyEmail`).
- Login & logout (custom `LoginResponse` mengarahkan ContentCreator ke halaman `/pages`).
- Verifikasi email.
- Reset & konfirmasi password.
- **Two-Factor Authentication (2FA)** dengan tombol pemulihan (recovery codes).
- Halaman pengaturan akun: profil (nama, email), keamanan (password, 2FA), hapus akun.

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

### 5.11 Setting — Info Idola & Fanbase (About)
- Halaman standalone **About** (`/content/about`) dengan tab **Idol Information** dan **Fansite Information** (KV `about_settings`).
- Idol: nama, slug, foto, deskripsi, achievements, discography, jikoshoukai, tanggal/tempat lahir, golongan darah, zodiak, sosmed, serta **versi profil** (`jkt48`/`general` — jikoshoukai & golongan darah hanya tampil pada versi JKT48, istilah Oshimen vs Idol/Bias).
- **Kabesha** (toggle tampil/tidak) + judul default.
- Fansite: nama, logo, deskripsi, aktivitas, galeri (maks. 5), CTA (judul + 2 tombol + background), toggle **Struktur Organisasi** & **Kegiatan Fanbase**, serta **Sejarah Fansite** (enable + sumber default/custom page + daftar foto & deskripsi).
- Cache setting di-invalidate (`Cache::forget('about_settings')`) saat disimpan.

### 5.12 Setting — Appearance (Branding)
- Halaman standalone **Appearance** (`/appearance`, komponen `pages::appearance.index`) untuk branding & tampilan (`app_settings`), layout multi-kolom:
  - **Brand Color**: 3 warna — Primer (`brand_color`), Sekunder (`brand_color_secondary`), Tersier (`brand_color_tertiary`) — dipetakan ke `indigo`/`violet`/`yellow` via `partials/brand-colors.blade.php` (light & dark).
  - **App Identity**: App Name/Sidebar Name, deskripsi, **App Logo**, **Hero Image**, **Login Image** (upload + preview).
  - **Hero Buttons**: 2 tombol hero (aktif/nonaktif, label, tipe tautan `url`/`page`/`list`, nilai).
  - **Youtube Playlist**: aktif/nonaktif, link playlist, mode tampilan `cards` (RSS carousel, tanpa API key) / `embed` (player playlist).
- Cache di-invalidate (`Cache::forget('app_settings')`) saat disimpan.

### 5.13 Setting — Features Activation
- Halaman standalone **Features Activation** (`/features`, komponen `pages::features.index`), layout 2 kolom:
  - **Fitur konten**: News (`news_enabled`), Blog (`blog_enabled`), Majalah (`magazines_enabled`), Trivia (`trivia_enabled`), Photobooth (`photobooth_enabled`), Sheet Integration (`sheet_integration_enabled`).
  - **Galeri — Tampilan Publik** (`gallery_mode`: `photos`/`videos`/`both`) dan **Kartu "Berita Terbaru" (Welcome)** (`welcome_feed_source`: `news`/`blog`/`magazines`/`trivia`).
- Default aktif (kecuali Sheet Integration default nonaktif). `SettingBag::featureEnabled()` membaca nilai ini; `SettingBag::sheetIntegrationEnabled()` untuk Sheet Integration. Menonaktifkan fitur → route publik & admin 404 serta menu terkait disembunyikan.

### 5.14 Setting — Header Menu
- Halaman standalone **Header Menu** (`/header-menu`, komponen `pages::header-menu.index`).
- Mode **Default Menu** (bawaan) atau **Custom Menu** (`menu_items`: label, tipe `link`/`group`/`page`/`page_list`/`blog`/`news`, parent & urutan; validasi siklus dan group minimal 1 submenu).
- Preview menu default & custom real-time.

### 5.15 Sheet Integration — Sinkronisasi Google Sheets
- Sinkronisasi master data (Show Teater, Live Streaming, Concert & Event, Meet & Greet) dengan Google Sheets; **nonaktif secara default** (`sheet_integration_enabled`).
- Halaman **Sheet Integration** (`/sheet-integration`, komponen `pages::sheet-integration.comparison`): konfigurasi per master (Spreadsheet ID, Nama Sheet, Header First Cell, Aktif, **Auto-Sync**), perbandingan baris/kolom DB vs Sheet, resolusi per kolom/baris (Database/Sheet/Lewati via ikon), penerapan manual dua arah.
- **Auto-Sync**: `fillMissing()` mengisi baris yang hanya ada di satu sisi ke sisi lain; terjadwal lewat command `app:sync-google-sheets` (hourly, `withoutOverlapping`).
- Enums `MasterData`, `SyncMode`, `DiffStatus`; service `SheetSyncService`; client `GoogleApiSheetsClient` (kontrak `GoogleSheetsClient`).

### 5.16 Ekspor & Impor Excel
- **Ekspor XLSX** (tanpa dependensi eksternal — `App\Support\Spreadsheet` + `App\Support\Csv`) untuk Show Teater, Live Streaming, Meet & Greet, Concert & Event (route `*.export`).
- **Impor XLSX/CSV** untuk Kategori Setlist & Unit Song (`show-teater.categories.import`, upsert).

### 5.17 Situs Publik
- **Layout publik** (`layouts/public.blade.php`): body flex kolom `min-h-dvh` + `<main class="flex-1">` sehingga **footer otomatis menempel dasar layar** saat konten pendek; header sticky, dark mode toggle (localStorage + `prefers-color-scheme`), ikon sosial media.
- **Header** memiliki dropdown **About** (nama idola & nama fanbase → `/about/idol`, `/about/fansite`) dan **Artikel** (News & Blog), plus link Home, Majalah, Data, Schedule (menyesuaikan fitur yang aktif).
- **Welcome** (`/`): hero foto produksi dengan **2 tombol hero** (label & tautan dari Appearance) + tombol **Berkenalan dengan {idol_shortname}**, bagian **About idola**, **Youtube Playlist** (cards carousel / embed), **Berita Terbaru** (dari `welcome_feed_source`, tampil bila fitur aktif), **Statistik** (jumlah show/setlist/partisipasi), **Schedule Event Mendatang** (dengan link pembelian), dan **Status Live**.
- **About** (`/about/idol`, `/about/fansite`): halaman profil idola (foto, detail, pencapaian, diskografi, jikoshoukai, sosmed) dan profil fanbase (logo, deskripsi, kegiatan, galeri, CTA, struktur organisasi, dan **modal Sejarah Fansite**).
- **News & Blog** publik (`/news`, `/blog`) serta **Majalah** (`/majalah`).

---

## 6. Skema Data Utama

| Tabel | Model | Catatan |
|---|---|---|
| `users` | `User` | `role` (enum), 2FA columns, password-hash |
| `show_teater` | `ShowTeater` | PK `show_id` non-increment, tanpa timestamp, **soft deletes**, FK `setlist_id` (nullable) |
| `show_teater_categories` | `ShowTeaterCategories` | Self-FK `setlist_id`, tab setlist/unit song |
| `show_teater_unit_song` | (pivot) | Pivot `show_id` ↔ `show_teater_categories.id` + `position` (unit song, mendukung double US) |
| `meet_greet_events` | `MeetGreetEvents` | Soft deletes |
| `concert_events` | `ConcertEvents` | Soft deletes, status enum |
| `live_streaming` | `LiveStreaming` | — |
| `categories` | `Category` | `type` = `news`/`blog`, slug unik per tipe |
| `news_posts` | `NewsPost` (extends `Post`) | Soft deletes, SEO, status, jadwal, featured, audit `created_by`/`updated_by`, FULLTEXT `(title, excerpt)` |
| `blog_posts` | `BlogPost` (extends `Post`) | Soft deletes, SEO, status, jadwal, featured, audit `created_by`/`updated_by`, FULLTEXT `(title, excerpt)` |
| `magazines` | `Magazine` | File PDF + cover, `is_main`, `views`, `downloads`, soft deletes, audit |
| `about_settings` | `AboutSettings` | KV |
| `app_settings` | `AppSettings` | KV (branding + feature flags) |
| `custom_pages` | `CustomPage` | JSON `blocks`, soft deletes |
| `theater_references` | `TheaterReference` | Tracking kode referensi API |
| `sheet_integrations` | `SheetIntegration` | Konfigurasi per master (spreadsheet, sheet, header, mode, auto_sync) |
| `menu_items` | `MenuItem` | Item menu custom header (parent, tipe, urutan) |
| `cache`, `cache_locks` | — | Cache store default `database` |
| `jobs` | — | Queue |
| `password_reset_tokens`, `sessions` | — | — |

---

## 7. Desain & UI

- **Primary color:** Indigo `#6C7CE8`; **Accent:** Emas `#FFD166`.
- **Warna brand (3, runtime):** Primer `#6C7CE8`, Sekunder `#A5B4FC`, Tersier `#FFD166` — di-set via `partials/brand-colors.blade.php` (memetakan ulang `indigo`/`violet`/`yellow`, light & dark).
- **Tipografi:** Poppins (heading) / Plus Jakarta Sans (body).
- **Komponen UI:** Livewire Flux (v2).
- **Layout admin:** sidebar + header, pola `admin-page`, `admin-page-header`, `admin-table`, filter form, pagination partial, flyout modal (Flux). Halaman konfigurasi standalone (Appearance, Features Activation, Header Menu) memakai heading `flux:heading` + `flux:subheading` tanpa tabs Settings.
- **Layout publik:** header sticky translucent, dark mode, card `rounded-[2rem]`, aksen kuning pada hero.
- **Editor konten:** komponen Blade `x-rich-text-editor` (Tiptap) dengan toolbar (bold/italic/underline/strike, H2/H3, list, quote, code, link, image, undo/redo).
- **Referensi penuh:** `docs/pages-feature-reference.md`

---

## 8. Fitur Non-Fungsional

- **Performa:** Statistik dashboard & setting di-cache; pipeline fetch data mengurangi beban manual.
- **Keamanan:** 2FA, verifikasi email, RBAC, hash password, **soft deletes** untuk recovery data, feature flags berbasis setting, serta **sanitasi HTML** (`App\Support\HtmlSanitizer`) untuk konten artikel (profil ketat) dan blok embed page builder (profil longgar) guna mencegah XSS.
- **Idempotensi:** `TheaterReference` mencegah duplikasi saat fetch dari API JKT48; soft delete pada `show_teater` menjaga `show_id` tetap stabil.
- **Testing:** 60 file test PHPUnit (feature) yang mencakup auth, settings, role access, feature toggle, soft delete, setiap domain data, Sheet Integration, ekspor/impor Excel, serta **HTML sanitizer** & `sort_order` konten.

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
- [x] **Brand palette 3 warna** (Primer/Sekunder/Tersier) runtime + hero buttons + Youtube playlist.
- [x] **Login split-screen** (login image) & error pages (403/404/500) dengan app name dari pengaturan.
- [x] **Registrasi publik dihapus** — pengguna dibuat Super Admin + verifikasi email.
- [x] **Sheet Integration** (Google Sheets) manual & auto-sync.
- [x] **Ekspor/impor Excel (XLSX/CSV)** master data.
- [x] **Header Menu** custom (mode default/custom).
- [x] Halaman pengaturan konfigurasi dipisah ke halaman standalone + navigasi Sidebar → Configuration.
- [x] **Fase 1 hardening database:** default `users.role` = `view_only`, FK `sessions.user_id` (cascade), index & tipe boolean `show_teater`, model Eloquent `ShowTeaterCategories`.
- [x] **Normalisasi `show_teater`:** `setlist_id` + pivot `show_teater_unit_song` (backfill dari teks, pencocokan name/jp_name, sinkron saat store/update/scrape, fallback teks).
- [x] **Refactor kategori:** `ShowTeaterCategoriesController` & `ShowTeaterController` memakai model Eloquent `ShowTeaterCategories` (scopes `setlists`/`unitSongs`/`active`).
- [x] **Fase 2:** index komposit (`menu_items`, `news_posts`/`blog_posts`, `photobooths`, `live_streaming`, `timelines`), kolom `sort_order` pada tabel galeri/timeline/trivia (schema + urutan baca), **soft delete** untuk tabel konten Eloquent (`magazines`, `gallery_*`, `timelines`, `trivias`).
- [x] **Fase 3:** kolom audit `created_by`/`updated_by` (trait `HasAuditColumns` pada post, magazine, custom page), index **FULLTEXT** `(title, excerpt)` untuk news/blog, typed accessors `SettingBag::bool/string/int/array`.
- [x] **HTML Sanitizer** (`App\Support\HtmlSanitizer`, berbasis DOM) untuk konten artikel (profil `article`) dan blok embed page builder (profil `embed`).
- [x] **Input `sort_order`** pada UI admin galeri (foto/video), timeline, dan trivia.
- [x] **Konsolidasi migrasi:** 10 migrasi inkremental (change/add/optimize/adjust) digabung ke file `create` terkait → 35 menjadi **25 file** (baseline tetap berbasis migrasi; kompatibel MySQL & SQLite).

Sisa backlog yang belum dikerjakan:

1. **Unifikasi** markup `block-preview.blade.php` vs `render-block.blade.php`.
2. Unifikasi tabel `news_posts`/`blog_posts` menjadi satu tabel `posts` + `type` (migrasi & query besar; dipertimbangkan bila jenis konten bertambah).
3. Konsistensi akses DB: sisa raw `DB::table` (mis. `ShowTeaterCategoriesSeeder` bulk insert sengaja raw).
4. Test tambahan untuk mode tampilan (full/welcome) & background color rendering page builder.
5. Otorisasi tingkat-kebijakan: penerapan Policy untuk kepemilikan/modifikasi konten.
6. Pertimbangkan penghapusan kolom teks `show_teater.setlist`/`unit_song` setelah seluruh jalur baca memakai normalisasi.

---

## 10. Keterangan

- **Route admin/master data:** `/dashboard`, `/show-teater`, `/show-teater/categories`, `/meet-greet-events`, `/concert-events`, `/live-streaming`, `/users`, plus ekspor `show-teater/export`, `meet-greet-events/export`, `concert-events/export`, `live-streaming/export` dan impor `show-teater/categories/import`.
- **Route Content Management:** `/pages`, `/magazines`, `/content/news`, `/content/blog`, `/content/categories`.
- **Route konfigurasi (standalone):** `/appearance`, `/features`, `/header-menu`, `/sheet-integration`, `/content/about`, `/users`; **Settings:** `/profile`, `/settings/security`, `/settings/photobooth`.
- **Route publik:** `/`, `/about/idol`, `/about/fansite`, `/majalah`, `/majalah/{slug}`, `/majalah/{slug}/download`, `/news`, `/news/{slug}`, `/blog`, `/blog/{slug}`, serta catch-all `/{customPage:slug}`.
- **Console:** `app:fetch-theater-shows` (fetch jadwal), `app:fetch-streaming-info` (live streaming), `app:check-member-live`, `app:backfill-live-ids`, `app:sync-google-sheets` (auto-sync, hourly), `app:audit-show-teater-mapping`, `app:backfill-show-teater-normalization` + `inspire`.
- **Seeder:** `AboutSeeder`, `AppSettingsSeeder`, `ShowTeaterCategoriesSeeder`, `DatabaseSeeder`.
- **Sumber API eksternal:** `https://jkt48.com/api/v1/schedules?lang=id&month=...&year=...&type=SHOW` + Google Sheets API.

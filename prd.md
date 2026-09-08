# PRD — FANSIGHT (Fansite Platform)

**Dokumen:** Product Requirements Document
**Versi:** 1.0
**Tanggal:** 8 September 2026
**Author:** Analisis Otomatis dari Codebase

---

## 1. Ringkasan Produk

**FANSIGHT** adalah aplikasi web fansite/idol data platform yang dirancang untuk mengumpulkan, mengelola, dan membagikan informasi serta konten penggemar (fanbase) yang didedikasikan untuk seorang idola — dalam implementasi saat ini difokuskan pada **JKT48**.

Aplikasi dibangun berbasis web dengan arsitektur terpusat, sehingga seluruh data dan rekap dapat dikelola secara **real-time dan terintegrasi**, dan dapat diakses dari banyak perangkat (PC maupun mobile).

### Tujuan Utama
1. Mengelola data statistik penampilan idola (pertunjukan teater, konser, meet & greet, live streaming).
2. Mengelola informasi fanbase dan data profil idola.
3. Membangun halaman publik (page builder) untuk membagikan data secara terbuka kepada publik.
4. Mempermudah pengelolaan data yang dilakukan fanbase (multi-user dengan manajemen peran).

### Informasi Umum
- **Situs produksi:** `https://fansight.labqitech.my.id`
- **Pengembang:** IqbalARauf (LabqiTech)
- **Konteks:** Eksperimen/proyek portofolio, sebagian besar dikembangkan dengan bantuan AI (vibe coding).
- **Lisensi:** Terbuka untuk publik, tanpa tujuan komersialisasi.

---

## 2. Target Pengguna & Persona

| Persona | Deskripsi | Peran dalam Sistem |
|---|---|---|
| **Super Admin** | Pemilik/pengelola utama sistem | Akses penuh: dashboard, seluruh master data, halaman, pengaturan, manajemen pengguna |
| **Bank Data Admin** | Anggota fanbase pengelola data statistik | Dashboard + master data (teater, konser, meet & greet, live streaming), tanpa manajemen pengguna |
| **Content Creator** | Anggota fanbase pengelola konten/halaman | Hanya akses halaman (page builder) |
| **View Only** | Pengamat/kontributor pasif | Akses baca-saja ke dashboard, master data, dan halaman (tanpa kemampuan menulis) |
| **Publik** | Pengunjung umum | Membaca halaman publik yang dipublikasikan |

---

## 3. Arsitektur Teknologi

### Stack Utama
- **Backend:** PHP ^8.3, Laravel Framework ^13.7
- **Frontend:** Blade, Livewire ^4.1, Flux UI ^2.13, Tailwind CSS ^4.0.7, Vite ^8
- **Autentikasi:** Laravel Fortify ^1.37 (registrasi, login, 2FA, reset password, verifikasi email)
- **Database:** SQLite (default) / dapat dikonfigurasi
- **Ekosistem:** Laravel Boost, Pint (formatter), Larastan/PHPStan (static analysis), PHPUnit ^12 (testing), Sail (Docker), Pail, MCP
- **Bahasa/Lokalisasi:** Indonesia (`id`)

### Pola Arsitektur
- **MVC Laravel** konvensional untuk CRUD domain data (controllers + blade views).
- **Livewire Volt** single-file components untuk fitur interaktif (page builder, pengaturan akun/pengaturan halaman).
- **Enums dengan role-based authorization** + middleware kustom untuk kontrol akses granular.
- **Arsitektur terpusat** dengan data real-time dan caching.
- **Pipeline fetch data** dari API eksternal (JKT48 public API) via Artisan command.

---

## 4. Definisi Peran & Otorisasi

Enums: `App\Enums\UserRole`

| Role | Dashboard & Master Data | Halaman (Pages) | Manajemen User & Settings |
|---|---|---|---|
| `super_admin` | ✅ | ✅ | ✅ |
| `bank_data_admin` | ✅ | ❌ | ❌ |
| `content_creator` | ❌ | ✅ | ❌ |
| `view_only` | ✅ (baca) | ✅ (baca) | ❌ |

**Middleware kustom:**
- `EnsureUserHasRole` (alias `role`) — membatasi akses route per peran.
- `PreventViewOnlyWrites` (alias `block-view-only-writes`) — mencegah peran view-only melakukan operasi tulis.
- `EnsureNotViewOnly` (alias `block-view-only`) — memblokir akses peran view-only ke halaman tertentu (settings super admin).

**Aturan khusus:**
- Pengguna tidak dapat mengubah role dirinya sendiri.
- Pengguna tidak dapat menghapus dirinya sendiri.
- `UserRole::masterDataRoles()` = SuperAdmin, ViewOnly, BankDataAdmin.
- `UserRole::pagesRoles()` = SuperAdmin, ViewOnly, ContentCreator.

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
- Aggregasi chart per tahun/bulan/minggu/hari.
- Hitung mundur ulang tahun (birthday countdown).
- Counter milestone (pencapaian).
- Timeline event masa lalu & mendatang (gabungan semua tipe data).
- Statistik **cached** untuk performa.

### 5.3 Master Data — Show Teater
- CRUD data pertunjukan teater (PK `show_id` integer non-increment).
- Join dengan kategori setlist & unit song (nama JP).
- Konversi format tanggal `YYYY-MM-DD` → `YYYY/MM/DD`.
- **Konfirmasi / reject** pertunjukan member (via JSON).
- **Fetch manual** dari API JKT48 via Artisan command `app:fetch-theater-shows`.
- Atribut: `is_global_center`, `is_us_center`, `is_the_show_has_event`, `is_scraped_data`, `is_member_show`, `last_fetch_at`.

### 5.4 Master Data — Kategori Setlist & Unit Song
- CRUD kategori setlist & unit song.
- Tampilan tabbed (setlist / unit song).
- **Toggle status aktif** (bertingkat: menonaktifkan unit song menurun ke anak).
- Relasi self-FK `setlist_id` (hierarki setlist → unit song).

### 5.5 Master Data — Meet & Greet Events
- CRUD event meet & greet.
- Tipe event: **meet-greet** / **video-call** (untuk video-call `event_date_2` wajib diisi).
- Field: nama event, tipe, tanggal, tanggal 2, jadwal penjualan tiket, link pembelian, lokasi.
- Soft deletes, pencarian/filter/sort.

### 5.6 Master Data — Concert Events
- CRUD event konser.
- Status enum: `off-air`, `on-air`, `jkt48-event`, `media`, `ofc-event`, `brand`.
- Field: nama, tanggal, lokasi, status, link pembelian.
- Soft deletes.

### 5.7 Master Data — Live Streaming
- CRUD live streaming.
- Platform enum: **IDN App** / **Showroom**.
- Field: platform, tanggal, durasi, info tambahan.

### 5.8 Page Builder (Custom Pages)
- Editor Livewire Volt untuk membangun halaman publik secara visual.
- **Blok pendukung:** container, text, statistic, image, video (YouTube), button, embed (raw HTML).
- **Blok image** mendukung dua sumber: URL eksternal **atau upload file** (store di disk `public` folder `pages/`, dilacak `data.storage_path`; validasi `image, max:3072`; file lama dihapus saat ganti/hapus blok/hapus page) — `uploadImage()` / `removeImage()`.
- Mode tampilan: `full` / `welcome`.
- Opsi background (halaman & container) dengan preset swatch + color picker + input hex (`#RRGGBB`), layout multi-kolom.
- Pratinjau blok (`block-preview`) & halaman.
- Render publik (`render-block`) di route `/{customPage:slug}` (404 jika belum dipublikasikan).
- Class `CustomPageStatistic` untuk blok statistik terhitung.
- Soft deletes.

### 5.9 Setting — Info Idola & Fanbase
- Halaman publik `idol` dan `fanbase` berdasarkan `about_settings` (KV).
- Pengelolaan via Volt component `⚡about`.
- Menggunakan Storage URLs untuk media.

### 5.10 Setting — Branding Aplikasi
- `AppSettings` (KV) untuk branding aplikasi (nama, logo, dll).
- Pengelolaan via Volt component `⚡app-settings`.

### 5.11 Halaman Publik (Welcome)
- Tampilan landing/welcome dengan ringkasan data dari semua domain via raw `DB::table` queries.

---

## 6. Skema Data Utama

| Tabel | Model | Catatan |
|---|---|---|
| `users` | `User` | `role` (enum), 2FA columns, password-hash |
| `show_teater` | `ShowTeater` | PK `show_id` non-increment, tanpa timestamp |
| `show_teater_categories` | `ShowTeaterCategories` | Self-FK `setlist_id`, tab setlist/unit song |
| `meet_greet_events` | `MeetGreetEvents` | Soft deletes |
| `concert_events` | `ConcertEvents` | Soft deletes |
| `live_streaming` | `LiveStreaming` | — |
| `about_settings` | `AboutSettings` | KV |
| `app_settings` | `AppSettings` | KV |
| `custom_pages` | `CustomPage` | JSON `blocks`, soft deletes |
| `theater_references` | `TheaterReference` | Tracking kode referensi API |
| `cache`, `cache_locks` | — | — |
| `jobs` | — | Queue |
| `password_reset_tokens`, `sessions` | — | — |

---

## 7. Desain & UI

- **Primary color:** Indigo `#6C7CE8`
- **Accent:** Emas `#FFD166`
- **Tipografi:** Poppins SemiBold / Plus Jakarta Sans Medium
- **Komponen UI:** Livewire Flux (v2)
- **Layout admin:** sidebar + header, dengan pola umum `admin-page`, `admin-page-header`, `admin-table`, filter form, pagination partial, flyout modal (Flux) untuk form create/edit.
- **Referensi penuh:** `docs/pages-feature-reference.md`

---

## 8. Fitur Non-Fungsional

- **Performa:** Statistik dashboard di-cache; pipeline fetch data untuk mengurangi beban manual.
- **Keamanan:** 2FA, verifikasi email, role-based access control (RBAC), hash password, soft deletes untuk recovery data.
- **Idempotensi:** `TheaterReference` mencegah duplikasi saat fetch dari API JKT48.
- **Testing:** 24+ file test PHPUnit (feature), mencakup auth, settings, role access, dan setiap domain data.

---

## 9. Backlog & Area Perbaikan Potensial

Berdasarkan `docs/pages-feature-reference.md` dan analisis codebase, area peningkatan:

Status selesai (September 2026):

- [x] **Drag-and-drop nested sorting** untuk blok anak di dalam kolom container — via `sortNestedBlock()` + `wire:sort`, termasuk pemindahan ke atas/bawah dalam kolom dan sorting di kolom kedua.
- [x] **Validasi rekursif** untuk blok bertingkat (URL, field wajib) — via `validateBlockRecursive()`; aturan `text`/`metric`/`url`/`label`/`html` dan URL (termasuk YouTube) konsisten untuk blok top-level & nested.
- [x] Tambahan **test** untuk blok bertingkat, layout 2 kolom, URL YouTube invalid, image URL invalid, nested button, dan empty embed HTML (lihat `tests/Feature/CustomPageTest.php`).

Sisa backlog yang belum dikerjakan:

1. **HTML sanitizer** untuk blok Embed (raw HTML) — keamanan XSS.
2. **Unifikasi** markup `block-preview.blade.php` vs `render-block.blade.php`.
3. **Model `ShowTeaterCategories`** belum ada (menggunakan `DB::table` raw) — kandidat refactor ke Eloquent model.
4. Konsistensi akses DB: campuran Eloquent model & raw `DB::table` antar controller.
5. Custom color picker untuk background; test untuk mode tampilan (full/welcome) dan background color rendering.

---

## 10. Keterangan

- **Route group:** `/dashboard`, `/show-teater`, `/show-teater/categories`, `/meet-greet-events`, `/concert-events`, `/live-streaming`, `/users`, `/pages`, `/settings/*`, dan route publik `/{customPage:slug}`.
- **Console:** `app:fetch-theater-shows` (fetch jadwal dari API JKT48) + `inspire`.
- **Sumber API eksternal:** `https://jkt48.com/api/v1/schedules?lang=id&month=...&year=...&type=SHOW`.

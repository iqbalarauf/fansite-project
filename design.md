# Design System — FANSIGHT

Dokumen ini merangkum **bahasa desain, token, dan pola UI** yang dipakai aplikasi FANSIGHT saat ini, berdasarkan implementasi di codebase (bukan rencana ke depan).

**Sumber kebenaran (single source of truth):** `resources/css/app.css` + `resources/views/partials/brand-colors.blade.php`
**Komponen UI:** Livewire Flux v2 (`livewire/flux`) + Tailwind CSS v4 utilities
**Template:** Blade + Livewire Volt (single-file components)

---

## 1. Prinsip Desain

1. **Terang & ramah** — latar terang dominan, kartu putih dengan border halus, aksen warna untuk menandai status.
2. **Dua wajah, satu sistem** — **situs publik** memakai palet `slate + indigo + kuning (accent)`; **panel admin** memakai palet `zinc + blue`. Keduanya berbagi tipografi dan token merek yang sama.
3. **Dual-mode wajib** — setiap halaman mendukung **light & dark** via varian `dark:`.
4. **Konsistensi bentuk** — radius kartu besar (`rounded-2xl`/`rounded-3xl`/`[2rem]`) di publik, radius lebih kecil (`rounded-xl`/`rounded-lg`) di admin; tombol berbentuk **pill** di publik.
5. **Merek dapat dikustom** — 3 warna brand (Primer/Sekunder/Tersier) + logo/nama dari pengaturan aplikasi, diterapkan secara runtime ke seluruh UI (light & dark).

---

## 2. Tipografi

Didefinisikan di `@theme` (`app.css`) dan `@layer base`.

| Peran | Font | Bobot | Catatan |
|---|---|---|---|
| Body | **Plus Jakarta Sans** | 400–800 | `--font-sans`, dipakai di `body` |
| Heading (h1–h6) | **Poppins** | 500–800 | `--font-heading`, `letter-spacing: -0.03em`, default `font-weight: 600` |

**Pola ukuran yang dipakai:**
- Display/hero: `text-4xl sm:text-5xl lg:text-6xl` + `font-black` + `leading-tight`.
- Judul kartu/section: `text-2xl`/`text-3xl` + `font-black`.
- Body: `text-base` + `leading-7`/`leading-8`; teks sekunder `text-sm`/`text-xs` + warna `text-slate-500`/`text-slate-400`.
- Label mikro/eyebrow: `text-xs font-bold uppercase tracking-[0.18em]`–`[0.22em]`.

---

## 3. Warna

### 3.1 Token Merek (`@theme`, `app.css`)

| Token | Nilai default | Penggunaan |
|---|---|---|
| `--color-primary` | `#6C7CE8` | Warna merek utama (Primer) |
| `--color-primary-strong` | `#4E5FD4` | Hover/aksen Primer |
| `--color-accent` | `#FFD166` | Aksen Tersier (diarahkan ke `--color-primary` untuk Flux) |
| `--color-accent-soft` | `#F4C95D` | Aksen lembut |
| `--color-lavender-soft` | `#A5B4FC` | Sekunder lavender |
| `--color-bg-white` | `#FFFFFF` | Permukaan terang |
| `--color-bg-soft` | `#F5F6FA` | Permukaan lembut |
| `--color-text` | `#2E2F3E` | Teks utama |
| `--color-text-soft` | `#8A8DA6` | Teks sekunder |
| `--color-success` | `#4CAF7D` | Sukses/online |
| `--color-warning` | `#F4C95D` | Peringatan |
| `--color-error` | `#E5605C` | Error/danger |

### 3.2 Skala Zinc (di-override, `app.css`)
`--color-zinc-50 … --color-zinc-950` didefinisikan eksplisit (netral abu untuk admin).

### 3.3 Varian Flux
`--color-accent`, `--color-accent-content`, `--color-accent-foreground` diarahkan ke warna Primer (light) dan putih (dark) agar komponen Flux selaras merek.

### 3.4 Brand Palette — 3 Warna (runtime)
`resources/views/partials/brand-colors.blade.php` men-set variabel runtime dan **memetakan ulang palet Tailwind** lewat `color-mix`:

| Token Brand | Palet Tailwind yang dipetakan | Elemen yang terpengaruh (light & dark) |
|---|---|---|
| **Primer** (`brand_color`) | `indigo-*` | Tombol/tautan utama, border & ring aktif, gradient hero, badge aktif, ikon aksen |
| **Sekunder** (`brand_color_secondary`) | `violet-*` | Aksen kedua: variasi gradient & kartu sosial |
| **Tersier** (`brand_color_tertiary`) | `yellow-*` | Sorotan: nama idol di hero, tombol highlight, badge, CTA |

Karena utility Tailwind (mis. `.bg-indigo-600{background-color:var(--color-indigo-600)}`) mereferensikan variabelnya, override ini otomatis merecolor **seluruh halaman** tanpa mengubah class di view. Varian dark ikut mengikuti karena shade gelap diturunkan dari warna yang sama (`color-mix` dengan hitam).

Nilai dibaca dari `app_settings` (`brand_color`, `brand_color_secondary`, `brand_color_tertiary`) dengan fallback default bila kosong/tidak valid. Partial disertakan di `partials/head.blade.php` (admin/auth) dan `partials/site-head.blade.php` (publik).

Helper class: `.brand-primary`, `.brand-primary-bg`, `.brand-primary-strong-bg`, `.brand-accent-bg`, `.brand-soft-bg`.

### 3.5 Palet per Konteks
- **Publik:** `slate` (netral) + `indigo` (Primer) + `violet` (Sekunder) + `yellow`/`amber` (Tersier). Status: `green` (online/upcoming), `blue`, `orange`, `red`, `pink` (ulang tahun).
- **Admin:** `zinc` (netral) + `blue` (aksi/link/current menu) + `green`/`red` (sukses/danger).

---

## 4. Dark Mode

- Strategi: **class-based** — varian `dark` aktif saat `<html class="dark">` (`@custom-variant dark`).
- Toggle publik: tombol header memanggil `window.toggleTheme()`, menyimpan di `localStorage('fansite-theme')`, default mengikuti `prefers-color-scheme` (`partials/site-head.blade.php`).
- Header publik: **translucent + blur** (`bg-white/50 dark:bg-slate-900/50 backdrop-blur`).
- Panel admin default **dark** (`<html class="dark">`).
- Pedoman: hindari hitam/putih murni; gunakan `slate-950`/`zinc-900` dan teks `slate-100`/`zinc-200`.

---

## 5. Bentuk, Bayangan, dan Gerak

- **Radius:**
  - Publik: kartu `rounded-2xl` s/d `rounded-[2rem]`, gambar `rounded-xl`/`[1.5rem]`, tombol/label **pill** (`rounded-full`).
  - Admin: tabel/filter `rounded-xl`, kontrol `rounded-lg`, badge `rounded-full`.
- **Border:** halus — `border-slate-200` (publik) / `border-zinc-200` (admin), dengan `dark:border-slate-800` / `dark:border-zinc-700`.
- **Bayangan:** `shadow-sm` untuk kartu; aksen berwarna untuk elemen merek; hindari drop-shadow hitam pekat.
- **Gerak:**
  - Transisi `transition`/`transition-transform` dengan `duration-500 ease-out` untuk carousel.
  - Hover kartu: `hover:-translate-y-0.5 hover:shadow-md`, `group-hover:scale-105` untuk gambar.
  - **Hero idol name swap** (JKT48 version): teks bergantian antara dua opsi nama (default `idol_name` dan `idol_shortname JKT48`) memakai animasi skew `@keyframes hero-swap` (`.hero-swap-animate`); teks & warna tiap nama dikustomisasi dari halaman **Landing Page** (`welcome_name_1_*`/`welcome_name_2_*`).
  - Prinsip: transisi pada `transform`/`opacity`, tidak berlebihan.

---

## 6. Layout

- **Kontainer:** `mx-auto max-w-7xl px-4 sm:px-6 lg:px-8` (publik) dan `max-w-6xl` (header/footer). Admin dibungkus layout sidebar Flux.
- **Struktur publik** (`layouts/public.blade.php`): `body` = `flex min-h-dvh flex-col`, `<main class="flex-1">`, footer di akhir — **footer menempel dasar layar** saat konten pendek.
- **Hero publik:** `min-h-svh` + gambar hero (`hero_image`) dengan mode `hero_image_display` (**Fit** `bg-cover`, **Contain** `bg-contain`, **Adjustable Height** tinggi mengikuti rasio gambar, **Original** `bg-auto`), overlay gelap, konten rata kiri, aksen kuning pada nama.
- **Halaman publik non-beranda** (artikel, galeri, majalah, trivia, timeline): tanpa hero gradient; judul memakai gaya tema (`text-slate-900 dark:text-white`). **Custom Page**: hero opsional (`hero_enabled` + `hero_image`) dengan Page Title di atas hero; warna judul menyesuaikan dark/light mode & background halaman.
- **Login split-screen** (`layouts/auth/login.blade.php`): kiri form, kanan panel gambar `login_image` (fallback gradien brand) dengan logo + nama aplikasi di atas.
- **Error page** (`layouts/error.blade.php`): fullscreen, grid latar (`error-grid-bg`), ilustrasi SVG 404/500/503 (403 tanpa gambar), footer nama aplikasi dari `app_settings`.
- **Grid:** `grid` + `gap-4`/`gap-8`; dua kolom asimetris (`lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]`).
- **Responsif:** breakpoint standar Tailwind (`sm 640`, `md 768`, `lg 1024`, `xl 1280`).

---

## 7. Komponen & Pola UI

### 7.1 Situs Publik
- **Header** (`partials/site-header.blade.php`): sticky, transparan + blur, logo + nama, navigasi (Home, About ▾, Artikel ▾, Majalah, Galeri, Trivia, Data, Schedule), ikon sosial, toggle tema. Dropdown `<details>`, satu terbuka → lainnya tertutup.
- **Kartu konten:** border + `rounded-[2rem]` + `shadow-sm`, header kartu dengan judul + tautan "Lihat Semua".
- **Badge/status:** pill kecil; status Live **Online** (`bg-green-100 text-green-700`) / **Offline** (`bg-slate-200/70 text-slate-500`).
- **Carousel galeri (Welcome & YouTube):** track `flex` digeser `translateX`, `transition-transform duration-500 ease-out`, prev/next, 3 item terlihat.
- **Modal Sejarah Fansite** (`about/fansite.blade.php`): overlay fixed, panel `max-h-full` scrollable (`min-h-0 flex-1 overflow-y-auto`), tutup via tombol/backdrop/Escape.
- **Footer** (`partials/site-footer.blade.php`): `border-t`, nama aplikasi + copyright.

### 7.2 Panel Admin
Kelas utilitas bersama di `app.css` (`@layer components`):

| Kelas | Fungsi |
|---|---|
| `.admin-page` | Wrapper halaman (flex kolom, `gap-6`) |
| `.admin-page-header` | Header halaman (judul + aksi) |
| `.admin-page-actions` | Grup tombol aksi |
| `.admin-filter*` | Baris filter (search, date, select, sort, reset) |
| `.admin-table-shell`, `.admin-table*` | Tabel data |
| `.admin-action-link` / `.admin-action-danger` | Aksi Edit (biru) / Delete (merah) |

- **Form CRUD:** `flux:modal` (varian `flyout`), tombol submit `variant="primary"`, dibatasi untuk view-only.
- **Komponen Flux:** `flux:heading`, `flux:subheading`, `flux:button`, `flux:input`, `flux:textarea`, `flux:label`, `flux:badge`, `flux:modal`, `flux:sidebar`, `flux:navlist`, `flux:menu`.
- **Sidebar admin** (`layouts/app/sidebar.blade.php`): grup **Dashboard**, **Master Data**, **Content Management** (Pages, Majalah, News, Blog, Kategori, Galeri, Timeline, Trivia), **Additional Content**, dan **Configuration** (Appearance, Features Activation, Header Menu, Sheet Integration, User Management, Halaman "About"); difilter sesuai peran & feature flag.

### 7.3 Komponen Blade Kustom
| Komponen | Berkas | Kegunaan |
|---|---|---|
| Rich Text Editor | `components/rich-text-editor.blade.php` | Editor Tiptap v3 untuk News/Blog |
| Social Media Icons | `components/social-media-icons.blade.php` | Tombol ikon Instagram/X/TikTok |
| App Logo | `components/app-logo.blade.php` + `app-logo-icon.blade.php` | Logo dari pengaturan |
| Sync Choice | `components/sync-choice.blade.php` | Pemilih sumber nilai (Database/Sheet/Lewati) dengan ikon + tooltip (Sheet Integration) |
| Admin Table Toolbar | `components/admin/table-toolbar.blade.php` | Toolbar filter + aksi tabel |
| Admin Search | `components/admin-search.blade.php` | Pencarian global admin |
| Custom Page Block | `components/custom-page-block.blade.php` | Render blok page builder (prop `preview`) — markup tunggal editor & publik |
| Element Fields | `custom-pages/{text,button,image,background}-fields.blade.php` | Kontrol editor blok: heading/font size, alignment & warna tombol, sumber & mode tampilan gambar, background (termasuk transparan) |

### 7.4 Konten Artikel (`.rich-content`)
Style untuk konten editor & render publik (`app.css`): heading, paragraf, list, blockquote, `pre`/`code`, tautan, gambar, `hr` — dengan varian `dark:`.

---

## 8. Aksesibilitas

- **Kontras:** teks utama memenuhi target WCAG AA; gunakan `slate-600`+ untuk body di atas putih.
- **Fokus:** kontrol Flux memakai ring aksen (`ring-accent ring-offset-2`); tautan/aksi memakai `focus`/`hover` jelas.
- **Label ikon:** tombol ikon memakai `aria-label`.
- **Reduced motion:** animasi ringan (transform/opacity); hindari animasi tak berujung.

---

## 9. Peta Berkas Desain

```
resources/css/app.css                          # Token, tema, komponen (sumber kebenaran)
resources/views/partials/brand-colors.blade.php # Override 3 warna brand (runtime)
resources/views/layouts/public.blade.php        # Shell situs publik
resources/views/layouts/auth/login.blade.php    # Shell login split-screen
resources/views/layouts/error.blade.php         # Shell halaman error
resources/views/layouts/app/sidebar.blade.php   # Shell admin (sidebar)
resources/views/partials/site-head.blade.php    # <head> publik, favicon, tema
resources/views/partials/site-header.blade.php  # Header + dropdown publik
resources/views/partials/site-footer.blade.php  # Footer publik
resources/views/welcome.blade.php               # Halaman Welcome
resources/views/about/idol.blade.php            # Halaman profil idola
resources/views/about/fansite.blade.php         # Halaman profil fanbase
resources/views/components/                     # Komponen Blade kustom
resources/views/pages/settings/                 # Halaman akun (profile/security) + 2FA (Volt)
resources/views/pages/appearance/               # Halaman konfigurasi Appearance (standalone)
resources/views/pages/features/                 # Halaman konfigurasi Features Activation (standalone)
resources/views/pages/header-menu/              # Halaman konfigurasi Header Menu (standalone)
resources/views/pages/sheet-integration/        # Sheet Integration (perbandingan & sinkronisasi)
```

**Referensi fitur konten:** `docs/pages-feature-reference.md`
**Ringkasan produk:** `prd.md`

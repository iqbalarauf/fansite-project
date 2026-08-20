# Pages Feature Reference

Dokumen ini menjelaskan fungsionalitas page builder yang berjalan saat ini. Gunakan sebagai konteks ketika membuat prompt perbaikan, debugging, atau pengembangan lanjutan.

## Ringkasan

Fitur Pages adalah page builder berbasis Livewire untuk membuat halaman publik dengan blok konten yang dapat disusun ulang. Halaman disimpan di tabel `custom_pages` dan hanya halaman dengan status `published` yang dapat diakses publik.

Komponen utama:

- Editor: `resources/views/pages/page-builder/⚡index.blade.php`
- Preview editor: `resources/views/custom-pages/block-preview.blade.php`
- Renderer publik: `resources/views/custom-pages/render-block.blade.php`
- View halaman publik: `resources/views/custom-pages/show.blade.php`
- Model: `app/Models/CustomPage.php`
- Controller: `app/Http/Controllers/CustomPageController.php`
- Test: `tests/Feature/CustomPageTest.php`

## Akses dan Route

Route admin membutuhkan user yang sudah login dan email terverifikasi:

| Route | Nama | Fungsi |
|---|---|---|
| `GET /pages` | `pages.index` | Daftar halaman |
| `GET /pages/create` | `pages.create` | Membuat halaman baru |
| `GET /pages/{customPage}/edit` | `pages.edit` | Mengedit halaman |
| `DELETE /pages/{customPage}` | `pages.destroy` | Soft delete halaman |

Route publik menggunakan slug:

```text
GET /{customPage:slug}
```

Nama route publik adalah `custom-pages.show`.

Controller hanya menampilkan halaman publik jika:

```php
$page->status === 'published'
```

Halaman draft menghasilkan HTTP 404 ketika dibuka melalui route publik.

## Properti Halaman

Setiap page memiliki properti berikut:

| Properti | Nilai | Keterangan |
|---|---|---|
| `title` | string | Judul halaman, wajib, maksimal 120 karakter |
| `slug` | string | Slug URL. Jika kosong, dibuat dari title |
| `status` | `draft` atau `published` | Status visibilitas halaman |
| `display_mode` | `full` atau `welcome` | Mode shell halaman publik |
| `background_color` | `white`, `slate`, atau `indigo` | Warna background halaman |
| `blocks` | JSON array | Daftar blok konten halaman |

### Slug

Jika slug tidak diisi, slug dibuat menggunakan `Str::slug($title)`.

Jika slug sudah digunakan halaman lain, sistem menambahkan suffix numerik:

```text
profil-oshimen
profil-oshimen-2
profil-oshimen-3
```

## Mode Tampilan Halaman

### `full`

Halaman menggunakan layout minimal:

- Judul halaman
- Isi blok
- Tidak menampilkan header Welcome
- Tidak menampilkan footer Welcome

### `welcome`

Halaman menampilkan shell yang menyerupai halaman Welcome:

- Header dengan logo dan nama aplikasi
- Navigasi Home, About, Data, dan Schedule
- Link Instagram, X, dan TikTok jika tersedia
- Footer dengan nama aplikasi dan tahun berjalan
- Isi custom page tetap ditampilkan di bagian main content

Data header/footer diambil dari tabel:

- `app_settings`
- `about_settings`

## Background Halaman

Nilai `background_color` diterjemahkan menjadi class berikut:

| Nilai | Tampilan |
|---|---|
| `white` | Background putih |
| `slate` | Background abu-abu lembut |
| `indigo` | Background indigo sangat lembut |

Nilai default untuk page baru adalah `slate`.

## Struktur Blok

Setiap blok memiliki format umum:

```json
{
  "id": "uuid",
  "type": "text",
  "data": {}
}
```

`id` digunakan untuk key Livewire dan sorting blok top-level.

## Tipe Elemen

### Container

Container adalah blok layout yang dapat memiliki satu atau dua kolom. Container tidak hanya menjadi placeholder; elemen lain dapat dimasukkan ke dalam setiap kolom.

Contoh struktur:

```json
{
  "id": "container-id",
  "type": "container",
  "data": {
    "background": "white",
    "padding": "medium",
    "columns": [
      {
        "id": "column-1-id",
        "blocks": []
      },
      {
        "id": "column-2-id",
        "blocks": []
      }
    ]
  }
}
```

Properti container:

| Properti | Nilai | Keterangan |
|---|---|---|
| `background` | `white`, `soft`, `accent` | Background container |
| `padding` | `small`, `medium`, `large` | Spacing internal container |
| `columns` | array berisi 1 atau 2 column | Area untuk elemen nested |

Kolom kedua dibuat ketika user memilih opsi 2 kolom. Jika kembali ke 1 kolom, kolom kedua dan blok di dalamnya dipotong dari state editor.

Elemen yang dapat dimasukkan ke container:

- Text
- Image
- YouTube video
- Button
- Embed HTML

### Text

```json
{
  "id": "text-id",
  "type": "text",
  "data": {
    "text": "Isi teks"
  }
}
```

Text ditampilkan sebagai teks escaped dan mendukung line break.

### Image

```json
{
  "id": "image-id",
  "type": "image",
  "data": {
    "url": "https://example.com/image.jpg",
    "alt": "Deskripsi gambar"
  }
}
```

URL image wajib valid ketika page disimpan. Image dirender dengan `img`, object cover, rounded corner, dan batas tinggi maksimum pada halaman publik.

### Video YouTube

```json
{
  "id": "video-id",
  "type": "video",
  "data": {
    "url": "https://www.youtube.com/watch?v=VIDEO_ID",
    "title": "Judul video"
  }
}
```

User cukup memasukkan link YouTube. Renderer mendukung pola umum:

- `youtube.com/watch?v=...`
- `youtube.com/embed/...`
- `youtube.com/shorts/...`
- `youtu.be/...`

URL diubah menjadi iframe:

```text
https://www.youtube.com/embed/VIDEO_ID
```

Video tidak menggunakan tag `<video>` untuk file lokal.

### Button

```json
{
  "id": "button-id",
  "type": "button",
  "data": {
    "label": "Buka tautan",
    "url": "https://example.com"
  }
}
```

Button publik dirender sebagai link dengan `target="_blank"` dan `rel="noopener"`.

### Embed HTML

```json
{
  "id": "embed-id",
  "type": "embed",
  "data": {
    "html": "<div>Custom HTML</div>"
  }
}
```

HTML dirender menggunakan raw Blade output:

```blade
{!! $block['data']['html'] ?? '' !!}
```

Karena itu, hanya user/admin yang dipercaya yang boleh mengisi Embed HTML. Belum ada sanitizer HTML pada implementasi saat ini.

## Editor Behavior

Editor menggunakan Livewire 4 dan Flux UI.

Fungsi utama component:

| Method | Fungsi |
|---|---|
| `mount()` | Membuka page baru atau memuat page existing |
| `newPage()` | Reset editor dan membuat container awal |
| `addBlock()` | Menambahkan blok top-level |
| `addBlockToContainer()` | Menambahkan blok nested ke column container |
| `setContainerColumns()` | Mengatur container menjadi 1 atau 2 kolom |
| `selectBlock()` | Memilih blok top-level |
| `selectNestedBlock()` | Memilih blok nested untuk diedit |
| `removeBlock()` | Menghapus blok top-level |
| `sortBlock()` | Mengubah urutan blok top-level melalui drag and drop |
| `save()` | Menyimpan draft atau publish |
| `deletePage()` | Soft delete page |

### Save Draft

Tombol Save draft menyimpan page dengan:

```text
status = draft
```

Draft tersimpan di database tetapi tidak dapat dibuka secara publik.

### Publish

Tombol Publish menyimpan page dengan:

```text
status = published
```

Publish hanya berjalan jika validasi page dan blok berhasil.

## Validasi Saat Ini

Validasi umum:

- Title wajib dan maksimal 120 karakter.
- Slug harus lowercase dengan format kebab-case.
- Slug harus unik.
- Minimal satu blok top-level.
- Setiap blok harus memiliki `id`, `type`, dan `data`.

Validasi per elemen top-level:

- Text: `data.text` wajib diisi.
- Image: `data.url` wajib berupa URL valid.
- Video: `data.url` wajib berupa URL valid dan mengandung domain YouTube.
- Button: `data.label` wajib diisi dan `data.url` harus URL valid.
- Embed HTML: `data.html` wajib diisi.
- Container: tidak memiliki field wajib khusus.

## Rendering

Preview editor menggunakan:

```text
resources/views/custom-pages/block-preview.blade.php
```

Preview mendukung rendering container secara recursive, sehingga child block dapat ditampilkan di dalam column.

Halaman publik menggunakan:

```text
resources/views/custom-pages/render-block.blade.php
```

Renderer publik juga recursive untuk container dan child block.

## Database

Tabel utama adalah `custom_pages`.

Migration terkait:

- `2026_08_19_120000_create_custom_pages_table.php`
- `2026_08_20_090000_add_display_options_to_custom_pages_table.php`

Kolom utama:

```text
id
 title
slug
status
display_mode
background_color
blocks
created_at
updated_at
deleted_at
```

Model menggunakan soft delete dan cast array untuk kolom `blocks`.

## Test Saat Ini

Test feature berada di:

```text
tests/Feature/CustomPageTest.php
```

Coverage yang tersedia:

- Halaman index menampilkan daftar dan tombol create.
- User authenticated dapat membuat dan publish page.
- Slug dapat dibuat otomatis.
- Slug custom dapat digunakan.
- Page draft tidak dapat diakses publik.
- Page dapat diedit dan dihapus.
- Soft delete page berjalan.
- Halaman published dapat dirender dan menampilkan isi text.

Command test:

```bash
php artisan test --compact tests/Feature/CustomPageTest.php
```

## Keterbatasan dan Area Perbaikan

Bagian ini penting untuk prompt pengembangan berikutnya.

1. Sorting drag and drop hanya tersedia untuk blok top-level. Nested block belum memiliki sorting drag and drop.
2. Nested block belum memiliki tombol hapus individual. Saat ini nested block hanya dapat dipilih dan diedit.
3. Validasi detail terutama berjalan pada blok top-level. Struktur nested perlu validasi recursive agar URL dan field child tervalidasi konsisten.
4. Embed HTML dirender raw tanpa sanitizer. Tambahkan sanitasi atau pembatasan tag/attribute jika editor dapat digunakan oleh user yang tidak sepenuhnya dipercaya.
5. Renderer preview dan renderer publik memiliki markup yang berbeda. Perubahan tipe blok perlu diterapkan pada dua file renderer.
6. Preview editor untuk image/video kosong menampilkan placeholder, sedangkan renderer publik tidak selalu menampilkan fallback yang sama.
7. Opsi background saat ini berupa preset class, belum mendukung custom color picker atau nilai hex.
8. Container mendukung maksimal dua kolom. Nested container tidak disediakan sebagai elemen di dalam column.
9. Saat jumlah kolom dikurangi dari dua menjadi satu, data blok pada kolom kedua ikut dihapus dari state editor.
10. Belum ada test khusus untuk nested block, dua kolom, YouTube URL, Embed HTML, display mode, dan background color.
11. Header Welcome pada custom page meniru markup Welcome, tetapi bukan partial bersama. Perubahan header Welcome perlu disinkronkan manual.
12. Page title pada mode `full` dan `welcome` masih ditampilkan oleh custom page sendiri; mode `welcome` hanya menyediakan shell header/footer, bukan seluruh isi Welcome.

## Contoh Prompt Perbaikan

Contoh prompt yang dapat digunakan sebagai dasar pekerjaan berikutnya:

```text
Pada fitur Pages di fansite-project, tambahkan sorting drag-and-drop untuk child block di dalam container column. Pertahankan format JSON blocks yang sudah ada, gunakan Livewire wire:sort, tambahkan method sorting recursive pada component page-builder, dan tambahkan test feature untuk memastikan urutan nested block tersimpan setelah save.
```

Contoh lain:

```text
Perbaiki validasi page builder agar berjalan recursive untuk blok di dalam container. Validasi text, image, YouTube video, button, dan Embed HTML harus sama antara blok top-level dan nested. Tambahkan test untuk URL YouTube invalid, image URL invalid, nested button, dan empty Embed HTML.
```

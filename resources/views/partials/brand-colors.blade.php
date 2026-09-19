@php
    use App\Support\BrandPalette;
    use App\Support\SettingBag;

    $__brand = SettingBag::app();
    $__primary = BrandPalette::normalize($__brand['brand_color'] ?? null, BrandPalette::PRIMARY);
    $__secondary = BrandPalette::normalize($__brand['brand_color_secondary'] ?? null, BrandPalette::SECONDARY);
    $__tertiary = BrandPalette::normalize($__brand['brand_color_tertiary'] ?? null, BrandPalette::TERTIARY);
@endphp
<style>
    /*
     * Brand palette mapping (light & dark use the same tokens):
     * - Primer   -> palet indigo: aksi/link/border utama, tombol primer, badge aktif, gradient hero.
     * - Sekunder -> palet violet: variasi aksen kedua (gradient, kartu sosial).
     * - Tersier  -> palet yellow: sorotan/aksen (nama idol, tombol highlight, badge).
     */
    :root {
        --brand-primary: {{ $__primary }};
        --brand-secondary: {{ $__secondary }};
        --brand-tertiary: {{ $__tertiary }};
        --brand-primary-strong: color-mix(in srgb, {{ $__primary }} 84%, black);
        --brand-secondary-strong: color-mix(in srgb, {{ $__secondary }} 84%, black);
        --brand-tertiary-strong: color-mix(in srgb, {{ $__tertiary }} 84%, black);
        --brand-primary-soft: color-mix(in srgb, {{ $__primary }} 16%, white);
        --brand-secondary-soft: color-mix(in srgb, {{ $__secondary }} 16%, white);
        --brand-tertiary-soft: color-mix(in srgb, {{ $__tertiary }} 16%, white);

        --color-primary: {{ $__primary }};
        --color-primary-strong: color-mix(in srgb, {{ $__primary }} 84%, black);
        --color-secondary: {{ $__secondary }};
        --color-tertiary: {{ $__tertiary }};

        /* Primer -> indigo */
        --color-indigo-50: color-mix(in srgb, {{ $__primary }} 8%, white);
        --color-indigo-100: color-mix(in srgb, {{ $__primary }} 15%, white);
        --color-indigo-200: color-mix(in srgb, {{ $__primary }} 26%, white);
        --color-indigo-300: color-mix(in srgb, {{ $__primary }} 45%, white);
        --color-indigo-400: color-mix(in srgb, {{ $__primary }} 65%, white);
        --color-indigo-500: color-mix(in srgb, {{ $__primary }} 85%, white);
        --color-indigo-600: {{ $__primary }};
        --color-indigo-700: color-mix(in srgb, {{ $__primary }} 88%, black);
        --color-indigo-800: color-mix(in srgb, {{ $__primary }} 72%, black);
        --color-indigo-900: color-mix(in srgb, {{ $__primary }} 58%, black);
        --color-indigo-950: color-mix(in srgb, {{ $__primary }} 42%, black);

        /* Sekunder -> violet */
        --color-violet-50: color-mix(in srgb, {{ $__secondary }} 8%, white);
        --color-violet-100: color-mix(in srgb, {{ $__secondary }} 15%, white);
        --color-violet-200: color-mix(in srgb, {{ $__secondary }} 26%, white);
        --color-violet-300: color-mix(in srgb, {{ $__secondary }} 45%, white);
        --color-violet-400: color-mix(in srgb, {{ $__secondary }} 65%, white);
        --color-violet-500: color-mix(in srgb, {{ $__secondary }} 85%, white);
        --color-violet-600: {{ $__secondary }};
        --color-violet-700: color-mix(in srgb, {{ $__secondary }} 88%, black);
        --color-violet-800: color-mix(in srgb, {{ $__secondary }} 72%, black);
        --color-violet-900: color-mix(in srgb, {{ $__secondary }} 58%, black);
        --color-violet-950: color-mix(in srgb, {{ $__secondary }} 42%, black);

        /* Tersier -> yellow */
        --color-yellow-50: color-mix(in srgb, {{ $__tertiary }} 12%, white);
        --color-yellow-100: color-mix(in srgb, {{ $__tertiary }} 22%, white);
        --color-yellow-200: color-mix(in srgb, {{ $__tertiary }} 40%, white);
        --color-yellow-300: color-mix(in srgb, {{ $__tertiary }} 70%, white);
        --color-yellow-400: color-mix(in srgb, {{ $__tertiary }} 88%, white);
        --color-yellow-500: {{ $__tertiary }};
        --color-yellow-600: color-mix(in srgb, {{ $__tertiary }} 82%, black);
        --color-yellow-700: color-mix(in srgb, {{ $__tertiary }} 68%, black);
        --color-yellow-800: color-mix(in srgb, {{ $__tertiary }} 55%, black);
        --color-yellow-900: color-mix(in srgb, {{ $__tertiary }} 45%, black);
        --color-yellow-950: color-mix(in srgb, {{ $__tertiary }} 30%, black);
    }
</style>

<?php

namespace App\Http\Controllers;

use App\Models\AppSettings as AppSettingsModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppSettings extends Controller
{
    /**
     * Ambil semua data setting aplikasi dari tabel app_settings.
     */
    public function index(): array
    {
        $settings = AppSettingsModel::query()
            ->pluck('value', 'key')
            ->all();

        $defaults = [
            'app_name' => 'FANSIGHT DEV',
            'sidebar_name' => 'FANSIGHT DEV',
            'desc_app' => 'Welcome to FANSIGHT - your ultimate destination for all things related to your favorite idol!',
            'app_logo' => null,
            'hero_image' => null,
        ];

        return [
            'settings' => array_merge($defaults, $settings),
        ];
    }

    /**
     * Ambil satu setting berdasarkan key.
     */
    public function show(string $key): ?string
    {
        return AppSettingsModel::query()->where('key', $key)->value('value');
    }

    /**
     * Simpan atau update data konfigurasi aplikasi.
     */
    public function update(Request $request): RedirectResponse
    {
        $keys = [
            'app_name',
            'sidebar_name',
            'desc_app',
            'app_logo',
            'hero_image',
        ];

        foreach ($keys as $key) {
            if (! $request->has($key)) {
                continue;
            }

            AppSettingsModel::query()
                ->updateOrCreate(
                    ['key' => $key],
                    ['value' => $request->input($key), 'updated_at' => now()],
                );
        }

        return redirect()->back()->with('success', 'App settings updated successfully.');
    }

    /**
     * Alternatif jika ingin menampilkan data ke view.
     */
    public function view(): View
    {
        return view('app-settings.index', $this->index());
    }
}

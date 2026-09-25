<?php

namespace App\Http\Controllers;

use App\Http\Requests\MerchandiseProductRequest;
use App\Models\MerchandiseProduct;
use App\Support\ImageOptimizer;
use App\Support\ListingQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MerchandiseController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', MerchandiseProduct::class);

        $filters = ListingQuery::from($request, ['name', 'price', 'created_at'], 'created_at');

        $products = MerchandiseProduct::query()
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $query->where(function ($nested) use ($filters): void {
                    $nested->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('slug', 'like', "%{$filters['search']}%");
                });
            })
            ->orderBy($filters['sort_by'], $filters['sort_dir'])
            ->paginate($filters['per_page'])
            ->withQueryString();

        return view('merchandise.index', [
            'products' => $products,
            'filters' => $filters,
        ]);
    }

    public function store(MerchandiseProductRequest $request): RedirectResponse
    {
        Gate::authorize('create', MerchandiseProduct::class);

        $product = MerchandiseProduct::query()->create($this->payload($request));

        $product->update(['slug' => $this->uniqueSlug($product)]);

        return redirect()->route('merchandise.admin.index')
            ->with('success', "Produk \"{$product->name}\" berhasil ditambahkan.");
    }

    public function update(MerchandiseProductRequest $request, MerchandiseProduct $merchandiseProduct): RedirectResponse
    {
        Gate::authorize('update', $merchandiseProduct);

        $payload = $this->payload($request, $merchandiseProduct);

        if (filled($request->validated('slug'))) {
            $payload['slug'] = $this->uniqueSlug($merchandiseProduct);
        }

        $merchandiseProduct->update($payload);

        return redirect()->route('merchandise.admin.index')
            ->with('success', 'Produk berhasil diupdate.');
    }

    public function destroy(MerchandiseProduct $merchandiseProduct): RedirectResponse
    {
        Gate::authorize('delete', $merchandiseProduct);

        foreach ($merchandiseProduct->imagePaths() as $path) {
            Storage::disk('public')->delete($path);
        }

        $merchandiseProduct->delete();

        return redirect()->route('merchandise.admin.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(MerchandiseProductRequest $request, ?MerchandiseProduct $product = null): array
    {
        $validated = $request->validated();
        $images = $product?->imagePaths() ?? [];

        foreach ($request->file('images', []) as $upload) {
            $images[] = ImageOptimizer::store($upload, 'merchandise');
        }

        return [
            'name' => $validated['name'],
            'slug' => filled($validated['slug'] ?? null) ? $validated['slug'] : Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'] ?? null,
            'shop_url' => $validated['shop_url'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'images' => array_values($images),
        ];
    }

    private function uniqueSlug(MerchandiseProduct $product): string
    {
        $base = Str::slug($product->slug ?: $product->name);
        $slug = $base;
        $suffix = 2;

        while (MerchandiseProduct::query()->where('slug', $slug)->whereKeyNot($product->getKey())->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}

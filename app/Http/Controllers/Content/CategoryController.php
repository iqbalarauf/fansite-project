<?php

namespace App\Http\Controllers\Content;

use App\Enums\ContentSection;
use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Support\ListingQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $type = ContentSection::tryFrom((string) $request->string('type', ContentSection::News->value))
            ?? ContentSection::News;

        $filters = ListingQuery::from($request, ['name', 'created_at'], 'name');

        $categories = Category::query()
            ->ofType($type)
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $query->where(function ($nested) use ($filters): void {
                    $nested->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('slug', 'like', "%{$filters['search']}%");
                });
            })
            ->orderBy($filters['sort_by'], $filters['sort_dir'])
            ->paginate($filters['per_page'])
            ->withQueryString();

        return view('content.categories.index', [
            'type' => $type,
            'categories' => $categories,
            'filters' => $filters,
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::query()->create($request->validated());

        return redirect()->route('content.categories.index', ['type' => $request->validated('type')])
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(CategoryRequest $request, int $id): RedirectResponse
    {
        Category::query()->findOrFail($id)->update($request->validated());

        return redirect()->route('content.categories.index', ['type' => $request->validated('type')])
            ->with('success', 'Kategori berhasil diupdate.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $category = Category::query()->findOrFail($id);
        $type = $category->type;
        $category->delete();

        return redirect()->route('content.categories.index', ['type' => $type])
            ->with('success', 'Kategori berhasil dihapus.');
    }
}

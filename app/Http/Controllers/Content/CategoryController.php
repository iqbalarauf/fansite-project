<?php

namespace App\Http\Controllers\Content;

use App\Enums\ContentSection;
use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $type = ContentSection::tryFrom((string) $request->string('type', ContentSection::News->value))
            ?? ContentSection::News;

        return view('content.categories.index', [
            'type' => $type,
            'categories' => Category::query()->ofType($type)->orderBy('name')->get(),
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

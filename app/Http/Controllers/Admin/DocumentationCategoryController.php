<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentationCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DocumentationCategoryController extends Controller
{
    public function index(): View
    {
        $categories = DocumentationCategory::ordered()
            ->withTranslations()
            ->withCount('articles')
            ->get();

        return view('admin.documentation.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.documentation.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:100|unique:documentation_categories,slug',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'translations' => 'required|array',
            'translations.en.name' => 'required|string|max:255',
            'translations.en.description' => 'nullable|string',
            'translations.sw.name' => 'required|string|max:255',
            'translations.sw.description' => 'nullable|string',
        ]);

        $category = DocumentationCategory::create([
            'slug' => Str::slug($validated['slug']),
            'icon' => $validated['icon'] ?? 'bi-folder',
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        foreach (['en', 'sw'] as $locale) {
            $category->translations()->create([
                'locale' => $locale,
                'name' => $validated['translations'][$locale]['name'],
                'description' => $validated['translations'][$locale]['description'] ?? null,
            ]);
        }

        return redirect()->route('admin.documentation.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(DocumentationCategory $category): View
    {
        $category->load('translations');
        return view('admin.documentation.categories.edit', compact('category'));
    }

    public function update(Request $request, DocumentationCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:100|unique:documentation_categories,slug,' . $category->id,
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'translations' => 'required|array',
            'translations.en.name' => 'required|string|max:255',
            'translations.en.description' => 'nullable|string',
            'translations.sw.name' => 'required|string|max:255',
            'translations.sw.description' => 'nullable|string',
        ]);

        $category->update([
            'slug' => Str::slug($validated['slug']),
            'icon' => $validated['icon'] ?? 'bi-folder',
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        foreach (['en', 'sw'] as $locale) {
            $category->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'name' => $validated['translations'][$locale]['name'],
                    'description' => $validated['translations'][$locale]['description'] ?? null,
                ]
            );
        }

        return redirect()->route('admin.documentation.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(DocumentationCategory $category): RedirectResponse
    {
        if ($category->articles()->exists()) {
            return back()->with('error', 'Cannot delete category with articles. Delete articles first.');
        }

        $category->translations()->delete();
        $category->delete();

        return redirect()->route('admin.documentation.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}

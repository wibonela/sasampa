<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentationArticle;
use App\Models\DocumentationCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DocumentationArticleController extends Controller
{
    public function index(Request $request): View
    {
        $query = DocumentationArticle::with(['category', 'translations', 'createdBy'])
            ->ordered();

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('status')) {
            if ($request->status === 'published') {
                $query->published();
            } elseif ($request->status === 'draft') {
                $query->where('is_published', false);
            }
        }

        $articles = $query->paginate(20);

        $categories = DocumentationCategory::ordered()->withTranslations()->get();

        return view('admin.documentation.articles.index', compact('articles', 'categories'));
    }

    public function create(): View
    {
        $categories = DocumentationCategory::active()->ordered()->withTranslations()->get();

        return view('admin.documentation.articles.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:documentation_categories,id',
            'slug' => 'required|string|max:100|unique:documentation_articles,slug',
            'sort_order' => 'nullable|integer|min:0',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'translations' => 'required|array',
            'translations.en.title' => 'required|string|max:255',
            'translations.en.excerpt' => 'nullable|string|max:500',
            'translations.en.content' => 'required|string',
            'translations.en.meta_description' => 'nullable|string|max:160',
            'translations.sw.title' => 'required|string|max:255',
            'translations.sw.excerpt' => 'nullable|string|max:500',
            'translations.sw.content' => 'required|string',
            'translations.sw.meta_description' => 'nullable|string|max:160',
        ]);

        $article = DocumentationArticle::create([
            'category_id' => $validated['category_id'],
            'slug' => Str::slug($validated['slug']),
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_published' => $request->boolean('is_published', false),
            'is_featured' => $request->boolean('is_featured', false),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        foreach (['en', 'sw'] as $locale) {
            $article->translations()->create([
                'locale' => $locale,
                'title' => $validated['translations'][$locale]['title'],
                'excerpt' => $validated['translations'][$locale]['excerpt'] ?? null,
                'content' => $validated['translations'][$locale]['content'],
                'meta_description' => $validated['translations'][$locale]['meta_description'] ?? null,
            ]);
        }

        return redirect()->route('admin.documentation.articles.index')
            ->with('success', 'Article created successfully.');
    }

    public function edit(DocumentationArticle $article): View
    {
        $article->load('translations');
        $categories = DocumentationCategory::active()->ordered()->withTranslations()->get();

        return view('admin.documentation.articles.edit', compact('article', 'categories'));
    }

    public function update(Request $request, DocumentationArticle $article): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:documentation_categories,id',
            'slug' => 'required|string|max:100|unique:documentation_articles,slug,' . $article->id,
            'sort_order' => 'nullable|integer|min:0',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'translations' => 'required|array',
            'translations.en.title' => 'required|string|max:255',
            'translations.en.excerpt' => 'nullable|string|max:500',
            'translations.en.content' => 'required|string',
            'translations.en.meta_description' => 'nullable|string|max:160',
            'translations.sw.title' => 'required|string|max:255',
            'translations.sw.excerpt' => 'nullable|string|max:500',
            'translations.sw.content' => 'required|string',
            'translations.sw.meta_description' => 'nullable|string|max:160',
        ]);

        $article->update([
            'category_id' => $validated['category_id'],
            'slug' => Str::slug($validated['slug']),
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_published' => $request->boolean('is_published', false),
            'is_featured' => $request->boolean('is_featured', false),
            'updated_by' => auth()->id(),
        ]);

        foreach (['en', 'sw'] as $locale) {
            $article->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title' => $validated['translations'][$locale]['title'],
                    'excerpt' => $validated['translations'][$locale]['excerpt'] ?? null,
                    'content' => $validated['translations'][$locale]['content'],
                    'meta_description' => $validated['translations'][$locale]['meta_description'] ?? null,
                ]
            );
        }

        return redirect()->route('admin.documentation.articles.index')
            ->with('success', 'Article updated successfully.');
    }

    public function destroy(DocumentationArticle $article): RedirectResponse
    {
        $article->translations()->delete();
        $article->delete();

        return redirect()->route('admin.documentation.articles.index')
            ->with('success', 'Article deleted successfully.');
    }

    public function togglePublish(DocumentationArticle $article): RedirectResponse
    {
        $article->update([
            'is_published' => !$article->is_published,
            'updated_by' => auth()->id(),
        ]);

        $status = $article->is_published ? 'published' : 'unpublished';

        return back()->with('success', "Article {$status} successfully.");
    }
}

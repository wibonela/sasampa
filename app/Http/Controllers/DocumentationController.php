<?php

namespace App\Http\Controllers;

use App\Models\DocumentationArticle;
use App\Models\DocumentationCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentationController extends Controller
{
    public function index(): View
    {
        $categories = DocumentationCategory::active()
            ->ordered()
            ->withTranslations()
            ->with(['articles' => function ($query) {
                $query->published()->ordered()->limit(3);
            }])
            ->get();

        $featuredArticles = DocumentationArticle::published()
            ->featured()
            ->with(['category', 'translations'])
            ->limit(6)
            ->get();

        return view('docs.index', compact('categories', 'featuredArticles'));
    }

    public function category(string $categorySlug): View
    {
        $category = DocumentationCategory::active()
            ->where('slug', $categorySlug)
            ->withTranslations()
            ->firstOrFail();

        $articles = $category->articles()
            ->published()
            ->ordered()
            ->with('translations')
            ->get();

        $categories = DocumentationCategory::active()
            ->ordered()
            ->withTranslations()
            ->get();

        return view('docs.category', compact('category', 'articles', 'categories'));
    }

    public function show(string $categorySlug, string $articleSlug): View
    {
        $category = DocumentationCategory::active()
            ->where('slug', $categorySlug)
            ->withTranslations()
            ->firstOrFail();

        $article = $category->articles()
            ->published()
            ->where('slug', $articleSlug)
            ->with('translations')
            ->firstOrFail();

        $categories = DocumentationCategory::active()
            ->ordered()
            ->withTranslations()
            ->get();

        $previousArticle = $article->getPreviousArticle();
        $nextArticle = $article->getNextArticle();

        return view('docs.show', compact('category', 'article', 'categories', 'previousArticle', 'nextArticle'));
    }

    public function search(Request $request): View
    {
        $query = $request->input('q', '');

        $articles = collect();
        if (strlen($query) >= 2) {
            $articles = DocumentationArticle::published()
                ->search($query)
                ->with(['category', 'translations'])
                ->limit(20)
                ->get();
        }

        $categories = DocumentationCategory::active()
            ->ordered()
            ->withTranslations()
            ->get();

        return view('docs.search', compact('query', 'articles', 'categories'));
    }
}

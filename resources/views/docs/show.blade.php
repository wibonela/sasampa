<x-docs-layout :title="$article->title" :categories="$categories">
    <nav style="margin-bottom: 1rem;">
        <a href="{{ route('docs.category', $category->slug) }}" style="color: var(--apple-blue); text-decoration: none;">
            &larr; {{ $category->name }}
        </a>
    </nav>

    <article class="docs-article">
        <h1>{{ $article->title }}</h1>

        @if($article->excerpt)
            <p style="font-size: 1.1rem; color: #86868b; margin-bottom: 1.5rem;">{{ $article->excerpt }}</p>
        @endif

        <div class="article-content">
            {!! $article->parsed_content !!}
        </div>

        <!-- Article Navigation -->
        <div class="docs-article-nav">
            <div>
                @if($previousArticle)
                    <a href="{{ route('docs.show', [$previousArticle->category->slug, $previousArticle->slug]) }}">
                        <i class="bi bi-arrow-left"></i>
                        {{ $previousArticle->title }}
                    </a>
                @endif
            </div>
            <div>
                @if($nextArticle)
                    <a href="{{ route('docs.show', [$nextArticle->category->slug, $nextArticle->slug]) }}">
                        {{ $nextArticle->title }}
                        <i class="bi bi-arrow-right"></i>
                    </a>
                @endif
            </div>
        </div>
    </article>

    <div style="margin-top: 2rem; padding: 1.5rem; background: #f5f5f7; border-radius: 10px; text-align: center;">
        <p style="margin: 0; color: #86868b;">
            {{ app()->getLocale() === 'sw' ? 'Je, makala hii ilikuwa na msaada?' : 'Was this article helpful?' }}
        </p>
        <div style="margin-top: 0.75rem;">
            <button class="btn btn-outline-primary btn-sm me-2">
                <i class="bi bi-hand-thumbs-up me-1"></i>{{ app()->getLocale() === 'sw' ? 'Ndiyo' : 'Yes' }}
            </button>
            <button class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-hand-thumbs-down me-1"></i>{{ app()->getLocale() === 'sw' ? 'Hapana' : 'No' }}
            </button>
        </div>
    </div>
</x-docs-layout>

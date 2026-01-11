<x-docs-layout :title="app()->getLocale() === 'sw' ? 'Matokeo ya Utafutaji' : 'Search Results'" :categories="$categories">
    <div class="docs-article">
        <h1>
            {{ app()->getLocale() === 'sw' ? 'Matokeo ya Utafutaji' : 'Search Results' }}
        </h1>

        @if($query)
            <p style="color: #86868b; margin-bottom: 1.5rem;">
                {{ app()->getLocale() === 'sw' ? 'Matokeo kwa' : 'Results for' }}: <strong>"{{ $query }}"</strong>
                @if($articles->count() > 0)
                    ({{ $articles->count() }} {{ app()->getLocale() === 'sw' ? 'makala zimepatikana' : 'articles found' }})
                @endif
            </p>

            @if($articles->count() > 0)
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    @foreach($articles as $article)
                        <a href="{{ route('docs.show', [$article->category->slug, $article->slug]) }}"
                           style="display: block; padding: 1.25rem; border: 1px solid #e5e5e7; border-radius: 10px; text-decoration: none;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <span style="background: rgba(0, 122, 255, 0.1); color: var(--apple-blue); font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 4px;">
                                    {{ $article->category->name }}
                                </span>
                            </div>
                            <h3 style="color: #1d1d1f; margin: 0 0 0.5rem 0; font-size: 1.1rem;">{{ $article->title }}</h3>
                            @if($article->excerpt)
                                <p style="color: #86868b; margin: 0; font-size: 0.9rem;">{{ Str::limit($article->excerpt, 150) }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>
            @else
                <div style="text-align: center; padding: 3rem; color: #86868b;">
                    <i class="bi bi-search" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                    <p>{{ app()->getLocale() === 'sw' ? 'Hakuna makala yenye maneno hayo.' : 'No articles found matching your search.' }}</p>
                    <a href="{{ route('docs.index') }}" class="btn btn-primary btn-sm mt-2">
                        {{ app()->getLocale() === 'sw' ? 'Rudi Nyumbani' : 'Back to Home' }}
                    </a>
                </div>
            @endif
        @else
            <div style="text-align: center; padding: 3rem; color: #86868b;">
                <i class="bi bi-search" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                <p>{{ app()->getLocale() === 'sw' ? 'Andika maneno ya kutafuta.' : 'Enter a search term to find articles.' }}</p>
            </div>
        @endif
    </div>
</x-docs-layout>

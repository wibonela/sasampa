<x-docs-layout :title="$category->name" :categories="$categories">
    <nav style="margin-bottom: 1rem;">
        <a href="{{ route('docs.index') }}" style="color: var(--apple-blue); text-decoration: none;">
            &larr; {{ app()->getLocale() === 'sw' ? 'Nyuma' : 'Back' }}
        </a>
    </nav>

    <div class="docs-article">
        <h1>
            <i class="{{ $category->icon }}" style="color: var(--apple-blue); margin-right: 0.5rem;"></i>
            {{ $category->name }}
        </h1>

        @if($category->description)
            <p style="font-size: 1.1rem; color: #86868b;">{{ $category->description }}</p>
        @endif

        <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid #e5e5e7;">

        @if($articles->count() > 0)
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                @foreach($articles as $article)
                    <a href="{{ route('docs.show', [$category->slug, $article->slug]) }}"
                       style="display: block; padding: 1.25rem; border: 1px solid #e5e5e7; border-radius: 10px; text-decoration: none; transition: border-color 0.2s, box-shadow 0.2s;">
                        <h3 style="color: #1d1d1f; margin: 0 0 0.5rem 0; font-size: 1.1rem;">{{ $article->title }}</h3>
                        @if($article->excerpt)
                            <p style="color: #86868b; margin: 0; font-size: 0.9rem;">{{ $article->excerpt }}</p>
                        @endif
                    </a>
                @endforeach
            </div>
        @else
            <div style="text-align: center; padding: 2rem; color: #86868b;">
                <i class="bi bi-file-text" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                <p>{{ app()->getLocale() === 'sw' ? 'Hakuna makala katika kundi hili.' : 'No articles in this category yet.' }}</p>
            </div>
        @endif
    </div>
</x-docs-layout>

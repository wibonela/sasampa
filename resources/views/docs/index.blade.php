<x-docs-layout :title="app()->getLocale() === 'sw' ? 'Mwongozo' : 'Documentation'" :categories="$categories">
    <div class="docs-home">
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">
            {{ app()->getLocale() === 'sw' ? 'Karibu kwenye Mwongozo wa Sasampa' : 'Welcome to Sasampa Documentation' }}
        </h1>
        <p style="font-size: 1.1rem; color: #86868b; margin-bottom: 2rem;">
            {{ app()->getLocale() === 'sw' ? 'Pata msaada na mwongozo wa kutumia mfumo wa Sasampa POS.' : 'Find help and guides on using the Sasampa POS system.' }}
        </p>

        <!-- Featured Articles -->
        @if($featuredArticles->count() > 0)
            <div style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.25rem; margin-bottom: 1rem;">
                    {{ app()->getLocale() === 'sw' ? 'Makala Muhimu' : 'Featured Articles' }}
                </h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem;">
                    @foreach($featuredArticles as $article)
                        <a href="{{ route('docs.show', [$article->category->slug, $article->slug]) }}"
                           style="display: block; background: white; padding: 1.25rem; border-radius: 10px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: transform 0.2s;">
                            <h4 style="color: #1d1d1f; margin: 0 0 0.5rem 0; font-size: 1rem;">{{ $article->title }}</h4>
                            <p style="color: #86868b; margin: 0; font-size: 0.85rem;">{{ Str::limit($article->excerpt, 80) }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Categories -->
        <h2 style="font-size: 1.25rem; margin-bottom: 1rem;">
            {{ app()->getLocale() === 'sw' ? 'Makundi' : 'Categories' }}
        </h2>

        @foreach($categories as $category)
            <div class="category-card">
                <h3>
                    <i class="{{ $category->icon }}" style="color: var(--apple-blue);"></i>
                    <a href="{{ route('docs.category', $category->slug) }}">{{ $category->name }}</a>
                </h3>
                @if($category->description)
                    <p>{{ $category->description }}</p>
                @endif

                @if($category->articles && $category->articles->count() > 0)
                    <ul class="article-list">
                        @foreach($category->articles->take(3) as $article)
                            <li>
                                <a href="{{ route('docs.show', [$category->slug, $article->slug]) }}">
                                    {{ $article->title }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    @if($category->articles->count() > 3)
                        <a href="{{ route('docs.category', $category->slug) }}" style="display: inline-block; margin-top: 0.5rem; font-size: 0.9rem;">
                            {{ app()->getLocale() === 'sw' ? 'Tazama zote' : 'View all' }} &rarr;
                        </a>
                    @endif
                @endif
            </div>
        @endforeach

        @if($categories->count() === 0)
            <div style="text-align: center; padding: 3rem; color: #86868b;">
                <i class="bi bi-book" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                <p>{{ app()->getLocale() === 'sw' ? 'Hakuna mwongozo kwa sasa.' : 'No documentation available yet.' }}</p>
            </div>
        @endif
    </div>
</x-docs-layout>

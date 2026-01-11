<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Documentation' }} - {{ config('app.name', 'Sasampa POS') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/favicon.svg">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --docs-sidebar-width: 280px;
        }

        body {
            background: #f5f5f7;
        }

        .docs-header {
            background: white;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            padding: 1rem 2rem;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .docs-logo {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1d1d1f;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .docs-logo:hover {
            color: var(--apple-blue);
        }

        .docs-nav-links {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .docs-nav-links a {
            color: #1d1d1f;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .docs-nav-links a:hover {
            color: var(--apple-blue);
        }

        .language-switcher {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .language-switcher a {
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            font-size: 0.85rem;
            text-decoration: none;
            color: #666;
            background: #f5f5f7;
            transition: all 0.2s;
        }

        .language-switcher a:hover {
            background: #e5e5e7;
        }

        .language-switcher a.active {
            background: var(--apple-blue);
            color: white;
        }

        .docs-container {
            display: flex;
            margin-top: 60px;
            min-height: calc(100vh - 60px);
        }

        .docs-sidebar {
            width: var(--docs-sidebar-width);
            background: white;
            border-right: 1px solid rgba(0,0,0,0.1);
            padding: 1.5rem;
            position: fixed;
            top: 60px;
            bottom: 0;
            overflow-y: auto;
        }

        .docs-sidebar-section {
            margin-bottom: 1.5rem;
        }

        .docs-sidebar-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: #86868b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.75rem;
        }

        .docs-sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .docs-sidebar-nav li a {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            color: #1d1d1f;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .docs-sidebar-nav li a:hover {
            background: #f5f5f7;
        }

        .docs-sidebar-nav li a.active {
            background: rgba(0, 122, 255, 0.1);
            color: var(--apple-blue);
        }

        .docs-content {
            flex: 1;
            margin-left: var(--docs-sidebar-width);
            padding: 2rem 3rem;
            max-width: 900px;
        }

        .docs-search {
            margin-bottom: 1.5rem;
        }

        .docs-search input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e5e5e7;
            border-radius: 10px;
            font-size: 0.95rem;
            background: white;
        }

        .docs-search input:focus {
            outline: none;
            border-color: var(--apple-blue);
            box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
        }

        .docs-article {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .docs-article h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #1d1d1f;
        }

        .docs-article h2 {
            font-size: 1.5rem;
            margin-top: 2rem;
            margin-bottom: 1rem;
            color: #1d1d1f;
        }

        .docs-article h3 {
            font-size: 1.25rem;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            color: #1d1d1f;
        }

        .docs-article p {
            line-height: 1.7;
            color: #424245;
            margin-bottom: 1rem;
        }

        .docs-article ul, .docs-article ol {
            margin-bottom: 1rem;
            padding-left: 1.5rem;
        }

        .docs-article li {
            margin-bottom: 0.5rem;
            line-height: 1.6;
        }

        .docs-article code {
            background: #f5f5f7;
            padding: 0.2rem 0.4rem;
            border-radius: 4px;
            font-size: 0.9em;
        }

        .docs-article pre {
            background: #1d1d1f;
            color: #f5f5f7;
            padding: 1rem;
            border-radius: 8px;
            overflow-x: auto;
            margin: 1rem 0;
        }

        .docs-article pre code {
            background: none;
            padding: 0;
            color: inherit;
        }

        .docs-article-nav {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e5e7;
        }

        .docs-article-nav a {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--apple-blue);
            text-decoration: none;
            font-size: 0.95rem;
        }

        .docs-article-nav a:hover {
            text-decoration: underline;
        }

        .category-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .category-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .category-card h3 {
            margin: 0 0 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .category-card h3 a {
            color: #1d1d1f;
            text-decoration: none;
        }

        .category-card h3 a:hover {
            color: var(--apple-blue);
        }

        .category-card p {
            color: #86868b;
            margin: 0;
            font-size: 0.9rem;
        }

        .article-list {
            list-style: none;
            padding: 0;
            margin: 1rem 0 0 0;
        }

        .article-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #f5f5f7;
        }

        .article-list li:last-child {
            border-bottom: none;
        }

        .article-list a {
            color: var(--apple-blue);
            text-decoration: none;
        }

        .article-list a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .docs-sidebar {
                display: none;
            }

            .docs-content {
                margin-left: 0;
                padding: 1rem;
            }

            .docs-header {
                padding: 1rem;
            }

            .docs-nav-links {
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="docs-header">
        <a href="{{ route('docs.index') }}" class="docs-logo">
            <i class="bi bi-book"></i>
            {{ app()->getLocale() === 'sw' ? 'Mwongozo' : 'Documentation' }}
        </a>

        <div class="docs-nav-links">
            <div class="language-switcher">
                <a href="{{ route('language.switch', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
                <a href="{{ route('language.switch', 'sw') }}" class="{{ app()->getLocale() === 'sw' ? 'active' : '' }}">SW</a>
            </div>
            @auth
                <a href="{{ route('dashboard') }}">
                    <i class="bi bi-grid me-1"></i>Dashboard
                </a>
            @else
                <a href="{{ route('login') }}">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Login
                </a>
            @endauth
        </div>
    </header>

    <div class="docs-container">
        <!-- Sidebar -->
        <aside class="docs-sidebar">
            <div class="docs-search">
                <form action="{{ route('docs.search') }}" method="GET">
                    <input type="text" name="q" placeholder="{{ app()->getLocale() === 'sw' ? 'Tafuta...' : 'Search...' }}" value="{{ request('q') }}">
                </form>
            </div>

            @if(isset($categories))
                @foreach($categories as $cat)
                    <div class="docs-sidebar-section">
                        <div class="docs-sidebar-title">
                            <i class="{{ $cat->icon }}"></i>
                            {{ $cat->name }}
                        </div>
                        @if($cat->articles && $cat->articles->count() > 0)
                            <ul class="docs-sidebar-nav">
                                @foreach($cat->articles as $art)
                                    <li>
                                        <a href="{{ route('docs.show', [$cat->slug, $art->slug]) }}"
                                           class="{{ isset($article) && $article->id === $art->id ? 'active' : '' }}">
                                            {{ $art->title }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            @endif
        </aside>

        <!-- Main Content -->
        <main class="docs-content">
            {{ $slot }}
        </main>
    </div>
</body>
</html>

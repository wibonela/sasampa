<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class DocumentationArticle extends Model
{
    protected $fillable = [
        'category_id',
        'slug',
        'sort_order',
        'is_published',
        'is_featured',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentationCategory::class, 'category_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(DocumentationArticleTranslation::class, 'article_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Translation Helpers
    |--------------------------------------------------------------------------
    */

    public function translation(?string $locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', 'en');
    }

    public function getTitleAttribute(): string
    {
        return $this->translation()?->title ?? $this->slug;
    }

    public function getTitle(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $translation = $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', 'en');
        return $translation?->title ?? $this->slug;
    }

    public function getExcerptAttribute(): ?string
    {
        return $this->translation()?->excerpt;
    }

    public function getExcerpt(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $translation = $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', 'en');
        return $translation?->excerpt;
    }

    public function getContentAttribute(): string
    {
        return $this->translation()?->content ?? '';
    }

    public function getContent(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $translation = $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', 'en');
        return $translation?->content ?? '';
    }

    public function getMetaDescriptionAttribute(): ?string
    {
        return $this->translation()?->meta_description;
    }

    /**
     * Get parsed HTML content from markdown
     */
    public function getParsedContentAttribute(): string
    {
        $content = $this->content;

        // Simple markdown to HTML conversion
        // For production, use league/commonmark
        $content = Str::markdown($content);

        return $content;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch($query, string $term, ?string $locale = null)
    {
        return $query->whereHas('translations', function ($q) use ($term, $locale) {
            $q->where(function ($sq) use ($term) {
                $sq->where('title', 'like', "%{$term}%")
                   ->orWhere('content', 'like', "%{$term}%")
                   ->orWhere('excerpt', 'like', "%{$term}%");
            });

            if ($locale) {
                $q->where('locale', $locale);
            }
        });
    }

    public function scopeWithTranslations($query)
    {
        return $query->with('translations');
    }

    /*
    |--------------------------------------------------------------------------
    | Navigation Helpers
    |--------------------------------------------------------------------------
    */

    public function getPreviousArticle()
    {
        return static::published()
            ->where('category_id', $this->category_id)
            ->where('sort_order', '<', $this->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();
    }

    public function getNextArticle()
    {
        return static::published()
            ->where('category_id', $this->category_id)
            ->where('sort_order', '>', $this->sort_order)
            ->orderBy('sort_order')
            ->first();
    }
}

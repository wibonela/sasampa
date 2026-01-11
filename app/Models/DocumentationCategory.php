<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentationCategory extends Model
{
    protected $fillable = [
        'slug',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function translations(): HasMany
    {
        return $this->hasMany(DocumentationCategoryTranslation::class, 'category_id');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(DocumentationArticle::class, 'category_id');
    }

    public function publishedArticles(): HasMany
    {
        return $this->articles()->where('is_published', true)->orderBy('sort_order');
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

    public function getNameAttribute(): string
    {
        return $this->translation()?->name ?? $this->slug;
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->translation()?->description;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeWithTranslations($query)
    {
        return $query->with('translations');
    }
}

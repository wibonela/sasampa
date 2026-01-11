<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentationArticleTranslation extends Model
{
    protected $fillable = [
        'article_id',
        'locale',
        'title',
        'excerpt',
        'content',
        'meta_description',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(DocumentationArticle::class, 'article_id');
    }
}

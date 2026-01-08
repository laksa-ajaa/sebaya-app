<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = ['title', 'slug', 'content'];

    protected static function booted()
    {
        static::creating(function ($article) {
            if (!$article->slug) {
                $article->slug = \Illuminate\Support\Str::slug($article->title);
            }
        });

        static::updating(function ($article) {
            if ($article->isDirty('title') && !$article->isDirty('slug')) {
                $article->slug = \Illuminate\Support\Str::slug($article->title);
            }
        });
    }
}

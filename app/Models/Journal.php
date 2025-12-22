<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Journal extends Model
{
    protected $fillable = [
        'user_id',
        'mood_check_id',
        'title',
        'content',
        'type',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function moodCheck(): BelongsTo
    {
        return $this->belongsTo(MoodCheck::class);
    }

    public function todoItems(): HasMany
    {
        return $this->hasMany(TodoItem::class)->orderBy('order');
    }

    public function habits(): HasMany
    {
        return $this->hasMany(Habit::class);
    }
}

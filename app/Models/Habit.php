<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Habit extends Model
{
    protected $fillable = [
        'journal_id',
        'name',
        'description',
        'is_completed_today',
        'streak',
    ];

    protected function casts(): array
    {
        return [
            'is_completed_today' => 'boolean',
            'streak' => 'integer',
        ];
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }
}

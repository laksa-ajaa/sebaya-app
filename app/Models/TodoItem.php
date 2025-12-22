<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TodoItem extends Model
{
    protected $fillable = [
        'journal_id',
        'text',
        'is_completed',
        'reminder_time',
        'reminder_label',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'reminder_time' => 'datetime',
        ];
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }
}

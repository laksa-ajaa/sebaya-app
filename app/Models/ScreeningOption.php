<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScreeningOption extends Model
{
    protected $fillable = [
        'screening_question_id',
        'label',
        'value',
        'order',
    ];

    protected $casts = [
        'value' => 'integer',
        'order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function question()
    {
        return $this->belongsTo(ScreeningQuestion::class, 'screening_question_id');
    }

    public function answers()
    {
        return $this->hasMany(ScreeningAnswer::class);
    }
}

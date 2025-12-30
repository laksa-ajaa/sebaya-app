<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScreeningAnswer extends Model
{
    protected $fillable = [
        'screening_session_id',
        'screening_question_id',
        'screening_option_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(ScreeningSession::class, 'screening_session_id');
    }

    public function question()
    {
        return $this->belongsTo(ScreeningQuestion::class, 'screening_question_id');
    }

    public function option()
    {
        return $this->belongsTo(ScreeningOption::class, 'screening_option_id');
    }
}

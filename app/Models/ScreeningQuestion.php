<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScreeningQuestion extends Model
{
    protected $fillable = [
        'screening_package_id',
        'question_text',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function package()
    {
        return $this->belongsTo(ScreeningPackage::class, 'screening_package_id');
    }

    public function options()
    {
        return $this->hasMany(ScreeningOption::class)->orderBy('order');
    }

    public function dimensions()
    {
        return $this->belongsToMany(ScreeningDimension::class, 'screening_question_dimensions', 'screening_question_id', 'screening_dimension_id')
            ->withPivot('weight');
    }

    public function answers()
    {
        return $this->hasMany(ScreeningAnswer::class);
    }
}

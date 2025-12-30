<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScreeningDimension extends Model
{
    protected $fillable = [
        'screening_package_id',
        'code',
        'name',
        'description',
        'multiplier',
    ];

    protected $casts = [
        'multiplier' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function package()
    {
        return $this->belongsTo(ScreeningPackage::class, 'screening_package_id');
    }

    public function questions()
    {
        return $this->belongsToMany(ScreeningQuestion::class, 'screening_question_dimensions', 'screening_dimension_id', 'screening_question_id')
            ->withPivot('weight');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScreeningSession extends Model
{
    protected $fillable = [
        'user_id',
        'screening_package_id',
        'started_at',
        'submitted_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(ScreeningPackage::class, 'screening_package_id');
    }

    public function answers()
    {
        return $this->hasMany(ScreeningAnswer::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScreeningPackage extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function dimensions()
    {
        return $this->hasMany(ScreeningDimension::class);
    }

    public function questions()
    {
        return $this->hasMany(ScreeningQuestion::class);
    }

    public function sessions()
    {
        return $this->hasMany(ScreeningSession::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class School extends Model
{
    protected $fillable = [
        'name',
        'npsn',
        'address',
        'phone',
    ];

    public function classes(): HasMany
    {
        return $this->hasMany(ClassModel::class);
    }

    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'school_admins', 'school_id', 'user_id');
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'school_teachers', 'school_id', 'teacher_id');
    }
}

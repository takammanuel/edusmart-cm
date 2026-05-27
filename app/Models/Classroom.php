<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'specialty', 'level'])]
class Classroom extends Model
{
    use HasFactory;

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class)
            ->withPivot('subject_id')
            ->withTimestamps();
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function absences(): HasMany
    {
        return $this->hasMany(Absence::class);
    }

    public function remarks(): HasMany
    {
        return $this->hasMany(Remark::class);
    }

    public function courseProgressions(): HasMany
    {
        return $this->hasMany(CourseProgression::class);
    }
}

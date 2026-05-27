<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['first_name', 'last_name', 'main_subject_id', 'email', 'phone'])]
class Teacher extends Model
{
    use HasFactory;

    public function mainSubject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'main_subject_id');
    }

    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class)
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

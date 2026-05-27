<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'code', 'coefficient'])]
class Subject extends Model
{
    use HasFactory;

    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class, 'main_subject_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function courseProgressions(): HasMany
    {
        return $this->hasMany(CourseProgression::class);
    }
}

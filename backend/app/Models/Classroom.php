<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'level',
        'section',
        'school_year',
        'max_students',
    ];

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_classroom_subject')
                    ->withPivot('subject_id')
                    ->withTimestamps();
    }

    public function timetables()
    {
        return $this->hasMany(TimeTable::class);
    }
}

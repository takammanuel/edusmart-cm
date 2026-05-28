<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'specialty',
        'phone',
        'status', // 'active' | 'inactive'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classrooms()
    {
        return $this->belongsToMany(Classroom::class, 'teacher_classroom_subject')
                    ->withPivot('subject_id')
                    ->withTimestamps();
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_classroom_subject')
                    ->withPivot('classroom_id')
                    ->withTimestamps();
    }
}

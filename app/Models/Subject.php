<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'coefficient',
        'classroom_id',
        'description',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_classroom_subject')
                    ->withPivot('classroom_id')
                    ->withTimestamps();
    }
}

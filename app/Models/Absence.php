<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'student_id',
    'classroom_id',
    'teacher_id',
    'date',
    'hours',
    'is_justified',
    'reason',
    'client_uuid',
    'recorded_at',
])]
class Absence extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_justified' => 'boolean',
            'recorded_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}

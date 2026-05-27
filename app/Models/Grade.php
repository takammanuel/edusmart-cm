<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'student_id',
    'classroom_id',
    'subject_id',
    'teacher_id',
    'sequence_id',
    'value',
    'client_uuid',
    'recorded_at',
])]
class Grade extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
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

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(Sequence::class);
    }
}

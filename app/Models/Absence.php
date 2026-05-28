<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'sequence_id',
        'date',
        'hours',
        'justified',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'date'      => 'date',
            'justified' => 'boolean',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function sequence()
    {
        return $this->belongsTo(Sequence::class);
    }
}

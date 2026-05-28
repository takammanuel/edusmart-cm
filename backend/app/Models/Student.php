<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'matricule',
        'birth_date',
        'gender',
        'classroom_id',
        'parent_name',
        'parent_phone',
        'status', // 'active' | 'expelled' | 'transferred'
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function absences()
    {
        return $this->hasMany(Absence::class);
    }
}

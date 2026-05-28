<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sequence extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'number',
        'trimester',
        'start_date',
        'end_date',
        'is_active',
        'school_year',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
            'is_active'  => 'boolean',
        ];
    }

    public function absences()
    {
        return $this->hasMany(Absence::class);
    }
}

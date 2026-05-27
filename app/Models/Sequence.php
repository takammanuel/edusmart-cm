<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['number', 'name', 'school_year', 'is_active'])]
class Sequence extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function remarks(): HasMany
    {
        return $this->hasMany(Remark::class);
    }
}

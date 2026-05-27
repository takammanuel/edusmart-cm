<?php

namespace Database\Seeders;

use App\Models\Sequence;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class EdusmartBaseSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['name' => 'Mathématiques', 'code' => 'MATH'],
            ['name' => 'Français', 'code' => 'FR'],
            ['name' => 'Anglais', 'code' => 'EN'],
            ['name' => 'Physique', 'code' => 'PHY'],
            ['name' => 'SVT', 'code' => 'SVT'],
            ['name' => 'Histoire-Géographie', 'code' => 'HG'],
        ];

        foreach ($subjects as $subject) {
            Subject::query()->firstOrCreate(['code' => $subject['code']], $subject);
        }

        $schoolYear = '2025-2026';

        for ($i = 1; $i <= 6; $i++) {
            Sequence::query()->firstOrCreate(
                ['number' => $i, 'school_year' => $schoolYear],
                [
                    'name' => "{$i}".($i === 1 ? 'ère' : 'ème').' séquence',
                    'is_active' => $i === 1,
                ]
            );
        }
    }
}

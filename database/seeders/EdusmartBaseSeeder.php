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
            ['name' => 'Mathématiques', 'code' => 'MATH', 'coefficient' => 4],
            ['name' => 'Français', 'code' => 'FR', 'coefficient' => 4],
            ['name' => 'Anglais', 'code' => 'EN', 'coefficient' => 3],
            ['name' => 'Physique', 'code' => 'PHY', 'coefficient' => 3],
            ['name' => 'SVT', 'code' => 'SVT', 'coefficient' => 2],
            ['name' => 'Histoire-Géographie', 'code' => 'HG', 'coefficient' => 2],
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

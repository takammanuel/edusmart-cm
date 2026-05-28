<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Sequence;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer les MATIÈRES (sans classroom_id — matières globales)
        $subjects = [
            ['name' => 'Mathématiques', 'code' => 'MAT', 'coefficient' => 3],
            ['name' => 'Français',       'code' => 'FRA', 'coefficient' => 2],
            ['name' => 'Anglais',        'code' => 'ANG', 'coefficient' => 2],
            ['name' => 'Histoire Géo',   'code' => 'HGEO', 'coefficient' => 1.5],
            ['name' => 'Sciences Nat.',  'code' => 'SN',  'coefficient' => 2],
            ['name' => 'Éducation Phys.','code' => 'EP',  'coefficient' => 1],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
        echo "✅ " . count($subjects) . " matières créées\n";

        // Créer les SÉQUENCES
        $sequences = [
            ['name' => 'Séquence 1', 'number' => 1, 'trimester' => 1, 'start_date' => '2024-09-09', 'end_date' => '2024-10-25', 'school_year' => '2024-2025', 'is_active' => true],
            ['name' => 'Séquence 2', 'number' => 2, 'trimester' => 1, 'start_date' => '2024-10-28', 'end_date' => '2024-12-13', 'school_year' => '2024-2025', 'is_active' => false],
            ['name' => 'Séquence 3', 'number' => 3, 'trimester' => 2, 'start_date' => '2025-01-06', 'end_date' => '2025-02-21', 'school_year' => '2024-2025', 'is_active' => false],
            ['name' => 'Séquence 4', 'number' => 4, 'trimester' => 2, 'start_date' => '2025-02-24', 'end_date' => '2025-04-11', 'school_year' => '2024-2025', 'is_active' => false],
            ['name' => 'Séquence 5', 'number' => 5, 'trimester' => 3, 'start_date' => '2025-04-22', 'end_date' => '2025-06-06', 'school_year' => '2024-2025', 'is_active' => false],
            ['name' => 'Séquence 6', 'number' => 6, 'trimester' => 3, 'start_date' => '2025-06-09', 'end_date' => '2025-07-04', 'school_year' => '2024-2025', 'is_active' => false],
        ];

        foreach ($sequences as $sequence) {
            Sequence::create($sequence);
        }
        echo "✅ " . count($sequences) . " séquences créées\n";

        // Créer les CLASSES
        $classroomsData = [
            ['name' => '6ème A', 'level' => '6ème', 'section' => 'Générale', 'school_year' => '2024-2025', 'max_students' => 50],
            ['name' => '6ème B', 'level' => '6ème', 'section' => 'Générale', 'school_year' => '2024-2025', 'max_students' => 50],
            ['name' => '5ème A', 'level' => '5ème', 'section' => 'Générale', 'school_year' => '2024-2025', 'max_students' => 50],
            ['name' => '5ème B', 'level' => '5ème', 'section' => 'Générale', 'school_year' => '2024-2025', 'max_students' => 50],
            ['name' => '4ème A', 'level' => '4ème', 'section' => 'Générale', 'school_year' => '2024-2025', 'max_students' => 50],
            ['name' => '3ème A', 'level' => '3ème', 'section' => 'Générale', 'school_year' => '2024-2025', 'max_students' => 50],
        ];

        foreach ($classroomsData as $classroomData) {
            Classroom::create($classroomData);
        }
        echo "✅ " . count($classroomsData) . " classes créées\n";

        // Créer les ENSEIGNANTS (avec leur compte User)
        $teachersData = [
            ['name' => 'Jean Dupont',    'email' => 'jean.dupont@edusmart.cm',    'specialty' => 'Mathématiques'],
            ['name' => 'Marie Martin',   'email' => 'marie.martin@edusmart.cm',   'specialty' => 'Français'],
            ['name' => 'Pierre Bernard', 'email' => 'pierre.bernard@edusmart.cm', 'specialty' => 'Anglais'],
            ['name' => 'Sophie Leclerc', 'email' => 'sophie.leclerc@edusmart.cm', 'specialty' => 'Sciences Naturelles'],
            ['name' => 'Luc Moreau',     'email' => 'luc.moreau@edusmart.cm',     'specialty' => 'Histoire Géographie'],
        ];

        foreach ($teachersData as $teacherData) {
            $user = User::create([
                'name'               => $teacherData['name'],
                'email'              => $teacherData['email'],
                'password'           => Hash::make('password123'),
                'role'               => 'enseignant',
                'email_verified_at'  => now(),
            ]);

            Teacher::create([
                'user_id'   => $user->id,
                'specialty' => $teacherData['specialty'],
                'status'    => 'active',
            ]);
        }
        echo "✅ " . count($teachersData) . " enseignants créés\n";

        // Créer les ÉLÈVES
        $classroomIds = Classroom::pluck('id')->toArray();
        $firstNames = ['Ahmed', 'Fatima', 'Mohamed', 'Aïcha', 'Karim', 'Zineb', 'Hassan', 'Amina', 'Ibrahim', 'Laila'];
        $lastNames  = ['Sow', 'Dia', 'Traore', 'Diallo', 'Kone', 'Toure', 'Camara', 'Sylla', 'Ba', 'Keita'];
        $genders    = ['M', 'F'];

        $studentCount = 0;
        foreach ($classroomIds as $classroomId) {
            for ($i = 0; $i < 15; $i++) {
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName  = $lastNames[array_rand($lastNames)];
                Student::create([
                    'first_name'   => $firstName,
                    'last_name'    => $lastName,
                    'matricule'    => 'STD' . str_pad($studentCount + 1, 5, '0', STR_PAD_LEFT),
                    'classroom_id' => $classroomId,
                    'gender'       => $genders[array_rand($genders)],
                    'status'       => 'active',
                ]);
                $studentCount++;
            }
        }
        echo "✅ " . $studentCount . " élèves créés\n";
    }
}

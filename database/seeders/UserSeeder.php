<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un utilisateur ADMIN
        User::create([
            'name' => 'Administrateur',
            'email' => 'admin@edusmart.cm',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Créer un utilisateur ENSEIGNANT
        User::create([
            'name' => 'Enseignant Test',
            'email' => 'teacher@edusmart.cm',
            'password' => Hash::make('password123'),
            'role' => 'enseignant',
            'email_verified_at' => now(),
        ]);

        echo "✅ 2 utilisateurs créés avec succès!\n";
        echo "Admin: admin@edusmart.cm / password123\n";
        echo "Enseignant: teacher@edusmart.cm / password123\n";
    }
}

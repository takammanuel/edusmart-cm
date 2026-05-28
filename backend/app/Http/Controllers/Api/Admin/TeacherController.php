<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = Teacher::with(['user', 'classrooms', 'subjects']);

        if ($request->search) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        return response()->json([
            'success' => true,
            'data'    => $query->orderBy('id', 'desc')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required_without:name|string|max:255',
            'last_name'  => 'required_without:name|string|max:255',
            'name'       => 'required_without_all:first_name,last_name|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'nullable|string|min:8',
            'specialty'  => 'nullable|string|max:255',
            'phone'      => 'nullable|string|max:30',
        ]);

        // Construire le nom complet depuis first_name + last_name ou name directement
        $fullName = $data['name'] ?? trim(($data['last_name'] ?? '') . ' ' . ($data['first_name'] ?? ''));

        $user = User::create([
            'name'     => $fullName,
            'email'    => $data['email'],
            'password' => Hash::make($data['password'] ?? 'password123'),
            'role'     => 'enseignant',
        ]);

        $teacher = Teacher::create([
            'user_id'   => $user->id,
            'specialty' => $data['specialty'] ?? null,
            'phone'     => $data['phone'] ?? null,
            'status'    => 'active',
        ]);

        return response()->json([
            'success' => true,
            'data'    => $teacher->load(['user', 'classrooms', 'subjects']),
        ], 201);
    }

    public function update(Request $request, Teacher $teacher)
    {
        $data = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name'  => 'sometimes|string|max:255',
            'name'       => 'sometimes|string|max:255',
            'specialty'  => 'nullable|string|max:255',
            'phone'      => 'nullable|string|max:30',
            'status'     => 'nullable|in:active,inactive,suspended',
        ]);

        // Mettre à jour le nom de l'utilisateur
        if (isset($data['name'])) {
            $teacher->user->update(['name' => $data['name']]);
        } elseif (isset($data['first_name']) || isset($data['last_name'])) {
            $lastName  = $data['last_name']  ?? '';
            $firstName = $data['first_name'] ?? '';
            $fullName  = trim($lastName . ' ' . $firstName);
            if ($fullName) {
                $teacher->user->update(['name' => $fullName]);
            }
        }

        $teacher->update([
            'specialty' => $data['specialty'] ?? $teacher->specialty,
            'phone'     => $data['phone']     ?? $teacher->phone,
            'status'    => $data['status']    ?? $teacher->status,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $teacher->load(['user', 'classrooms', 'subjects']),
        ]);
    }

    public function assignClassroom(Request $request, Teacher $teacher)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id'   => 'required|exists:subjects,id',
        ]);

        $teacher->classrooms()->syncWithoutDetaching([
            $request->classroom_id => ['subject_id' => $request->subject_id]
        ]);

        return response()->json([
            'success' => true,
            'data'    => $teacher->load(['user', 'classrooms', 'subjects']),
        ]);
    }

    public function unassignClassroom(Request $request, Teacher $teacher)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id'   => 'required|exists:subjects,id',
        ]);

        $teacher->classrooms()->detach($request->classroom_id);

        return response()->json([
            'success' => true,
            'data'    => $teacher->load(['user', 'classrooms', 'subjects']),
        ]);
    }

    public function destroy(Teacher $teacher)
    {
        $teacher->user->delete();
        $teacher->delete();

        return response()->json(['success' => true]);
    }
}

<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Classroom;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with('classroom');

        if ($request->classroom_id) {
            $query->where('classroom_id', $request->classroom_id);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'like', '%'.$request->search.'%')
                  ->orWhere('last_name', 'like', '%'.$request->search.'%')
                  ->orWhere('matricule', 'like', '%'.$request->search.'%');
            });
        }

        return response()->json([
            'success' => true,
            'data'    => $query->orderBy('last_name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'matricule'    => 'nullable|string|unique:students,matricule',
            'birth_date'   => 'nullable|date',
            'gender'       => 'nullable|in:M,F',
            'classroom_id' => 'required|exists:classrooms,id',
            'parent_name'  => 'nullable|string|max:255',
            'parent_phone' => 'nullable|string|max:30',
        ]);

        // Auto-générer le matricule si absent
        if (empty($data['matricule'])) {
            $data['matricule'] = 'ES-' . strtoupper(uniqid());
        }
        $data['status'] = 'active';

        $student = Student::create($data);

        return response()->json([
            'success' => true,
            'data'    => $student->load('classroom'),
        ], 201);
    }

    public function update(Request $request, Student $student)
    {
        $data = $request->validate([
            'first_name'   => 'sometimes|string|max:255',
            'last_name'    => 'sometimes|string|max:255',
            'birth_date'   => 'nullable|date',
            'gender'       => 'nullable|in:M,F',
            'classroom_id' => 'sometimes|exists:classrooms,id',
            'parent_name'  => 'nullable|string|max:255',
            'parent_phone' => 'nullable|string|max:30',
        ]);

        $student->update($data);

        return response()->json([
            'success' => true,
            'data'    => $student->load('classroom'),
        ]);
    }

    public function transfer(Request $request, Student $student)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
        ]);

        $student->update([
            'classroom_id' => $request->classroom_id,
            'status'       => 'transferred',
        ]);

        return response()->json([
            'success' => true,
            'data'    => $student->load('classroom'),
        ]);
    }

    public function expel(Request $request, Student $student)
    {
        $student->update(['status' => 'expelled']);

        return response()->json([
            'success' => true,
            'data'    => $student,
        ]);
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return response()->json(['success' => true]);
    }
}

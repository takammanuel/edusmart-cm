<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function index()
    {
        $classrooms = Classroom::withCount('students')->get();

        return response()->json([
            'success' => true,
            'data'    => $classrooms,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'level'        => 'nullable|string|max:100',
            'section'      => 'nullable|string|max:100',
            'school_year'  => 'nullable|string|max:20',
            'max_students' => 'nullable|integer|min:1',
        ]);

        $classroom = Classroom::create($data);

        return response()->json([
            'success' => true,
            'data'    => $classroom,
        ], 201);
    }

    public function update(Request $request, Classroom $classroom)
    {
        $data = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'level'        => 'nullable|string|max:100',
            'section'      => 'nullable|string|max:100',
            'school_year'  => 'nullable|string|max:20',
            'max_students' => 'nullable|integer|min:1',
        ]);

        $classroom->update($data);

        return response()->json([
            'success' => true,
            'data'    => $classroom,
        ]);
    }

    public function destroy(Classroom $classroom)
    {
        $classroom->delete();

        return response()->json(['success' => true]);
    }
}

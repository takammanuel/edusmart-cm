<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Subject::query();

        if ($request->classroom_id) {
            $query->where('classroom_id', $request->classroom_id);
        }
        if ($request->search) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        return response()->json([
            'success' => true,
            'data'    => $query->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'code'         => 'nullable|string|max:20',
            'coefficient'  => 'nullable|numeric|min:0.5|max:10',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'description'  => 'nullable|string|max:500',
        ]);

        $subject = Subject::create($data);

        return response()->json([
            'success' => true,
            'data'    => $subject->load('classroom'),
        ], 201);
    }

    public function update(Request $request, Subject $subject)
    {
        $data = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'code'         => 'nullable|string|max:20',
            'coefficient'  => 'nullable|numeric|min:0.5|max:10',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'description'  => 'nullable|string|max:500',
        ]);

        $subject->update($data);

        return response()->json([
            'success' => true,
            'data'    => $subject->load('classroom'),
        ]);
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();

        return response()->json(['success' => true]);
    }
}

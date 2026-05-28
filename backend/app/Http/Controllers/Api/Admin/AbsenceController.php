<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Student;
use Illuminate\Http\Request;

class AbsenceController extends Controller
{
    public function index(Request $request)
    {
        $query = Absence::with(['student.classroom', 'sequence']);

        if ($request->student_id) {
            $query->where('student_id', $request->student_id);
        }
        if ($request->sequence_id) {
            $query->where('sequence_id', $request->sequence_id);
        }
        if ($request->classroom_id) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('classroom_id', $request->classroom_id);
            });
        }

        return response()->json([
            'success' => true,
            'data'    => $query->orderBy('date', 'desc')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id'  => 'required|exists:students,id',
            'sequence_id' => 'nullable|exists:sequences,id',
            'date'        => 'required|date',
            'hours'       => 'nullable|numeric|min:0.5',
            'justified'   => 'boolean',
            'reason'      => 'nullable|string|max:500',
        ]);

        $data['justified'] = $data['justified'] ?? false;

        $absence = Absence::create($data);

        return response()->json([
            'success' => true,
            'data'    => $absence->load(['student.classroom', 'sequence']),
        ], 201);
    }

    public function update(Request $request, Absence $absence)
    {
        $data = $request->validate([
            'date'        => 'sometimes|date',
            'hours'       => 'nullable|numeric|min:0.5',
            'justified'   => 'boolean',
            'reason'      => 'nullable|string|max:500',
            'sequence_id' => 'nullable|exists:sequences,id',
        ]);

        $absence->update($data);

        return response()->json([
            'success' => true,
            'data'    => $absence->load(['student.classroom', 'sequence']),
        ]);
    }

    public function destroy(Absence $absence)
    {
        $absence->delete();

        return response()->json(['success' => true]);
    }
}

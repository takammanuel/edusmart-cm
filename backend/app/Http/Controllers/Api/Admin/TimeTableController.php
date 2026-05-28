<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimeTable;
use Illuminate\Http\Request;

class TimeTableController extends Controller
{
    public function index(Request $request)
    {
        $query = TimeTable::with(['classroom', 'subject', 'teacher.user']);

        if ($request->classroom_id) {
            $query->where('classroom_id', $request->classroom_id);
        }
        if ($request->teacher_id) {
            $query->where('teacher_id', $request->teacher_id);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->orderBy('day_of_week')
                               ->orderBy('start_time')
                               ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id'   => 'required|exists:subjects,id',
            'teacher_id'   => 'required|exists:teachers,id',
            'day_of_week'  => 'required|in:lundi,mardi,mercredi,jeudi,vendredi,samedi',
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
        ]);

        $timetable = TimeTable::create($data);

        return response()->json([
            'success' => true,
            'data'    => $timetable->load(['classroom', 'subject', 'teacher.user']),
        ], 201);
    }

    public function update(Request $request, TimeTable $timeTable)
    {
        $data = $request->validate([
            'classroom_id' => 'sometimes|exists:classrooms,id',
            'subject_id'   => 'sometimes|exists:subjects,id',
            'teacher_id'   => 'sometimes|exists:teachers,id',
            'day_of_week'  => 'sometimes|in:lundi,mardi,mercredi,jeudi,vendredi,samedi',
            'start_time'   => 'sometimes|date_format:H:i',
            'end_time'     => 'sometimes|date_format:H:i',
        ]);

        $timeTable->update($data);

        return response()->json([
            'success' => true,
            'data'    => $timeTable->load(['classroom', 'subject', 'teacher.user']),
        ]);
    }

    public function destroy(TimeTable $timeTable)
    {
        $timeTable->delete();

        return response()->json(['success' => true]);
    }
}

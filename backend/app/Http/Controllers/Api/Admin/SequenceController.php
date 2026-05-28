<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sequence;
use Illuminate\Http\Request;

class SequenceController extends Controller
{
    public function index(Request $request)
    {
        $query = Sequence::query();

        if ($request->school_year) {
            $query->where('school_year', $request->school_year);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->orderBy('start_date')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'number'      => 'nullable|integer|min:1|max:6',
            'trimester'   => 'nullable|integer|in:1,2,3',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after:start_date',
            'school_year' => 'nullable|string|max:20',
            'is_active'   => 'boolean',
        ]);

        // Désactiver les autres séquences si celle-ci est active
        if (!empty($data['is_active']) && $data['is_active']) {
            Sequence::where('is_active', true)->update(['is_active' => false]);
        }

        $sequence = Sequence::create($data);

        return response()->json([
            'success' => true,
            'data'    => $sequence,
        ], 201);
    }

    public function update(Request $request, Sequence $sequence)
    {
        $data = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'number'      => 'nullable|integer|min:1|max:6',
            'trimester'   => 'nullable|integer|in:1,2,3',
            'start_date'  => 'sometimes|date',
            'end_date'    => 'sometimes|date',
            'school_year' => 'nullable|string|max:20',
            'is_active'   => 'boolean',
        ]);

        if (!empty($data['is_active']) && $data['is_active']) {
            Sequence::where('id', '!=', $sequence->id)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
        }

        $sequence->update($data);

        return response()->json([
            'success' => true,
            'data'    => $sequence,
        ]);
    }

    public function destroy(Sequence $sequence)
    {
        $sequence->delete();

        return response()->json(['success' => true]);
    }
}

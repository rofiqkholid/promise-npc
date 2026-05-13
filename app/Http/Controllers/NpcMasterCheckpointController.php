<?php

namespace App\Http\Controllers;

use App\Models\NpcMasterCheckpoint;
use Illuminate\Http\Request;

class NpcMasterCheckpointController extends Controller
{
    public function index(Request $request)
    {
        $query = NpcMasterCheckpoint::orderBy('point_number', 'asc');
        
        if ($request->has('search') && $request->search != '') {
            $query->where('check_item', 'like', '%' . $request->search . '%')
                  ->orWhere('point_number', 'like', '%' . $request->search . '%');
        }

        $checkpoints = $query->paginate(20);
        return view('master.checkpoints.index', compact('checkpoints'));
    }

    public function create()
    {
        // Suggest the next point number automagically
        $maxPoint = NpcMasterCheckpoint::max('point_number') ?? 0;
        $nextPoint = $maxPoint + 1;
        
        return view('master.checkpoints.create', compact('nextPoint'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'point_number' => 'required|integer|unique:npc_master_checkpoints',
            'check_item'   => 'required|string|max:255',
        ]);

        NpcMasterCheckpoint::create([
            'point_number' => $request->point_number,
            'check_item'   => $request->check_item,
            'is_active'    => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('master.checkpoints.index')->with('success', 'QA Checkpoint successfully added.');
    }

    public function edit(NpcMasterCheckpoint $checkpoint)
    {
        return view('master.checkpoints.edit', compact('checkpoint'));
    }

    public function update(Request $request, NpcMasterCheckpoint $checkpoint)
    {
        $request->validate([
            'point_number' => 'required|integer|unique:npc_master_checkpoints,point_number,' . $checkpoint->id,
            'check_item'   => 'required|string|max:255',
        ]);

        $checkpoint->update([
            'point_number' => $request->point_number,
            'check_item'   => $request->check_item,
            'is_active'    => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('master.checkpoints.index')->with('success', 'QA Checkpoint successfully updated.');
    }

    public function destroy(NpcMasterCheckpoint $checkpoint)
    {
        $checkpoint->delete();
        return redirect()->route('master.checkpoints.index')->with('success', 'QA Checkpoint successfully deleted.');
    }
}

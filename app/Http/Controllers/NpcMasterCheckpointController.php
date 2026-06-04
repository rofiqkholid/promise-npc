<?php

namespace App\Http\Controllers;

use App\Models\NpcMasterCheckpoint;
use Illuminate\Http\Request;

class NpcMasterCheckpointController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = NpcMasterCheckpoint::query();
            
            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->editColumn('point_number', function ($cp) {
                    return '<div class="font-bold text-center text-lg text-indigo-600 dark:text-indigo-400">' . $cp->point_number . '</div>';
                })
                ->editColumn('is_active', function ($cp) {
                    if ($cp->is_active) {
                        return '<span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 border border-green-200 dark:border-green-800">Active</span>';
                    }
                    return '<span class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">Omit</span>';
                })
                ->addColumn('action', function ($cp) {
                    return view('components.datatable-actions', [
                        'editUrl' => route('master.checkpoints.edit', $cp->hashed_id),
                        'deleteUrl' => route('master.checkpoints.destroy', $cp->hashed_id),
                        'deleteMessage' => 'Permanently delete this check point?'
                    ])->render();
                })
                ->rawColumns(['point_number', 'is_active', 'action'])
                ->make(true);
        }

        return view('master.checkpoints.index');
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

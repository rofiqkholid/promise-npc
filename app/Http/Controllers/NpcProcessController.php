<?php

namespace App\Http\Controllers;

use App\Models\NpcProcess;
use App\Models\NpcDepartment;
use Illuminate\Http\Request;

class NpcProcessController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = NpcProcess::with('departments');
            
            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('departments', function ($process) {
                    return view('master.processes.columns.departments', compact('process'))->render();
                })
                ->addColumn('action', function ($process) {
                    return view('components.datatable-actions', [
                        'editUrl' => route('master.processes.edit', $process->hashed_id),
                        'deleteUrl' => route('master.processes.destroy', $process->hashed_id),
                        'deleteMessage' => 'Permanently delete this process?'
                    ])->render();
                })
                ->rawColumns(['departments', 'action'])
                ->make(true);
        }

        return view('master.processes.index');
    }

    public function create()
    {
        $departments = NpcDepartment::where('is_active', true)->orderBy('name')->get();
        return view('master.processes.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'process_name' => 'required|string|max:255|unique:npc_processes',
            'department_ids'   => 'required|array|min:1',
            'department_ids.*' => 'exists:npc_departments,id',
        ]);

        $process = NpcProcess::create([
            'process_name' => $request->process_name
        ]);
        
        $process->departments()->sync($request->department_ids);

        return redirect()->route('master.processes.index')->with('success', 'Process Master successfully added.');
    }

    public function edit(NpcProcess $process)
    {
        $departments = NpcDepartment::where('is_active', true)->orderBy('name')->get();
        return view('master.processes.edit', compact('process', 'departments'));
    }

    public function update(Request $request, NpcProcess $process)
    {
        $request->validate([
            'process_name' => 'required|string|max:255|unique:npc_processes,process_name,' . $process->id,
            'department_ids'   => 'required|array|min:1',
            'department_ids.*' => 'exists:npc_departments,id',
        ]);

        $process->update([
            'process_name' => $request->process_name
        ]);
        
        $process->departments()->sync($request->department_ids);

        return redirect()->route('master.processes.index')->with('success', 'Process Master successfully updated.');
    }

    public function destroy(NpcProcess $process)
    {
        $process->delete();
        return redirect()->route('master.processes.index')->with('success', 'Process Master successfully deleted.');
    }
}

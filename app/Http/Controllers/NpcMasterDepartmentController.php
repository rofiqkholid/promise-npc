<?php

namespace App\Http\Controllers;

use App\Models\NpcDepartment;
use Illuminate\Http\Request;

class NpcMasterDepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = NpcDepartment::query();
            
            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('full_name', function ($dept) {
                    return $dept->full_name ?? '-';
                })
                ->editColumn('is_active', function ($dept) {
                    if ($dept->is_active) {
                        return '<span class="px-2.5 py-1 border text-xs font-semibold bg-green-100 text-green-800 border-green-200 dark:bg-green-900/40 dark:text-green-300 dark:border-green-800">Active</span>';
                    }
                    return '<span class="px-2.5 py-1 border text-xs font-semibold bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">Inactive</span>';
                })
                ->addColumn('action', function ($dept) {
                    return view('components.datatable-actions', [
                        'editUrl' => route('master.departments.edit', $dept->hashed_id),
                        'deleteUrl' => route('master.departments.destroy', $dept->hashed_id),
                        'deleteMessage' => 'Permanently delete this department?'
                    ])->render();
                })
                ->rawColumns(['is_active', 'action'])
                ->make(true);
        }

        return view('master.departments.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('master.departments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:npc_departments,name',
            'full_name' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        NpcDepartment::create($validated);

        return redirect()->route('master.departments.index')->with('success', 'Department successfully added.');
    }

    /**
     * Display the specified resource.
     */
    public function show(NpcDepartment $npcDepartment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(NpcDepartment $department)
    {
        return view('master.departments.edit', compact('department'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, NpcDepartment $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:npc_departments,name,' . $department->id,
            'full_name' => 'nullable|string|max:255',
            'is_active' => 'nullable' // Checkbox might be missing
        ]);

        // Handle checkbox
        $validated['is_active'] = $request->has('is_active');

        $department->update($validated);

        return redirect()->route('master.departments.index')->with('success', 'Department successfully updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NpcDepartment $department)
    {
        // Check if being used in NpcProcess? (Optional but good)
        // For now just delete
        $department->delete();

        return redirect()->route('master.departments.index')->with('success', 'Department successfully deleted.');
    }
}

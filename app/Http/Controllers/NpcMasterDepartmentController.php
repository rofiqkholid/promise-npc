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
        $query = NpcDepartment::orderBy('name');
        
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('full_name', 'like', '%' . $request->search . '%');
        }

        $departments = $query->paginate(20);
        return view('master.departments.index', compact('departments'));
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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NpcRoleController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\NpcRole::orderBy('name');
        
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
        }

        $roles = $query->paginate(20);
        return view('master.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('master.roles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:npc_roles,code',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        \App\Models\NpcRole::create($request->all());

        return redirect()->route('master.roles.index')->with('success', 'Role created successfully. You can now configure its permissions.');
    }

    public function edit(\App\Models\NpcRole $role)
    {
        // Load all menus to build the permission matrix
        $menus = \App\Models\NpcMenu::whereNull('parent_id')
            ->with('children')
            ->orderBy('order')
            ->get();

        // Load the role's current permissions
        $role->load('menus');
        $roleMenuIds = $role->menus->pluck('pivot', 'id');

        return view('master.roles.edit', compact('role', 'menus', 'roleMenuIds'));
    }

    public function update(Request $request, \App\Models\NpcRole $role)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:npc_roles,code,' . $role->id,
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        // Update role basic info
        $role->update($request->only(['code', 'name', 'description']));

        // Synchronize permissions
        $permissions = $request->input('permissions', []);
        
        $syncData = [];
        foreach ($permissions as $menuId => $perms) {
            // Only save if at least one permission is checked
            if (isset($perms['can_view']) || isset($perms['can_create']) || isset($perms['can_update']) || isset($perms['can_delete']) || isset($perms['can_approve'])) {
                $syncData[$menuId] = [
                    'can_view' => isset($perms['can_view']) ? 1 : 0,
                    'can_create' => isset($perms['can_create']) ? 1 : 0,
                    'can_update' => isset($perms['can_update']) ? 1 : 0,
                    'can_delete' => isset($perms['can_delete']) ? 1 : 0,
                    'can_approve' => isset($perms['can_approve']) ? 1 : 0,
                ];
            }
        }

        $role->menus()->sync($syncData);

        return redirect()->route('master.roles.index')->with('success', 'Role and permissions updated successfully.');
    }

    public function destroy(\App\Models\NpcRole $role)
    {
        // Prevent deleting admin role
        if ($role->code === 'admin') {
            return redirect()->route('master.roles.index')->with('error', 'Cannot delete the administrator role.');
        }

        $role->delete();
        return redirect()->route('master.roles.index')->with('success', 'Role deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NpcRoleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = \App\Models\NpcRole::query();

            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('code', function ($role) {
                    return '<span class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">' . $role->code . '</span>';
                })
                ->addColumn('name', function ($role) {
                    return '<span class="font-semibold text-slate-900 dark:text-white">' . $role->name . '</span>';
                })
                ->addColumn('description', function ($role) {
                    return '<span class="text-sm">' . ($role->description ?: '-') . '</span>';
                })
                ->addColumn('users_count', function ($role) {
                    return '<span class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full">' . $role->users()->count() . ' Users</span>';
                })
                ->addColumn('action', function ($role) {
                    return view('components.datatable-actions', [
                        'editUrl' => route('master.roles.edit', $role->id),
                        'deleteUrl' => route('master.roles.destroy', $role->id),
                        'deleteMessage' => 'Are you sure you want to delete this role?'
                    ])->render();
                })
                ->rawColumns(['code', 'name', 'description', 'users_count', 'action'])
                ->make(true);
        }

        return view('master.roles.index');
    }

    public function create()
    {
        return view('master.roles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:roles,code',
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
            ->orderBy('sort_order')
            ->get();

        // Load the role's current permissions
        $rolePermissions = \Illuminate\Support\Facades\DB::table('role_scope_permissions')
            ->where('role_id', $role->id)
            ->where('scope_id', 'app_npc')
            ->get();
            
        $roleMenuIds = collect();
        foreach ($rolePermissions as $rp) {
            $menuId = $rp->menu_id;
            if (!$roleMenuIds->has($menuId)) {
                $roleMenuIds->put($menuId, (object)[
                    'can_view' => false,
                    'can_create' => false,
                    'can_update' => false,
                    'can_delete' => false,
                    'can_approve' => false
                ]);
            }
            $obj = $roleMenuIds->get($menuId);
            if ($rp->permission_id == 1) $obj->can_view = true;
            if ($rp->permission_id == 2) $obj->can_create = true;
            if ($rp->permission_id == 7) $obj->can_update = true;
            if ($rp->permission_id == 4) $obj->can_delete = true;
            if ($rp->permission_id == 8) $obj->can_approve = true;
        }

        return view('master.roles.edit', compact('role', 'menus', 'roleMenuIds'));
    }

    public function update(Request $request, \App\Models\NpcRole $role)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:roles,code,' . $role->id,
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        // Update role basic info
        $role->update($request->only(['code', 'name', 'description']));

        // Synchronize permissions
        $permissions = $request->input('permissions', []);
        
        $permMap = [
            'can_view' => 1,
            'can_create' => 2,
            'can_update' => 7,
            'can_delete' => 4,
            'can_approve' => 8
        ];

        \Illuminate\Support\Facades\DB::table('role_scope_permissions')->where('role_id', $role->id)->delete();

        $insertData = [];
        foreach ($permissions as $menuId => $perms) {
            foreach ($permMap as $key => $permId) {
                if (isset($perms[$key])) {
                    $insertData[] = [
                        'role_id' => $role->id,
                        'menu_id' => $menuId,
                        'permission_id' => $permId,
                        'scope_id' => 'app_npc',
                    ];
                }
            }
        }

        if (!empty($insertData)) {
            \Illuminate\Support\Facades\DB::table('role_scope_permissions')->insert($insertData);
        }

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

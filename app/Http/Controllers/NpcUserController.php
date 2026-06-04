<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NpcUserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = \App\Models\User::whereHas('roles')->with('roles');

            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('name_email', function ($user) {
                    $html = '<div class="text-blue-900 dark:text-blue-400 font-semibold text-sm">' . $user->name . '</div>';
                    $html .= '<div class="text-xs text-slate-500 font-normal mt-0.5">' . $user->email . '</div>';
                    return $html;
                })
                ->addColumn('nik', function ($user) {
                    return '<span class="text-gray-600 dark:text-gray-400 text-sm font-medium">' . ($user->nik ?? '-') . '</span>';
                })
                ->addColumn('roles', function ($user) {
                    $html = '<div class="flex flex-wrap gap-1">';
                    foreach ($user->roles as $role) {
                        $html .= '<span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 border border-blue-200 dark:border-blue-800">';
                        $html .= $role->name;
                        $html .= '</span>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('action', function ($user) {
                    return view('components.datatable-actions', [
                        'editUrl' => route('master.npc-users.edit', $user->hashed_id),
                        'deleteUrl' => route('master.npc-users.destroy', $user->hashed_id),
                        'deleteMessage' => 'Are you sure you want to revoke NPC access for this user?'
                    ])->render();
                })
                ->filterColumn('name_email', function($query, $keyword) {
                    $query->where('name', 'like', "%{$keyword}%")
                          ->orWhere('email', 'like', "%{$keyword}%");
                })
                ->rawColumns(['name_email', 'nik', 'roles', 'action'])
                ->make(true);
        }

        return view('master.npc-users.index');
    }

    public function create()
    {
        // Get all users who don't have an NPC role yet
        $availableUsers = \App\Models\User::whereDoesntHave('roles')->orderBy('name')->get();
        $roles = \App\Models\NpcRole::orderBy('name')->get();
        
        return view('master.npc-users.create', compact('availableUsers', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_ids' => 'required|array|min:1',
            'role_ids.*' => 'exists:npc_roles,id'
        ]);

        $user = \App\Models\User::where('id', $request->user_id)->firstOrFail();
        $user->roles()->sync($request->role_ids);

        return redirect()->route('master.npc-users.index')->with('success', 'NPC User added successfully. You can edit the user to configure individual permissions if needed.');
    }

    public function edit(string $id)
    {
        if (!is_numeric($id)) {
            $hashids = new \Hashids\Hashids(env('APP_KEY'), 10);
            $decoded = $hashids->decode($id);
            $id = !empty($decoded) ? $decoded[0] : abort(404);
        }
        $user = \App\Models\User::where('id', $id)->firstOrFail();
        
        // Prevent editing non-NPC users here
        if ($user->roles->isEmpty()) {
            return redirect()->route('master.npc-users.index')->with('error', 'That user is not an NPC User.');
        }

        $roles = \App\Models\NpcRole::orderBy('name')->get();
        
        // Load all menus to build the permission matrix
        $menus = \App\Models\NpcMenu::whereNull('parent_id')
            ->with('children')
            ->orderBy('order')
            ->get();

        // Load the user's explicit extra permissions
        $user->load('specificMenus');
        $userMenuIds = $user->specificMenus->pluck('pivot', 'id');

        // Load the user's role permissions to display as "inherited"
        $roleMenus = collect();
        foreach ($user->roles()->with('menus')->get() as $role) {
            $roleMenus = $roleMenus->merge($role->menus);
        }
        
        // Key the role menus by ID and merge permissions (logical OR)
        $inheritedPermissions = [];
        foreach ($roleMenus as $menu) {
            if (!isset($inheritedPermissions[$menu->id])) {
                $inheritedPermissions[$menu->id] = [
                    'can_view' => false,
                    'can_create' => false,
                    'can_update' => false,
                    'can_delete' => false,
                    'can_approve' => false,
                ];
            }
            $p = $menu->pivot;
            $inheritedPermissions[$menu->id]['can_view'] |= $p->can_view;
            $inheritedPermissions[$menu->id]['can_create'] |= $p->can_create;
            $inheritedPermissions[$menu->id]['can_update'] |= $p->can_update;
            $inheritedPermissions[$menu->id]['can_delete'] |= $p->can_delete;
            $inheritedPermissions[$menu->id]['can_approve'] |= $p->can_approve;
        }

        return view('master.npc-users.edit', compact('user', 'roles', 'menus', 'userMenuIds', 'inheritedPermissions'));
    }

    public function update(Request $request, string $id)
    {
        if (!is_numeric($id)) {
            $hashids = new \Hashids\Hashids(env('APP_KEY'), 10);
            $decoded = $hashids->decode($id);
            $id = !empty($decoded) ? $decoded[0] : abort(404);
        }
        $user = \App\Models\User::where('id', $id)->firstOrFail();

        $request->validate([
            'role_ids' => 'required|array|min:1',
            'role_ids.*' => 'exists:npc_roles,id'
        ]);

        // Update role
        $user->roles()->sync($request->role_ids);

        // Synchronize individual permissions
        $permissions = $request->input('permissions', []);
        
        $syncData = [];
        foreach ($permissions as $menuId => $perms) {
            // Only save if at least one permission is explicitly checked
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

        $user->specificMenus()->sync($syncData);

        return redirect()->route('master.npc-users.index')->with('success', 'NPC User roles and permissions updated successfully.');
    }

    public function destroy(string $id)
    {
        if (!is_numeric($id)) {
            $hashids = new \Hashids\Hashids(env('APP_KEY'), 10);
            $decoded = $hashids->decode($id);
            $id = !empty($decoded) ? $decoded[0] : abort(404);
        }
        $user = \App\Models\User::where('id', $id)->firstOrFail();
        
        // Prevent revoking admin from self if it's the last admin
        if ($user->nik === Auth::id() && $user->roles->contains('code', 'admin')) {
            // Checking if other admins exist could be done, but for simplicity:
            return redirect()->route('master.npc-users.index')->with('error', 'You cannot revoke your own access.');
        }

        // Just detach the roles and specific menus, do NOT delete the user
        $user->roles()->detach();
        $user->specificMenus()->detach();

        return redirect()->route('master.npc-users.index')->with('success', 'NPC Access revoked successfully. The user is still in the system but no longer has NPC access.');
    }
}

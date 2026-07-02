<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NpcMenuController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('search') && $request->search != '') {
            $menus = \App\Models\NpcMenu::where('title', 'like', '%' . $request->search . '%')
                ->orWhere('route', 'like', '%' . $request->search . '%')
                ->orderBy('sort_order')
                ->get();
        } else {
            $parents = \App\Models\NpcMenu::whereNull('parent_id')
                ->with('children')
                ->orderBy('sort_order')
                ->get();

            $menus = collect();
            foreach ($parents as $parent) {
                $menus->push($parent);
                foreach ($parent->children as $child) {
                    $menus->push($child);
                }
            }
        }

        return view('master.menus.index', compact('menus'));
    }

    public function create()
    {
        $parents = \App\Models\NpcMenu::whereNull('parent_id')->orderBy('sort_order')->get();
        return view('master.menus.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'route' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'required|integer',
            'parent_id' => 'nullable|exists:menus,id',
            'is_active' => 'boolean'
        ]);

        \App\Models\NpcMenu::create($request->all());

        return redirect()->route('master.menus.index')->with('success', 'Menu created successfully.');
    }

    public function edit(\App\Models\NpcMenu $menu)
    {
        $parents = \App\Models\NpcMenu::whereNull('parent_id')->where('id', '!=', $menu->id)->orderBy('sort_order')->get();
        return view('master.menus.edit', compact('menu', 'parents'));
    }

    public function update(Request $request, \App\Models\NpcMenu $menu)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'route' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'required|integer',
            'parent_id' => 'nullable|exists:menus,id',
            'is_active' => 'boolean'
        ]);

        $menu->update($request->all());

        return redirect()->route('master.menus.index')->with('success', 'Menu updated successfully.');
    }

    public function destroy(\App\Models\NpcMenu $menu)
    {
        $menu->delete();
        return redirect()->route('master.menus.index')->with('success', 'Menu deleted successfully.');
    }
}

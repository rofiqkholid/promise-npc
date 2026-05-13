<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NpcInternalCategory;

class NpcInternalCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = NpcInternalCategory::latest();
        
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = $query->paginate(20);
        return view('master.internal_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('master.internal_categories.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:npc_internal_categories']);
        NpcInternalCategory::create($request->all());
        return redirect()->route('master.internal-categories.index')->with('success', 'Internal Category successfully added.');
    }

    public function edit(NpcInternalCategory $internalCategory)
    {
        return view('master.internal_categories.edit', compact('internalCategory'));
    }

    public function update(Request $request, NpcInternalCategory $internalCategory)
    {
        $request->validate(['name' => 'required|string|max:255|unique:npc_internal_categories,name,' . $internalCategory->id]);
        $internalCategory->update($request->all());
        return redirect()->route('master.internal-categories.index')->with('success', 'Internal Category successfully updated.');
    }

    public function destroy(NpcInternalCategory $internalCategory)
    {
        $internalCategory->delete();
        return redirect()->route('master.internal-categories.index')->with('success', 'Internal Category successfully deleted.');
    }
}

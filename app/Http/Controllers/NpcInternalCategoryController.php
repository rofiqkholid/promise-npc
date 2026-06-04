<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NpcInternalCategory;

class NpcInternalCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = NpcInternalCategory::query();
            
            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('name', function ($category) {
                    return '<span class="font-bold text-slate-900 dark:text-white">' . $category->name . '</span>';
                })
                ->addColumn('action', function ($category) {
                    return view('components.datatable-actions', [
                        'editUrl' => route('master.internal-categories.edit', $category->hashed_id),
                        'deleteUrl' => route('master.internal-categories.destroy', $category->hashed_id),
                        'deleteMessage' => 'Permanently delete this category?'
                    ])->render();
                })
                ->rawColumns(['name', 'action'])
                ->make(true);
        }

        return view('master.internal_categories.index');
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

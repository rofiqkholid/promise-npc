<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NpcDeliveryGroup;

class NpcDeliveryGroupController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = NpcDeliveryGroup::query();
            
            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('name', function ($group) {
                    return '<span class="font-bold text-slate-900 dark:text-white">' . $group->name . '</span>';
                })
                ->addColumn('action', function ($group) {
                    return view('components.datatable-actions', [
                        'editUrl' => route('master.delivery-groups.edit', $group->hashed_id),
                        'deleteUrl' => route('master.delivery-groups.destroy', $group->hashed_id),
                        'deleteMessage' => 'Delete grup ini secara permanen?'
                    ])->render();
                })
                ->rawColumns(['name', 'action'])
                ->make(true);
        }

        return view('master.delivery_groups.index');
    }

    public function create()
    {
        return view('master.delivery_groups.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:npc_delivery_groups']);
        NpcDeliveryGroup::create($request->all());
        return redirect()->route('master.delivery-groups.index')->with('success', 'Delivery Group successfully added.');
    }

    public function edit(NpcDeliveryGroup $deliveryGroup)
    {
        return view('master.delivery_groups.edit', compact('deliveryGroup'));
    }

    public function update(Request $request, NpcDeliveryGroup $deliveryGroup)
    {
        $request->validate(['name' => 'required|string|max:255|unique:npc_delivery_groups,name,' . $deliveryGroup->id]);
        $deliveryGroup->update($request->all());
        return redirect()->route('master.delivery-groups.index')->with('success', 'Delivery Group successfully updated.');
    }

    public function destroy(NpcDeliveryGroup $deliveryGroup)
    {
        $deliveryGroup->delete();
        return redirect()->route('master.delivery-groups.index')->with('success', 'Delivery Group successfully deleted.');
    }
}

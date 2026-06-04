<?php

namespace App\Http\Controllers;

use App\Models\NpcDeliveryTarget;
use Illuminate\Http\Request;

class NpcDeliveryTargetController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = NpcDeliveryTarget::query();
            
            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('is_active', function ($target) {
                    if ($target->is_active) {
                        return '<span class="px-2.5 py-1 border text-xs font-semibold bg-green-100 text-green-800 border-green-200 dark:bg-green-900/40 dark:text-green-300 dark:border-green-800">Active</span>';
                    }
                    return '<span class="px-2.5 py-1 border text-xs font-semibold bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">Inactive</span>';
                })
                ->addColumn('action', function ($target) {
                    return view('components.datatable-actions', [
                        'editUrl' => route('master.delivery-targets.edit', $target->hashed_id),
                        'deleteUrl' => route('master.delivery-targets.destroy', $target->hashed_id),
                        'deleteMessage' => 'Permanently delete this delivery target?'
                    ])->render();
                })
                ->rawColumns(['is_active', 'action'])
                ->make(true);
        }

        return view('master.delivery_targets.index');
    }

    public function create()
    {
        return view('master.delivery_targets.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'target_name' => 'required|string|max:255|unique:npc_delivery_targets',
            'is_active'   => 'boolean'
        ]);

        NpcDeliveryTarget::create([
            'target_name' => $request->target_name,
            'is_active'   => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('master.delivery-targets.index')->with('success', 'Delivery Target successfully added.');
    }

    public function edit(NpcDeliveryTarget $deliveryTarget)
    {
        return view('master.delivery_targets.edit', compact('deliveryTarget'));
    }

    public function update(Request $request, NpcDeliveryTarget $deliveryTarget)
    {
        $request->validate([
            'target_name' => 'required|string|max:255|unique:npc_delivery_targets,target_name,' . $deliveryTarget->id,
            'is_active'   => 'boolean'
        ]);

        $deliveryTarget->update([
            'target_name' => $request->target_name,
            'is_active'   => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('master.delivery-targets.index')->with('success', 'Delivery Target successfully updated.');
    }

    public function destroy(NpcDeliveryTarget $deliveryTarget)
    {
        $deliveryTarget->delete();
        return redirect()->route('master.delivery-targets.index')->with('success', 'Delivery Target successfully deleted.');
    }
}

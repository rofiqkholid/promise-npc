<?php

namespace App\Http\Controllers;

use App\Models\NpcDeliveryTarget;
use Illuminate\Http\Request;

class NpcDeliveryTargetController extends Controller
{
    public function index(Request $request)
    {
        $query = NpcDeliveryTarget::orderBy('target_name', 'asc');
        
        if ($request->has('search') && $request->search != '') {
            $query->where('target_name', 'like', '%' . $request->search . '%');
        }

        $targets = $query->paginate(20);
        return view('master.delivery_targets.index', compact('targets'));
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

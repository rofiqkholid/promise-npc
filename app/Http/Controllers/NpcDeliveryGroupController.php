<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NpcDeliveryGroup;

class NpcDeliveryGroupController extends Controller
{
    public function index(Request $request)
    {
        $query = NpcDeliveryGroup::latest();
        
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $groups = $query->paginate(20);
        return view('master.delivery_groups.index', compact('groups'));
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

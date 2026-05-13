<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NpcCustomerCategory;
use App\Models\Customer;
use App\Models\NpcInternalCategory;

class NpcCustomerCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = NpcCustomerCategory::with(['customer', 'internalCategory'])->latest();
        
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhereHas('customer', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('code', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('internalCategory', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
        }

        $categories = $query->paginate(20);
        return view('master.customer_categories.index', compact('categories'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $internalCategories = NpcInternalCategory::orderBy('name')->get();
        return view('master.customer_categories.create', compact('customers', 'internalCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'internal_category_id' => 'required|exists:npc_internal_categories,id',
            'name' => 'required|string|max:255'
        ]);
        NpcCustomerCategory::create($request->all());
        return redirect()->route('master.customer-categories.index')->with('success', 'Customer Category Mapping successfully added.');
    }

    public function edit(NpcCustomerCategory $customerCategory)
    {
        $customers = Customer::orderBy('name')->get();
        $internalCategories = NpcInternalCategory::orderBy('name')->get();
        return view('master.customer_categories.edit', compact('customerCategory', 'customers', 'internalCategories'));
    }

    public function update(Request $request, NpcCustomerCategory $customerCategory)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'internal_category_id' => 'required|exists:npc_internal_categories,id',
            'name' => 'required|string|max:255'
        ]);
        $customerCategory->update($request->all());
        return redirect()->route('master.customer-categories.index')->with('success', 'Customer Category Mapping successfully updated.');
    }

    public function destroy(NpcCustomerCategory $customerCategory)
    {
        $customerCategory->delete();
        return redirect()->route('master.customer-categories.index')->with('success', 'Customer Category Mapping successfully deleted.');
    }
}

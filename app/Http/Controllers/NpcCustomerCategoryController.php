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
        if ($request->ajax()) {
            $query = NpcCustomerCategory::with(['customer', 'internalCategory']);
            
            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('customer.code', function ($category) {
                    return $category->customer ? $category->customer->code : 'N/A';
                })
                ->editColumn('name', function ($category) {
                    return '<span class="font-bold text-slate-900 dark:text-white">' . $category->name . '</span>';
                })
                ->editColumn('internalCategory.name', function ($category) {
                    $internalName = $category->internalCategory ? $category->internalCategory->name : 'N/A';
                    return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                                <i class="fa-solid fa-arrow-right-arrow-left text-[10px]"></i> ' . $internalName . '
                            </span>';
                })
                ->addColumn('action', function ($category) {
                    return view('components.datatable-actions', [
                        'editUrl' => route('master.customer-categories.edit', $category->hashed_id),
                        'deleteUrl' => route('master.customer-categories.destroy', $category->hashed_id),
                        'deleteMessage' => 'Permanently delete this mapping?'
                    ])->render();
                })
                ->rawColumns(['name', 'internalCategory.name', 'action'])
                ->make(true);
        }

        return view('master.customer_categories.index');
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

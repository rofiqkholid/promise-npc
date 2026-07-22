<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\NpcProductDetail;

class MasterChecksheetApprovalController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Product::has('mappedCheckpoints')->with([
                'customer', 
                'vehicleModel', 
                'productDetail', 
                'mappedCheckpoints',
                'docPackage.currentRevision',
                'siblings.docPackage.currentRevision'
            ]);

            if ($request->has('customer_id') && $request->customer_id != '' && $request->customer_id != 'ALL') {
                $query->where('customer_id', $request->customer_id);
            }

            if ($request->has('model_id') && $request->model_id != '' && $request->model_id != 'ALL') {
                $query->where('model_id', $request->model_id);
            }

            if ($request->has('status') && $request->status != '' && $request->status != 'ALL') {
                if ($request->status === 'WAITING_APPROVAL') {
                    $query->where(function($q) {
                        $q->whereDoesntHave('productDetail')
                          ->orWhereHas('productDetail', function($q2) {
                              $q2->whereNull('master_checksheet_status')
                                 ->orWhereIn('master_checksheet_status', ['DRAFT', 'WAITING_APPROVAL']);
                          });
                    });
                } else {
                    $query->whereHas('productDetail', function($q) use ($request) {
                        $q->where('master_checksheet_status', $request->status);
                    });
                }
            }

            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->order(function ($q) {
                    $q->orderBy('products.updated_at', 'desc')
                      ->orderBy('products.part_no', 'asc');
                })
                ->addIndexColumn()
                ->addColumn('customer', function ($product) {
                    return '<div class="text-sm font-bold text-gray-900 dark:text-gray-100">' . (optional($product->customer)->code ?? '-') . '</div>';
                })
                ->addColumn('model', function ($product) {
                    return '<div class="text-sm font-medium text-gray-800 dark:text-gray-200">' . (optional($product->vehicleModel)->name ?? '-') . '</div>';
                })
                ->addColumn('part_no', function ($product) {
                    return '<div class="text-blue-600 dark:text-blue-400 font-bold text-sm">' . $product->part_no . '</div>';
                })
                ->addColumn('part_name', function ($product) {
                    return '<div class="text-gray-800 dark:text-gray-200 font-bold">' . $product->part_name . '</div>';
                })
                ->addColumn('ecn_info', function ($product) {
                    $docPackage = $product->getEffectiveDocPackage();
                    if ($docPackage && $docPackage->currentRevision) {
                        return '<div class="text-sm font-bold text-gray-800 dark:text-gray-200">' . ($docPackage->currentRevision->ecn_no ?? 'No ECN') . '</div>' .
                               '<div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Rev ' . $docPackage->currentRevision->revision_no . '</div>';
                    }
                    return '<span class="text-xs text-gray-400 italic">No Data</span>';
                })
                ->addColumn('mapping_status', function ($product) {
                    $status = optional($product->productDetail)->master_checksheet_status ?? 'DRAFT';
                    $html = '';
                    if ($product->mappedCheckpoints->isNotEmpty()) {
                        $html .= '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-50 text-green-700 border border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800/50 text-[10px] font-bold uppercase tracking-wider mb-1"><i class="fa-solid fa-check-circle"></i> Mapped (' . $product->mappedCheckpoints->count() . ' Points)</span>';
                    }
                    
                    if ($status === 'DRAFT' || $status === null) {
                        $status = 'WAITING_APPROVAL';
                    }
                    
                    if ($status === 'APPROVED') {
                        $html .= '<br><span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-50 text-green-700 border border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800/50 text-[10px] font-bold uppercase tracking-wider"><i class="fa-solid fa-check-double"></i> APPROVED</span>';
                    } elseif ($status === 'WAITING_APPROVAL') {
                        $html .= '<br><span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-yellow-50 text-yellow-700 border border-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-400 dark:border-yellow-800/50 text-[10px] font-bold uppercase tracking-wider"><i class="fa-solid fa-clock"></i> WAITING APPROVAL</span>';
                    } elseif ($status === 'REJECTED') {
                        $html .= '<br><span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-red-50 text-red-700 border border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800/50 text-[10px] font-bold uppercase tracking-wider"><i class="fa-solid fa-times-circle"></i> QC Rejected</span>';
                    }
                    return $html;
                })
                ->addColumn('action', function ($product) {
                    $buttons = '';
                    $status = optional($product->productDetail)->master_checksheet_status ?? 'DRAFT';
                    
                    if ($status === 'DRAFT' || $status === null) {
                        $status = 'WAITING_APPROVAL';
                    }
                    
                    $user = auth()->user();
                    $canApprove = false;
                    if ($user) {
                        $canApprove = $user->hasMenuAccess('master.checksheet_approvals.index', 'approve') || $user->roles->contains('code', 'admin') || $user->roles->contains('code', 'npc_admin') || $user->roles->contains('code', 'qe_mgr') || $user->roles->contains('code', 'qe_spv');
                    }
                    
                    if ($status === 'WAITING_APPROVAL' && $canApprove) {
                        $buttons .= '<a href="' . route('master.checksheet_approvals.show', $product->hashed_id) . '" class="inline-flex px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-medium transition items-center gap-1.5 text-xs shadow-sm mr-2" title="Approve / Review"><i class="fa-solid fa-check"></i> Approve</a>';
                    }

                    if ($product->mappedCheckpoints->isNotEmpty()) {
                        $buttons .= '<a href="' . route('checksheets.setup.preview', $product->hashed_id) . '" target="_blank" class="inline-flex px-3 py-1.5 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white dark:hover:bg-emerald-500 font-medium transition items-center gap-1.5 text-xs shadow-sm border border-emerald-200 dark:border-emerald-800/50 hover:border-transparent mr-2" title="Preview Checksheet"><i class="fa-solid fa-eye"></i> Preview</a>';
                    }
                    return '<div class="flex items-center justify-end gap-2">' . $buttons . '</div>';
                })
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $search = $request->search['value'];
                        $query->where(function ($q) use ($search) {
                            $q->where('part_no', 'like', "%{$search}%")
                              ->orWhere('part_name', 'like', "%{$search}%");
                        });
                    }
                })
                ->rawColumns(['customer', 'model', 'part_no', 'part_name', 'ecn_info', 'mapping_status', 'action'])
                ->make(true);
        }

        $customers = \App\Models\Customer::orderBy('code')->get();
        $models = \App\Models\VehicleModel::whereIn('id', function($q) { $q->selectRaw('MIN(id)')->from('models')->groupBy('name', 'customer_id'); })->orderBy('name')->get();

        return view('master.checksheet_approvals.index', compact('customers', 'models'));
    }

    public function show(\App\Models\Product $product)
    {
        $product->load('mappedCheckpoints.masterCheckpoint', 'productDetail', 'specChildParts.stdPart');
        
        $user = auth()->user();
        $canApprove = false;
        if ($user) {
            $canApprove = $user->hasMenuAccess('master.checksheet_approvals.index', 'approve') || $user->roles->contains('code', 'admin') || $user->roles->contains('code', 'npc_admin') || $user->roles->contains('code', 'qe_mgr') || $user->roles->contains('code', 'qe_spv');
        }

        $materialIds = $product->specChildParts->where('part_type', 'MATERIAL')->pluck('inventory_material_id')->filter()->toArray();
        $inventoryMaterials = [];
        if (!empty($materialIds)) {
            $invMats = \Illuminate\Support\Facades\DB::table('inv_m_material_spec')
                        ->whereIn('id', $materialIds)
                        ->get(['id', 'spec_name'])->keyBy('id');
            foreach ($invMats as $id => $mat) {
                $inventoryMaterials[$id] = $mat->spec_name;
            }
        }

        return view('master.checksheet_approvals.show', compact('product', 'canApprove', 'inventoryMaterials'));
    }

    public function approve(Product $product)
    {
        $userId = auth()->check() ? auth()->user()->getAttribute('id') : null;
        \App\Models\NpcProductDetail::updateOrCreate(
            ['product_id' => $product->id],
            [
                'master_checksheet_status' => 'APPROVED',
                'checksheet_approved_by' => $userId,
                'checksheet_approved_at' => now(),
                'reject_reason' => null,
            ]
        );
        
        activity()
            ->causedBy(auth()->user())
            ->performedOn($product)
            ->event('approved')
            ->log('Part Checksheet Master Approved');

        return back()->with('success', 'Master Checksheet for Part ' . $product->part_no . ' has been approved.');
    }

    public function reject(\Illuminate\Http\Request $request, Product $product)
    {
        $request->validate([
            'reject_reason' => 'required|string|max:1000'
        ]);

        \App\Models\NpcProductDetail::updateOrCreate(
            ['product_id' => $product->id],
            [
                'master_checksheet_status' => 'REJECTED',
                'checksheet_approved_by' => null,
                'checksheet_approved_at' => null,
                'reject_reason' => $request->reject_reason,
            ]
        );
        
        activity()
            ->causedBy(auth()->user())
            ->performedOn($product)
            ->event('rejected')
            ->log('Part Checksheet Master Rejected');

        return back()->with('success', 'Master Checksheet for Part ' . $product->part_no . ' has been rejected.');
    }
}

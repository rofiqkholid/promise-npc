<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NpcEventController;
use App\Http\Controllers\NpcPartController;
use App\Http\Controllers\NpcProcessController;
use App\Http\Controllers\NpcDeliveryTargetController;
use App\Http\Controllers\NpcMasterCheckpointController;
use App\Http\Controllers\NpcMasterDepartmentController;
use App\Http\Controllers\NpcMasterRoutingController;
use App\Http\Controllers\NpcMasterStdPartController;
use App\Http\Controllers\ProductChecksheetSetupController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\NpcInternalCategoryController;
use App\Http\Controllers\NpcCustomerCategoryController;
use App\Http\Controllers\NpcDeliveryGroupController;
use App\Http\Controllers\NpcPartProcessController;
use App\Http\Controllers\ProductionTrackingController;
use App\Http\Controllers\NpcChecksheetController;

Route::get('/check-session-status', function () {
    return response()->json(['active' => Auth::check()]);
})->name('session.check');
// Route for redirecting to Central SSO Portal
Route::get('/login', function () {
    return redirect(config('services.portal_login_url'));
})->name('login');

Route::get('/', function () {
    if (Auth::check() && session()->has('url.intended')) {
        return redirect()->to(session()->pull('url.intended'));
    }
    return redirect()->route('dashboard');
});

Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    return redirect(config('services.portal_login_url'));
})->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Routes
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // System Activity Logs
    Route::get('/activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-logs.index');

    // NPC Events Master Route
    Route::middleware('menu.access')->group(function () {
        Route::get('/events/import/template', [\App\Http\Controllers\NpcEventController::class, 'downloadTemplate'])->name('events.import.template');
        Route::get('/events/import', [\App\Http\Controllers\NpcEventController::class, 'importForm'])->name('events.import');
        Route::post('/events/import', [\App\Http\Controllers\NpcEventController::class, 'importData'])->name('events.import.store');
        Route::post('/events/{event}/save-edit', [\App\Http\Controllers\NpcEventController::class, 'update'])->name('events.update_post');
        Route::resource('events', \App\Http\Controllers\NpcEventController::class);
    });
    Route::resource('events.parts', \App\Http\Controllers\NpcPartController::class)->middleware('menu.access:events.index');

    // Master Data Routes
    Route::prefix('master')->name('master.')->group(function () {
        Route::middleware('menu.access')->group(function () {
            Route::resource('processes', NpcProcessController::class)->except(['show']);
            Route::resource('delivery-targets', NpcDeliveryTargetController::class)->except(['show']);
            Route::resource('checkpoints', NpcMasterCheckpointController::class)->except(['show']);
            Route::get('std-parts/import/template', [\App\Http\Controllers\NpcMasterStdPartController::class, 'downloadTemplate'])->name('std-parts.import.template');
            Route::post('std-parts/import', [\App\Http\Controllers\NpcMasterStdPartController::class, 'importData'])->name('std-parts.import.store');
            Route::resource('std-parts', NpcMasterStdPartController::class)->except(['show']);
            Route::resource('departments', NpcMasterDepartmentController::class)->except(['show']);
            // Menambahkan Routings Route tapi dengan parameter part_id khusus
            Route::get('routings/import/template', [\App\Http\Controllers\NpcMasterRoutingController::class, 'downloadTemplate'])->name('routings.import.template');
            Route::get('routings/import', [\App\Http\Controllers\NpcMasterRoutingController::class, 'importForm'])->name('routings.import');
            Route::post('routings/import', [\App\Http\Controllers\NpcMasterRoutingController::class, 'importData'])->name('routings.import.store');
            Route::resource('routings', \App\Http\Controllers\NpcMasterRoutingController::class)->except(['show']);
            
            // Master Checksheet Mapping based on Product
            Route::get('product-checksheets/import/template', [\App\Http\Controllers\ProductChecksheetSetupController::class, 'downloadTemplate'])->name('checksheets.import.template');
            Route::get('product-checksheets/import', [\App\Http\Controllers\ProductChecksheetSetupController::class, 'importForm'])->name('checksheets.import');
            Route::post('product-checksheets/import', [\App\Http\Controllers\ProductChecksheetSetupController::class, 'importData'])->name('checksheets.import.store');
            Route::get('product-checksheets', [\App\Http\Controllers\ProductChecksheetSetupController::class, 'index'])->name('checksheets.index');
            
            Route::get('master-checksheet-approvals', [\App\Http\Controllers\MasterChecksheetApprovalController::class, 'index'])->name('checksheet_approvals.index');
            Route::get('master-checksheet-approvals/{product}/show', [\App\Http\Controllers\MasterChecksheetApprovalController::class, 'show'])->name('checksheet_approvals.show');
            Route::post('master-checksheet-approvals/{product}/approve', [\App\Http\Controllers\MasterChecksheetApprovalController::class, 'approve'])->name('checksheet_approvals.approve');
            Route::post('master-checksheet-approvals/{product}/reject', [\App\Http\Controllers\MasterChecksheetApprovalController::class, 'reject'])->name('checksheet_approvals.reject');

            Route::resource('internal-categories', \App\Http\Controllers\NpcInternalCategoryController::class)->except(['show']);
            Route::resource('customer-categories', \App\Http\Controllers\NpcCustomerCategoryController::class)->except(['show']);
            Route::resource('delivery-groups', \App\Http\Controllers\NpcDeliveryGroupController::class)->except(['show']);
            Route::resource('menus', \App\Http\Controllers\NpcMenuController::class)->except(['show']);
            Route::resource('roles', \App\Http\Controllers\NpcRoleController::class)->except(['show']);
            Route::resource('promise-users', \App\Http\Controllers\PromiseUserController::class)->except(['show']);
            Route::resource('npc-users', \App\Http\Controllers\NpcUserController::class)->except(['show']);

            // Master Label Image Produk
            Route::get('product-images', [ProductImageController::class, 'index'])->name('product-images.index');
            Route::get('product-images/{product}/edit', [ProductImageController::class, 'edit'])->name('product-images.edit');
            Route::put('product-images/{product}', [ProductImageController::class, 'update'])->name('product-images.update');
            Route::delete('product-images/{product}', [ProductImageController::class, 'destroy'])->name('product-images.destroy');
        });

        Route::post('routings/reorder', [\App\Http\Controllers\NpcMasterRoutingController::class, 'reorder'])
            ->name('routings.reorder')
            ->middleware('menu.access:master.routings.index,update');
    });

    // Dummy API Routes for Dashboard Filters
    Route::prefix('api')->name('api.')->group(function () {
        Route::post('/data/models', function (\Illuminate\Http\Request $request) {
            $models = \App\Models\VehicleModel::where('customer_id', $request->customer_id)
                ->where('status_id', 3) // Only Project status
                ->whereIn('id', function($q) {
                    $q->selectRaw('MIN(id)')->from('models')->groupBy('name', 'customer_id');
                })
                ->orderBy('name')
                ->get(['id', 'name as text']);
            return response()->json(['results' => $models]);
        })->name('data.models');

        Route::post('/data/products', function (\Illuminate\Http\Request $request) {
            $query = \App\Models\Product::with('vehicleModel.customer');
            
            // HANYA tampilkan product yang sudah disetup routing (proses) ATAU checksheet-nya
            if (!$request->boolean('all_products')) {
                $query->where(function($q) {
                    $q->whereIn('id', function($sub) {
                        $sub->select('part_id')->from('npc_master_routings');
                    })->orWhereHas('mappedCheckpoints');
                });
            }
            // Filter by customer_id if provided
            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }

            if ($request->filled('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('part_no', 'like', '%' . $request->search . '%')
                        ->orWhere('part_name', 'like', '%' . $request->search . '%');
                });
            }
            // Tambahkan order by untuk relevansi
            $query->orderBy('part_no', 'asc');

            $products = $query->limit(30)->get();

            // Include default process_name from NpcMasterRouting mapping
            foreach ($products as $prod) {
                $routing = \App\Models\NpcMasterRouting::with('process')
                    ->where('part_id', $prod->id)
                    ->orderBy('sequence_order', 'asc')
                    ->first();

                $prod->process_name = ($routing && $routing->process) ? $routing->process->process_name : null;
                $prod->model_name = $prod->vehicleModel ? $prod->vehicleModel->name : 'N/A';
                $prod->customer_name = ($prod->vehicleModel && $prod->vehicleModel->customer) ? $prod->vehicleModel->customer->code : 'N/A';
            }

            return response()->json(['results' => $products]);
        })->name('data.products');

        Route::post('/data/customers', function () {
            return response()->json(['results' => []]);
        })->name('data.customers');
        Route::get('/data/statuses', function () {
            return response()->json(['results' => []]);
        })->name('data.statuses');

        Route::post('/data/customer-categories', function (\Illuminate\Http\Request $request) {
            $categories = \App\Models\NpcCustomerCategory::where('customer_id', $request->customer_id)->get(['id', 'name as text']);
            return response()->json(['results' => $categories]);
        })->name('data.customer-categories');

        Route::post('/data/inventory-materials', function (\Illuminate\Http\Request $request) {
            $query = \Illuminate\Support\Facades\DB::table('inv_m_material_spec');
            if ($request->filled('search')) {
                $query->where('spec_name', 'like', '%' . $request->search . '%');
            }
            $materials = $query->limit(30)->get();
            $mapped = $materials->map(function($m) {
                return ['id' => $m->id, 'text' => $m->spec_name];
            });
            return response()->json(['results' => $mapped]);
        })->name('data.inventory-materials');

        Route::post('/data/std-parts', function (\Illuminate\Http\Request $request) {
            $query = \App\Models\NpcMasterStdPart::where('is_active', true);
            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('spec', 'like', '%' . $request->search . '%');
            }
            $parts = $query->limit(30)->get(['id', 'name as text']);
            return response()->json(['results' => $parts]);
        })->name('data.std-parts');
    });

    // Part Routing Routes
    Route::get('/parts/{part}/routing', [NpcPartProcessController::class, 'edit'])->name('parts.routing.edit')->middleware('menu.access:master.routings.index,update');
    Route::post('/parts/{part}/routing', [NpcPartProcessController::class, 'update'])->name('parts.routing.update')->middleware('menu.access:master.routings.index,update');

    // Production Tracking Route
    Route::middleware('menu.access')->group(function () {
        Route::get('/tracking', [ProductionTrackingController::class, 'index'])->name('tracking.index');
        Route::get('/tracking/setup', [ProductionTrackingController::class, 'setup'])->name('tracking.setup');
        Route::get('/tracking/production', [ProductionTrackingController::class, 'production'])->name('tracking.production');
        Route::get('/tracking/qc', [ProductionTrackingController::class, 'qc'])->name('tracking.qc');
        Route::get('/tracking/mgm', [ProductionTrackingController::class, 'mgm'])->name('tracking.mgm');
        Route::get('/tracking/stock', [ProductionTrackingController::class, 'stock'])->name('tracking.stock');
        Route::get('/tracking/history', [ProductionTrackingController::class, 'history'])->name('tracking.history');
    });

    // Status update and action routes
    Route::post('/tracking/{part}/status', [\App\Http\Controllers\ProductionTrackingController::class, 'updateStatus'])->name('tracking.status.update')->middleware('menu.access:tracking.production,update');
    Route::post('/tracking/{part}/setup-rollback', [\App\Http\Controllers\ProductionTrackingController::class, 'rollbackSetup'])->name('tracking.setup.rollback')->middleware('menu.access:tracking.setup,update');
    Route::post('/tracking/{part}/process-complete', [\App\Http\Controllers\ProductionTrackingController::class, 'completeProcess'])->name('tracking.process.complete')->middleware('menu.access:tracking.production,update');
    Route::post('/tracking/{part}/process-rollback', [\App\Http\Controllers\ProductionTrackingController::class, 'rollbackProcess'])->name('tracking.process.rollback')->middleware('menu.access:tracking.production,update');
    Route::post('/tracking/{part}/qc-rollback', [\App\Http\Controllers\ProductionTrackingController::class, 'rollbackQc'])->name('tracking.qc.rollback')->middleware('menu.access:tracking.qc,update');
    Route::post('/tracking/{part}/mgm-rollback', [\App\Http\Controllers\ProductionTrackingController::class, 'rollbackMgm'])->name('tracking.mgm.rollback')->middleware('menu.access:tracking.mgm,update');
    Route::post('/tracking/{part}/deliver', [\App\Http\Controllers\ProductionTrackingController::class, 'deliver'])->name('tracking.deliver')->middleware('menu.access:tracking.stock,update');
    Route::post('/parts/{part}/apply-ecn', [\App\Http\Controllers\NpcPartController::class, 'applyEcn'])->name('parts.apply-ecn')->middleware('menu.access:events.index,update');

    // Quality Checksheet Routes
    Route::get('/tracking/products/{product}/checksheet-setup', [ProductChecksheetSetupController::class, 'edit'])->name('checksheets.setup.edit')->middleware('menu.access:master.checksheets.index,update');
    Route::post('/tracking/products/{product}/checksheet-setup', [ProductChecksheetSetupController::class, 'update'])->name('checksheets.setup.update')->middleware('menu.access:master.checksheets.index,update');
    Route::get('/tracking/products/{product}/checksheet-setup/preview', [ProductChecksheetSetupController::class, 'preview'])->name('checksheets.setup.preview')->middleware('menu.access:master.checksheets.index,view');
    
    Route::get('/tracking/{part}/checksheet/create', [NpcChecksheetController::class, 'create'])->name('checksheets.create')->middleware('menu.access:tracking.qc,create');
    Route::post('/tracking/bulk-print-labels', [NpcChecksheetController::class, 'bulkPrintLabel'])->name('checksheets.bulk-print-labels')->middleware('menu.access:tracking.qc|tracking.stock,create');
    Route::get('/tracking/{part}/print-label', [NpcChecksheetController::class, 'printLabel'])->name('checksheets.print-label')->middleware('menu.access:tracking.qc|tracking.stock,create');
    Route::get('/checksheets/{checksheet}/preview', [NpcChecksheetController::class, 'preview'])->name('checksheets.preview')->middleware('menu.access:tracking.qc,view');
    Route::get('/checksheets/{checksheet}/export', [NpcChecksheetController::class, 'export'])->name('checksheets.export')->middleware('menu.access:tracking.qc,view');
    Route::get('/checksheets/{checksheet}/edit', [NpcChecksheetController::class, 'edit'])->name('checksheets.edit')->middleware('menu.access:tracking.qc,update');
    Route::post('/checksheets/{checksheet}/sync', [NpcChecksheetController::class, 'sync'])->name('checksheets.sync')->middleware('menu.access:tracking.qc,update');
    Route::post('/checksheets/{checksheet}', [NpcChecksheetController::class, 'update'])->name('checksheets.update')->middleware('menu.access:tracking.qc,update');

    // Checksheet Approval Routes
    Route::middleware('menu.access')->group(function () {
        Route::get('/checksheet-approvals', [\App\Http\Controllers\NpcChecksheetApprovalController::class, 'index'])->name('checksheet-approvals.index');
        Route::get('/checksheet-approvals/{checksheet}', [\App\Http\Controllers\NpcChecksheetApprovalController::class, 'show'])->name('checksheet-approvals.show');
        Route::post('/checksheet-approvals/{checksheet}', [\App\Http\Controllers\NpcChecksheetApprovalController::class, 'store'])->name('checksheet-approvals.store');
    });
});

// Fallback route to serve storage files directly for environments where symlink is broken or restricted
Route::get('file/storage/{path}', function ($path) {
    $possiblePaths = [
        storage_path('app/public/' . $path),
        storage_path('app/private/public/' . $path),
        storage_path('app/public/public/' . $path),
    ];

    foreach ($possiblePaths as $fullPath) {
        if (file_exists($fullPath)) {
            return response()->file($fullPath);
        }
    }

    abort(404);
})->where('path', '.*');

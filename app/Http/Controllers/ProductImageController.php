<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\NpcProductDetail;
use App\Models\Customer;
use App\Models\VehicleModel;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    /**
     * List all products with label image status.
     */
    public function index(Request $request)
    {
        $query = Product::with(['customer', 'vehicleModel', 'productDetail', 'docPackage.currentRevision', 'siblings.docPackage.currentRevision'])
            ->where('products.is_delete', 0)
            ->leftJoin('npc_product_details', 'products.id', '=', 'npc_product_details.product_id')
            ->select('products.*')
            ->orderByRaw('CASE WHEN npc_product_details.label_image_path IS NOT NULL THEN 1 ELSE 0 END DESC')
            ->orderBy('npc_product_details.updated_at', 'desc')
            ->orderBy('products.part_no', 'asc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('products.part_no', 'like', "%{$search}%")
                  ->orWhere('products.part_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('customer_id')) {
            $query->where('products.customer_id', $request->customer_id);
        }

        if ($request->filled('model_id')) {
            $query->where('products.model_id', $request->model_id);
        }

        if ($request->filled('has_image')) {
            if ($request->has_image === 'yes') {
                $query->whereHas('productDetail', fn($q) => $q->whereNotNull('label_image_path'));
            } elseif ($request->has_image === 'no') {
                $query->where(function ($q) {
                    $q->whereDoesntHave('productDetail')
                      ->orWhereHas('productDetail', fn($s) => $s->whereNull('label_image_path'));
                });
            }
        }

        $products  = $query->paginate(10)->withQueryString();
        $customers = Customer::orderBy('code')->get();
        $models    = VehicleModel::whereIn('id', function($q) { $q->selectRaw('MIN(id)')->from('models')->groupBy('name', 'customer_id'); })->orderBy('name')->get();

        return view('master.product_images.index', compact('products', 'customers', 'models'));
    }

    /**
     * Show upload form for a specific product.
     */
    public function edit(Product $product)
    {
        $product->load('productDetail', 'customer', 'vehicleModel');
        return view('master.product_images.edit', compact('product'));
    }

    /**
     * Save the uploaded label image.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'label_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        // Hapus gambar lama jika ada
        if ($product->productDetail && $product->productDetail->label_image_path) {
            Storage::disk('public')->delete(
                ltrim(str_replace('public/', '', $product->productDetail->label_image_path), '/')
            );
        }

        $file     = $request->file('label_image');
        $filename = time() . '_label_' . $file->getClientOriginalName();
        // Store on 'public' disk → path will be 'labels/images/filename' (no 'public/' prefix)
        $path     = $file->storeAs('labels/images', $filename, 'public');

        NpcProductDetail::updateOrCreate(
            ['product_id' => $product->id],
            ['label_image_path' => $path]
        );

        return redirect()
            ->route('master.product-images.index', request()->query())
            ->with('success', 'Label image for Part ' . $product->part_no . ' has been saved successfully.');
    }

    /**
     * Delete label image.
     */
    public function destroy(Product $product)
    {
        if ($product->productDetail && $product->productDetail->label_image_path) {
            Storage::disk('public')->delete(
                ltrim(str_replace('public/', '', $product->productDetail->label_image_path), '/')
            );
            $product->productDetail->update(['label_image_path' => null]);
        }

        return redirect()
            ->route('master.product-images.index', request()->query())
            ->with('success', 'Label image for Part ' . $product->part_no . ' has been deleted.');
    }
}

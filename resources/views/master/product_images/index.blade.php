@extends('layouts.app')

@section('title', 'Master Product Label Images')
@section('page_title', 'Master Data / Product Label Images')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">

    {{-- Header --}}
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
            <div class="flex-1">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-image text-emerald-500"></i> Master Product Label Images
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 pl-7">
                    Manage product photos that will be displayed on QC labels when printed.
                </p>
            </div>
        </div>

        {{-- Filter form --}}
        <form id="filterForm" method="GET" action="{{ route('master.product-images.index') }}" class="w-full">
            <div class="flex flex-wrap gap-3 items-end justify-between">
                <div class="flex flex-wrap gap-3 flex-1">

                    <div class="w-full sm:w-40">
                        <select name="customer_id" class="w-full py-2 pl-3 pr-10 bg-white text-sm border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all rounded-none" onchange="document.getElementById('filterForm').submit()">
                            <option value="">All Customers</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->code }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full sm:w-40">
                        <select name="model_id" class="w-full py-2 pl-3 pr-10 bg-white text-sm border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all rounded-none" onchange="document.getElementById('filterForm').submit()">
                            <option value="">All Models</option>
                            @foreach($models as $model)
                                <option value="{{ $model->id }}" {{ request('model_id') == $model->id ? 'selected' : '' }}>
                                    {{ $model->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full sm:w-44">
                        <select name="has_image" class="w-full py-2 pl-3 pr-10 bg-white text-sm border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all rounded-none" onchange="document.getElementById('filterForm').submit()">
                            <option value="">All Image Status</option>
                            <option value="yes" {{ request('has_image') === 'yes' ? 'selected' : '' }}>Have Label Image</option>
                            <option value="no"  {{ request('has_image') === 'no'  ? 'selected' : '' }}>Not Have Label Image</option>
                        </select>
                    </div>

                    @if(request('search') || request('customer_id') || request('model_id') || request('has_image'))
                        <a href="{{ route('master.product-images.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 transition text-sm flex items-center gap-2 shadow-sm rounded-none">
                            <i class="fa-solid fa-xmark"></i> Clear
                        </a>
                    @endif
                </div>

                {{-- Search --}}
                <div class="w-full sm:w-64 lg:w-80">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-search text-gray-400"></i>
                        </div>
                        <input type="text" id="searchInput" name="search" value="{{ request('search') }}"
                            class="w-full pr-4 py-2 bg-white text-sm border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all placeholder-gray-400 rounded-none"
                            style="padding-left: 2.5rem;"
                            placeholder="Search Part No / Name...">
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="px-6 py-3 bg-emerald-50 dark:bg-emerald-900/20 border-b border-emerald-200 dark:border-emerald-800 flex items-center gap-2 text-emerald-700 dark:text-emerald-400 text-sm">
        <i class="fa-solid fa-circle-check"></i>
        {{ session('success') }}
    </div>
    @endif

    {{-- Table --}}
    <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-6 py-4 font-semibold w-12 text-center">No</th>
                        <th class="px-6 py-4 font-semibold">Customer</th>
                        <th class="px-6 py-4 font-semibold">Model</th>
                        <th class="px-6 py-4 font-semibold">Part No</th>
                        <th class="px-6 py-4 font-semibold">Part Name</th>
                        <th class="px-6 py-4 font-semibold text-center">Label Image</th>
                        <th class="px-6 py-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($products as $index => $product)
                    <tr class="bg-white dark:bg-gray-800 hover:bg-emerald-50/30 dark:hover:bg-gray-700/30 transition group text-sm">
                        <td class="px-6 py-4 text-center text-gray-500">{{ $products->firstItem() + $index }}</td>
                        <td class="px-6 py-4 font-bold text-gray-800 dark:text-gray-200">
                            {{ optional($product->customer)->code ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                            {{ optional($product->vehicleModel)->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-blue-600 dark:text-blue-400 font-bold">{{ $product->part_no }}</div>
                        </td>
                        <td class="px-6 py-4 text-gray-800 dark:text-gray-200">{{ $product->part_name }}</td>
                        <td class="px-6 py-4 text-center">
                            @if($product->productDetail && $product->productDetail->label_image_path)
                                <div class="flex flex-col items-center gap-1">
                                    <img src="{{ Storage::url($product->productDetail->label_image_path) }}"
                                         alt="Label"
                                         class="h-12 w-auto object-contain mx-auto border border-gray-200 rounded shadow-sm">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 text-[10px] font-bold uppercase rounded-full border border-emerald-200 dark:border-emerald-800">
                                        <i class="fa-solid fa-check"></i> Has Image
                                    </span>
                                </div>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 text-[10px] font-bold uppercase rounded-full border border-gray-200 dark:border-gray-600">
                                    <i class="fa-solid fa-minus"></i> No Image
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('master.product-images.edit', $product->hashed_id) }}"
                                   class="inline-flex px-3 py-1.5 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white font-medium transition items-center gap-1.5 text-xs shadow-sm border border-emerald-200 dark:border-emerald-800/50 hover:border-transparent">
                                    <i class="fa-solid fa-upload"></i>
                                    {{ $product->productDetail && $product->productDetail->label_image_path ? 'Change Image' : 'Upload Image' }}
                                </a>

                                @if($product->productDetail && $product->productDetail->label_image_path)
                                <form method="POST" action="{{ route('master.product-images.destroy', $product->hashed_id) }}"
                                      onsubmit="return confirm('Delete label image for Part {{ $product->part_no }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex px-3 py-1.5 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-600 hover:text-white font-medium transition items-center gap-1.5 text-xs shadow-sm border border-red-200 dark:border-red-800/50 hover:border-transparent">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i class="fa-regular fa-image text-4xl text-gray-300 dark:text-gray-600"></i>
                                <p>No product data found.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
        {{ $products->links() }}
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        let typingTimer;
        const doneTypingInterval = 500;
        const $input = $('#searchInput');
        const $form  = $('#filterForm');

        $input.on('keyup', function () {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => $form.submit(), doneTypingInterval);
        });
        $input.on('keydown', function () { clearTimeout(typingTimer); });
    });
</script>
@endsection

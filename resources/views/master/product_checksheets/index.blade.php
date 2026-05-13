@extends('layouts.app')

@section('title', 'Master Checksheet Part')
@section('page_title', 'Master Data / Master Checksheet Part')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
            <div class="flex-1">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-blue-500"></i> Master Checksheet per Part
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 pl-7">Manage QC checkpoint specifications for each part number.</p>
            </div>
        </div>

        <form id="searchForm" method="GET" action="{{ route('master.checksheets.index') }}" class="w-full">
            <div class="flex flex-wrap gap-3 items-end justify-between">
                
                <!-- Left Side: Dropdown Filters -->
                <div class="flex flex-wrap gap-3 flex-1">
                    <div class="w-full sm:w-40">
                        <select name="customer_id" class="w-full py-2 pl-3 pr-10 bg-white text-sm border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all rounded-none" onchange="document.getElementById('searchForm').submit()">
                            <option value="">All Customers</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->code }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="w-full sm:w-40">
                        <select name="model_id" class="w-full py-2 pl-3 pr-10 bg-white text-sm border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all rounded-none" onchange="document.getElementById('searchForm').submit()">
                            <option value="">All Models</option>
                            @foreach($models as $model)
                                <option value="{{ $model->id }}" {{ request('model_id') == $model->id ? 'selected' : '' }}>
                                    {{ $model->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full sm:w-40">
                        <select name="status" class="w-full py-2 pl-3 pr-10 bg-white text-sm border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all rounded-none" onchange="document.getElementById('searchForm').submit()">
                            <option value="">All Statuses</option>
                            <option value="mapped" {{ request('status') == 'mapped' ? 'selected' : '' }}>Mapped</option>
                            <option value="unmapped" {{ request('status') == 'unmapped' ? 'selected' : '' }}>Unmapped</option>
                        </select>
                    </div>
                    
                    @if(request('search') || request('customer_id') || request('model_id') || request('status'))
                        <a href="{{ route('master.checksheets.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 transition text-sm flex items-center gap-2 shadow-sm rounded-none" title="Clear Filters">
                            <i class="fa-solid fa-xmark"></i> Clear
                        </a>
                    @endif
                </div>

                <!-- Right Side: Search Input -->
                <div class="w-full sm:w-64 lg:w-80">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-search text-gray-400"></i>
                        </div>
                        <input type="text" id="searchInput" name="search" value="{{ request('search') }}" 
                            class="w-full pr-4 py-2 bg-white text-sm border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-gray-400 rounded-none" 
                            style="padding-left: 2.5rem;"
                            placeholder="Search Part No / Name...">
                    </div>
                </div>

            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="p-6">
        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-12 text-center">No</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Customer</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Model</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Part No</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Name Part</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Mapping Status</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($products as $index => $product)
                    <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-blue-50/50 dark:hover:bg-gray-700/30 transition group text-sm">
                        <td class="px-6 py-4 text-center text-gray-500">{{ $products->firstItem() + $index }}</td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                {{ optional($product->customer)->code ?? '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                {{ optional($product->vehicleModel)->name ?? '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-blue-600 dark:text-blue-400 font-bold text-sm">{{ $product->part_no }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-gray-800 dark:text-gray-200 font-bold">{{ $product->part_name }}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($product->mappedCheckpoints->isNotEmpty())
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-50 text-green-700 border border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800/50 text-[10px] font-bold uppercase tracking-wider">
                                    <i class="fa-solid fa-check-circle"></i> Mapped ({{ $product->mappedCheckpoints->count() }} Points)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-100 text-gray-600 border border-gray-200 dark:bg-gray-700 dark:text-gray-400 dark:border-gray-600 text-[10px] font-bold uppercase tracking-wider">
                                    <i class="fa-solid fa-minus"></i> Unmapped
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right align-middle">
                            <a href="{{ route('checksheets.setup.edit', $product->hashed_id) }}" class="inline-flex px-3 py-1.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 hover:bg-blue-600 hover:text-white dark:hover:bg-blue-500 font-medium transition items-center gap-1.5 text-xs shadow-sm border border-blue-200 dark:border-blue-800/50 hover:border-transparent">
                                <i class="fa-solid fa-pencil"></i> Mapping Checksheet
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i class="fa-regular fa-folder-open text-4xl text-gray-300 dark:text-gray-600"></i>
                                <p>No part data found.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($products->hasPages())
    <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        {{ $products->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        let typingTimer;
        const doneTypingInterval = 500; // time in ms, 500ms delay
        const $input = $('#searchInput');
        const $form = $('#searchForm');

        // on keyup, start the countdown
        $input.on('keyup', function () {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(doneTyping, doneTypingInterval);
        });

        // on keydown, clear the countdown 
        $input.on('keydown', function () {
            clearTimeout(typingTimer);
        });

        // user is "finished typing," do something
        function doneTyping () {
            $form.submit();
        }
    });
</script>
@endsection

@extends('layouts.app')

@section('title', 'Part Detailss Event')
@section('page_title', 'Master Data / Event / Parts')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-circle-info text-blue-500"></i> Information Event
        </h2>
    </div>
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <span class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase text-gray-500">Event Name</span>
            <span class="block text-sm font-medium text-gray-800 dark:text-gray-200">{{ optional($event->customerCategory)->name ?? '-' }}</span>
        </div>
        <div>
            <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Customer</span>
            <span class="block text-sm font-medium text-gray-800 dark:text-gray-200">{{ optional(optional($event->customerCategory)->customer)->code ?? '-' }}</span>
        </div>
        <div>
            <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Model</span>
            <span class="block text-sm font-medium text-gray-800 dark:text-gray-200">{{ optional(optional(optional($event->parts->first())->product)->vehicleModel)->name ?? '-' }}</span>
        </div>
        <div>
            <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Delivery To</span>
            <span class="block text-sm font-medium text-gray-800 dark:text-gray-200">{{ $event->delivery_to ?? '-' }}</span>
        </div>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-cubes text-blue-500"></i> Part List / Item
        </h2>
        <div class="flex gap-2">
            <a href="{{ route('events.index') }}" class="px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition font-medium text-sm flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
            <a href="{{ route('events.parts.create', $event->hashed_id) }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white hover:from-blue-700 hover:to-cyan-700 transition shadow-md shadow-blue-500/20 font-medium text-sm flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Add Part
            </a>
        </div>
    </div>

    <div class="p-6">
        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-16">#</th>
                        <th scope="col" class="px-6 py-4 font-semibold">PO No</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Part No</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Part Name</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Qty</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Delv Date</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Process</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Dept</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Status</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right w-32">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($parts as $index => $part)
                    <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-blue-50/50 dark:hover:bg-gray-700/30 transition group">
                        <td class="px-6 py-4 text-slate-800 dark:text-slate-200 text-sm">{{ $parts->firstItem() + $index }}</td>
                        <td class="px-6 py-4 text-slate-800 dark:text-slate-200 font-medium text-sm">{{ optional($part->event)->po_no }}</td>
                        <td class="px-6 py-4 text-blue-600 dark:text-blue-400 text-sm font-semibold">{{ optional($part->product)->part_no }}</td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400 text-sm">{{ optional($part->product)->part_name }}</td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400 text-sm font-medium">{{ $part->qty }}</td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400 text-sm font-medium">{{ \Carbon\Carbon::parse($part->delivery_date)->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-slate-700 dark:text-slate-300 text-sm">
                            @php
                                $processLabel = match($part->status) {
                                    'PO_REGISTERED'       => ['label' => 'Registrasi', 'color' => 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400'],
                                    'WAITING_DEPT_CONFIRM'=> ['label' => 'Production', 'color' => 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300'],
                                    'WAITING_QE_CHECK'    => ['label' => 'Quality Check', 'color' => 'bg-orange-100 dark:bg-orange-900/40 text-orange-700 dark:text-orange-300'],
                                    'WAITING_MGM_CHECK'   => ['label' => 'Mgm Review', 'color' => 'bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300'],
                                    'FINISHED'            => ['label' => 'Done', 'color' => 'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300'],
                                    default               => ['label' => $part->process ?: '-', 'color' => 'bg-slate-100 dark:bg-slate-700 text-slate-400'],
                                };
                            @endphp
                            <span class="p-1 px-2 text-xs font-medium {{ $processLabel['color'] }}">{{ $processLabel['label'] }}</span>
                        </td>
                        <td class="px-6 py-4 text-slate-700 dark:text-slate-300 text-sm font-semibold">
                            @php
                                $deptLabel = match($part->status) {
                                    'PO_REGISTERED'        => '-',
                                    'WAITING_DEPT_CONFIRM' => $part->department ?: 'Dept',
                                    'WAITING_QE_CHECK'     => 'QE / QC',
                                    'WAITING_MGM_CHECK'    => 'Management',
                                    'FINISHED'             => 'Done',
                                    default                => $part->department ?: '-',
                                };
                            @endphp
                            @if($deptLabel === '-')
                                <span class="text-slate-300 dark:text-slate-600 text-xs italic">—</span>
                            @else
                                <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ $deptLabel }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($part->status === 'WAITING_DEPT_CONFIRM')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium">WAITING DEPT</span>
                            @elseif($part->status === 'WAITING_QE_CHECK')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-orange-100 text-orange-800 text-xs font-medium">WAITING QE</span>
                            @elseif($part->status === 'WAITING_MGM_CHECK')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-purple-100 text-purple-800 text-xs font-medium">WAITING MGM</span>
                            @elseif($part->status === 'FINISHED')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-100 text-green-800 text-xs font-medium">FINISHED</span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-100 text-gray-800 text-xs font-medium">{{ $part->status }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1 opacity-50 group-hover:opacity-100 transition">
                                <a href="{{ route('events.parts.edit', [$event->hashed_id, $part->hashed_id]) }}" class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 p-2 transition" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('events.parts.destroy', [$event->hashed_id, $part->hashed_id]) }}" method="POST" class="inline" onsubmit="confirmAction(event, 'Are you sure you want to delete this data?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-2 transition" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="p-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i class="fa-regular fa-folder-open text-4xl text-gray-300 dark:text-gray-600"></i>
                                <p>No parts registered for this event yet.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($parts->hasPages())
    <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        {{ $parts->links() }}
    </div>
    @endif
</div>
@endsection


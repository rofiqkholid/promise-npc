@extends('layouts.app')

@section('title', 'Master Data Event')
@section('page_title', 'Data Event')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-calendar-check text-blue-500"></i> Register Project Event
        </h2>
        <div class="flex items-center gap-3">
            <a href="{{ route('events.import') }}" class="px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition shadow-sm font-medium text-sm flex items-center gap-2">
                <i class="fa-solid fa-file-excel text-green-600"></i> Import Excel
            </a>
            <a href="{{ route('events.create') }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-lg hover:from-blue-700 hover:to-cyan-700 transition shadow-md shadow-blue-500/20 font-medium text-sm flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Add Event
            </a>
        </div>
    </div>

    <div class="p-6">
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-16">#</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Event Name</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Customer</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Model</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Category</th>
                        <th scope="col" class="px-6 py-4 font-semibold">GR</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Delivery To</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right w-32">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($events as $index => $event)
                    <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-blue-50/50 dark:hover:bg-gray-700/30 transition group">
                        <td class="px-6 py-4 text-slate-800 dark:text-slate-200 text-sm">{{ $events->firstItem() + $index }}</td>
                        <td class="px-6 py-4 text-blue-900 dark:text-blue-400 font-semibold text-sm">{{ optional($event->customerCategory)->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400 text-sm">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 font-medium">
                                {{ optional(optional($event->customerCategory)->customer)->code ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400 text-sm font-medium">{{ optional(optional(optional($event->parts->first())->product)->vehicleModel)->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400 text-sm">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ optional($event->customerCategory)->name ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400 text-sm font-medium">{{ optional($event->deliveryGroup)->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400 text-sm font-medium">{{ $event->delivery_to ?? '-' }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1 opacity-50 group-hover:opacity-100 transition">
                                <a href="{{ route('events.parts.index', $event->hashed_id) }}" class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 p-2 rounded-md transition" title="Parts List">
                                    <i class="fa-solid fa-list-check"></i>
                                </a>
                                <a href="{{ route('events.edit', $event->hashed_id) }}" class="text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 p-2 rounded-md transition" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('events.destroy', $event->hashed_id) }}" method="POST" class="inline" onsubmit="confirmAction(event, 'Are you sure you want to delete this data?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-2 rounded-md transition" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i class="fa-regular fa-folder-open text-4xl text-gray-300 dark:text-gray-600"></i>
                                <p>No event data registered yet.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($events->hasPages())
    <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        {{ $events->links() }}
    </div>
    @endif
</div>
@endsection


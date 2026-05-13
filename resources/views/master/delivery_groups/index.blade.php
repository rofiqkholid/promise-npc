@extends('layouts.app')

@section('title', 'Master Delivery Group')
@section('page_title', 'Master Data / Delivery Group')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow border border-gray-200 dark:border-gray-700">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-layer-group text-blue-500"></i> Register Delivery Group
        </h2>
        <a href="{{ route('master.delivery-groups.create') }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white hover:from-blue-700 hover:to-cyan-700 transition shadow-md shadow-blue-500/20 font-medium text-sm flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Add Grup
        </a>
    </div>

    <div class="p-6">
        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-16">No</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Name Delivery Group (Example: GR1, GR2)</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right w-24">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($groups as $i => $group)
                    <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-blue-50/50 dark:hover:bg-gray-700/30 transition group">
                        <td class="px-6 py-4 font-medium text-slate-500 dark:text-slate-400">{{ $i + 1 }}</td>
                        <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">{{ $group->name }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-1 opacity-50 group-hover:opacity-100 transition">
                                <a href="{{ route('master.delivery-groups.edit', $group->hashed_id) }}" class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 p-2 transition" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('master.delivery-groups.destroy', $group->hashed_id) }}" method="POST" class="inline" onsubmit="confirmAction(event, 'Delete grup ini secara permanen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-2 transition" title="Delete">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="p-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i class="fa-regular fa-folder-open text-4xl text-gray-300 dark:text-gray-600"></i>
                                <p>No delivery group data registered yet.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection


@extends('layouts.app')

@section('title', 'Master STD Parts')
@section('page_title', 'Master Data / STD Parts')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow border border-gray-200 dark:border-gray-700">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-screwdriver-wrench text-blue-500"></i> Master STD Part Register
        </h2>
        <a href="{{ route('master.std-parts.create') }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white hover:from-blue-700 hover:to-cyan-700 transition shadow-md shadow-blue-500/20 font-medium text-sm flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Add New STD Part
        </a>
    </div>

    <div class="p-6">
        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-16 text-center">No</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Name</th>
                        <th scope="col" class="px-6 py-4 font-semibold w-24">Status</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right w-24">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stdParts as $part)
                    <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-blue-50/50 dark:hover:bg-gray-700/30 transition group">
                        <td class="px-6 py-4 font-bold text-center text-indigo-600 dark:text-indigo-400">{{ ($stdParts->currentPage() - 1) * $stdParts->perPage() + $loop->iteration }}</td>
                        <td class="px-6 py-4 font-semibold text-slate-900 dark:text-white">{{ $part->name }}</td>
                        <td class="px-6 py-4">
                            @if($part->is_active)
                                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 border border-green-200 dark:border-green-800">
                                    Active
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-1 opacity-50 group-hover:opacity-100 transition">
                                <a href="{{ route('master.std-parts.edit', $part->hashed_id) }}" class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 p-2 transition" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('master.std-parts.destroy', $part->hashed_id) }}" method="POST" class="inline" onsubmit="confirmAction(event, 'Permanently delete this STD part?')">
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
                        <td colspan="5" class="p-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i class="fa-regular fa-folder-open text-4xl text-gray-300 dark:text-gray-600"></i>
                                <p>No STD parts registered yet.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $stdParts->links() }}
        </div>
    </div>
</div>
@endsection

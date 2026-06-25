@props(['editUrl' => null, 'deleteUrl' => null, 'deleteMessage' => 'Permanently delete this record?', 'extraButtons' => null])

<div class="flex justify-end gap-1 opacity-50 group-hover:opacity-100 transition">
    @if($extraButtons)
        {!! $extraButtons !!}
    @endif

    @if($editUrl)
        <a href="{{ $editUrl }}" class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 p-2 transition" title="Edit">
            <i class="fa-solid fa-pen-to-square"></i>
        </a>
    @endif

    @if($deleteUrl)
        <form action="{{ $deleteUrl }}" method="POST" class="inline" onsubmit="confirmAction(event, '{{ addslashes($deleteMessage) }}')">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-2 transition" title="Delete">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        </form>
    @endif
</div>

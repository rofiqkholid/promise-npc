@php
    $earliestDelivery = $po->parts->min('delivery_date');
    $totalParts = $po->parts->count();
    $closedCount = $po->parts->where('status', 'CLOSED')->count();
@endphp
@if($earliestDelivery)
    <div class="text-xs {{ \Carbon\Carbon::parse($earliestDelivery)->endOfDay()->isPast() && $closedCount !== $totalParts ? 'text-red-500 font-bold' : 'text-gray-600 font-medium' }}">
        <i class="fa-regular fa-calendar-alt md:mr-1"></i> {{ \Carbon\Carbon::parse($earliestDelivery)->format('d M y') }}
    </div>
@else
    - 
@endif

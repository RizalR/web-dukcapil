@props(['supplyStatus'])

@php
    $badgeClasses = match ($supplyStatus->status) {
        \App\SupplyLevel::Available => 'bg-emerald-100 text-emerald-800',
        \App\SupplyLevel::Limited => 'bg-amber-100 text-amber-800',
        \App\SupplyLevel::Unavailable => 'bg-rose-100 text-rose-800',
    };
@endphp

<article class="card p-5 sm:p-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-lg font-bold text-ink">{{ $supplyStatus->item }}</h3>
            <p class="mt-1 text-sm text-slate-600">{{ $supplyStatus->location }}</p>
        </div>
        <span class="rounded-full px-3 py-1 text-sm font-bold {{ $badgeClasses }}">{{ $supplyStatus->status->label() }}</span>
    </div>

    @if($supplyStatus->note)
        <p class="mt-4 text-sm leading-6 text-slate-700">{{ $supplyStatus->note }}</p>
    @endif

    @if($supplyStatus->checked_at)
        <p class="mt-3 text-xs text-slate-500">Diperiksa {{ $supplyStatus->checked_at->translatedFormat('d F Y, H.i') }} WITA · {{ $supplyStatus->source }}</p>
    @else
        <p class="mt-3 text-xs text-slate-500">Menunggu pembaruan dari petugas.</p>
    @endif
</article>

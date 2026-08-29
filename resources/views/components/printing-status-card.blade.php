@props(['printingStatus'])

@php
    $badgeClasses = $printingStatus->checked_at === null
        ? 'bg-slate-100 text-slate-700'
        : ($printingStatus->is_available ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800');
@endphp

<article class="card p-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-lg font-bold text-ink">{{ $printingStatus->document->label() }}</h3>
            <p class="mt-1 text-sm text-slate-600">{{ $printingStatus->location }}</p>
        </div>
        <span class="rounded-full px-3 py-1 text-sm font-bold {{ $badgeClasses }}">{{ $printingStatus->availabilityLabel() }}</span>
    </div>

    @if($printingStatus->note)
        <p class="mt-4 text-sm leading-6 text-slate-700">{{ $printingStatus->note }}</p>
    @endif

    @if($printingStatus->checked_at)
        <p class="mt-3 text-xs text-slate-500">Diperiksa {{ $printingStatus->checked_at->translatedFormat('d F Y, H.i') }} WITA · {{ $printingStatus->source }}</p>
    @else
        <p class="mt-3 text-xs text-slate-500">Menunggu pembaruan dari petugas.</p>
    @endif
</article>

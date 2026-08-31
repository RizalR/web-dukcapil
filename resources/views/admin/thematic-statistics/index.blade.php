@extends('layouts.admin')

@section('title', 'Statistik Tematik')

@section('content')
<div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
    <div>
        <p class="eyebrow">Data tematik</p>
        <h1 class="page-title mt-2">Versi statistik tematik</h1>
        <p class="mt-3 max-w-3xl text-slate-600">Kelola pembaruan APAK, pendidikan menurut umur tunggal, Akta Kelahiran, Akta Kematian, dan KIA. Setiap impor disimpan sebagai versi baru.</p>
    </div>
    <a href="{{ route('admin.thematic-statistic-imports.create') }}" class="btn-primary">Unggah workbook</a>
</div>

<div class="card mt-8 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-4">Dataset</th>
                    <th class="px-5 py-4">Periode</th>
                    <th class="px-5 py-4">Isi</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4">Pembaruan</th>
                    <th class="px-5 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($datasets as $dataset)
                    @php
                        $statusClasses = match($dataset->status) {
                            \App\ThematicStatisticDatasetStatus::Published => 'bg-emerald-100 text-emerald-800',
                            \App\ThematicStatisticDatasetStatus::Superseded => 'bg-slate-200 text-slate-700',
                            default => 'bg-amber-100 text-amber-800',
                        };
                    @endphp
                    <tr>
                        <td class="px-5 py-4">
                            <span class="block font-bold text-ink">{{ $dataset->type->title() }}</span>
                            <span class="mt-1 block max-w-xs break-all text-xs text-slate-500">{{ $dataset->original_filename }}</span>
                        </td>
                        <td class="px-5 py-4">{{ $dataset->period }}</td>
                        <td class="px-5 py-4">
                            <span class="block">{{ number_format($dataset->observation_count, 0, ',', '.') }} observasi</span>
                            <span class="text-xs text-slate-500">{{ number_format($dataset->row_count, 0, ',', '.') }} baris publik</span>
                        </td>
                        <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-xs font-bold {{ $statusClasses }}">{{ $dataset->status->label() }}</span></td>
                        <td class="px-5 py-4">
                            <span class="block">{{ $dataset->created_at->translatedFormat('d M Y, H.i') }}</span>
                            <span class="text-xs text-slate-500">{{ $dataset->importedBy?->name ?? 'Sistem' }}</span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            @if($dataset->status !== \App\ThematicStatisticDatasetStatus::Published)
                                <form action="{{ route('admin.published-thematic-statistic-datasets.update', $dataset) }}" method="post">
                                    @csrf
                                    @method('PUT')
                                    <button class="font-bold text-ocean" type="submit">Terbitkan</button>
                                </form>
                            @else
                                <span class="text-xs font-semibold text-emerald-700">Aktif di publik</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center">Belum ada dataset statistik tematik.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">{{ $datasets->links() }}</div>
@endsection

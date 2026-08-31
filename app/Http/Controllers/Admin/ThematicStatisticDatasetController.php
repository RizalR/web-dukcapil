<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThematicStatisticDataset;
use Illuminate\Contracts\View\View;

class ThematicStatisticDatasetController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', ThematicStatisticDataset::class);

        return view('admin.thematic-statistics.index', [
            'datasets' => ThematicStatisticDataset::query()
                ->with('importedBy:id,name')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(20),
        ]);
    }
}

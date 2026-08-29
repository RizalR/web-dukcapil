<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSupplyStatusRequest;
use App\Models\SupplyStatus;
use App\Services\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SupplyStatusController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', SupplyStatus::class);

        return view('admin.supplies.index', ['supplies' => SupplyStatus::query()->orderBy('location')->orderBy('item')->get()]);
    }

    public function edit(SupplyStatus $supplyStatus): View
    {
        $this->authorize('update', $supplyStatus);

        return view('admin.supplies.edit', ['supply' => $supplyStatus]);
    }

    public function update(UpdateSupplyStatusRequest $request, SupplyStatus $supplyStatus, AuditLogger $auditLogger): RedirectResponse
    {
        $supplyStatus->update([...$request->validated(), 'updated_by' => $request->user()->id]);
        $auditLogger->record('supply.updated', "Status {$supplyStatus->item} di {$supplyStatus->location} diperbarui.", $supplyStatus, ['status' => $supplyStatus->status->value]);

        return redirect()->route('admin.supply-statuses.index')->with('status', 'Status bahan berhasil diperbarui.');
    }
}

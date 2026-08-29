<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePrintingStatusRequest;
use App\Models\PrintingStatus;
use App\Services\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PrintingStatusController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', PrintingStatus::class);

        return view('admin.printing-statuses.index', [
            'printingStatuses' => PrintingStatus::query()->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function edit(PrintingStatus $printingStatus): View
    {
        $this->authorize('update', $printingStatus);

        return view('admin.printing-statuses.edit', ['printingStatus' => $printingStatus]);
    }

    public function update(UpdatePrintingStatusRequest $request, PrintingStatus $printingStatus, AuditLogger $auditLogger): RedirectResponse
    {
        $printingStatus->update([
            ...$request->safe()->only(['is_available', 'note', 'source', 'checked_at']),
            'updated_by' => $request->user()->id,
        ]);
        $auditLogger->record(
            'printing-status.updated',
            "Status {$printingStatus->document->label()} di {$printingStatus->location} diperbarui.",
            $printingStatus,
            ['is_available' => $printingStatus->is_available],
        );

        return redirect()->route('admin.printing-statuses.index')->with('status', 'Status pencetakan berhasil diperbarui.');
    }
}

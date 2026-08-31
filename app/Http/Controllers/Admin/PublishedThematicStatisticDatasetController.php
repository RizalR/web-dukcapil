<?php

namespace App\Http\Controllers\Admin;

use App\Actions\PublishThematicStatisticDataset;
use App\Http\Controllers\Controller;
use App\Models\ThematicStatisticDataset;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;

class PublishedThematicStatisticDatasetController extends Controller
{
    public function update(
        ThematicStatisticDataset $thematicStatisticDataset,
        PublishThematicStatisticDataset $publish,
        AuditLogger $auditLogger,
    ): RedirectResponse {
        $this->authorize('update', $thematicStatisticDataset);
        $publish->handle($thematicStatisticDataset);
        $auditLogger->record(
            'thematic-statistic.published',
            "Dataset {$thematicStatisticDataset->type->title()} periode {$thematicStatisticDataset->period} diterbitkan.",
            $thematicStatisticDataset,
        );

        return back()->with('status', 'Dataset statistik tematik berhasil diterbitkan.');
    }
}

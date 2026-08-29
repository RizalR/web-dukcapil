<?php

namespace App\Http\Controllers\Admin;

use App\AdministrativeAreaLevel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePopulationStatisticRequest;
use App\Http\Requests\Admin\UpdatePopulationStatisticRequest;
use App\Models\PopulationStatistic;
use App\Services\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class PopulationStatisticController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $this->authorize('viewAny', PopulationStatistic::class);

        return view('admin.statistics.index', [
            'statistics' => PopulationStatistic::query()->latest('updated_at')->orderByDesc('id')->paginate(20),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', PopulationStatistic::class);

        return view('admin.statistics.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePopulationStatisticRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $validated = $request->validated();
        $statistic = PopulationStatistic::query()->create([
            ...$validated,
            'area_code' => $this->manualAreaCode($validated['area']),
            'area_level' => AdministrativeAreaLevel::Legacy,
            'updated_by' => $request->user()->id,
        ]);
        $auditLogger->record('statistic.created', "Statistik {$statistic->label} dibuat.", $statistic, ['value' => $statistic->value]);

        return redirect()->route('admin.population-statistics.index')->with('status', 'Statistik berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PopulationStatistic $populationStatistic): RedirectResponse
    {
        return redirect()->route('admin.population-statistics.edit', $populationStatistic);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PopulationStatistic $populationStatistic): View
    {
        $this->authorize('update', $populationStatistic);

        return view('admin.statistics.edit', ['statistic' => $populationStatistic]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePopulationStatisticRequest $request, PopulationStatistic $populationStatistic, AuditLogger $auditLogger): RedirectResponse
    {
        $validated = $request->validated();
        $areaIdentity = $populationStatistic->area_level === AdministrativeAreaLevel::Legacy
            ? ['area_code' => $this->manualAreaCode($validated['area'])]
            : [];

        $populationStatistic->update([...$validated, ...$areaIdentity, 'updated_by' => $request->user()->id]);
        $auditLogger->record('statistic.updated', "Statistik {$populationStatistic->label} diperbarui.", $populationStatistic, ['value' => $populationStatistic->value]);

        return redirect()->route('admin.population-statistics.index')->with('status', 'Statistik berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PopulationStatistic $populationStatistic, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorize('delete', $populationStatistic);
        $auditLogger->record('statistic.deleted', "Statistik {$populationStatistic->label} dihapus.", $populationStatistic);
        $populationStatistic->delete();

        return redirect()->route('admin.population-statistics.index')->with('status', 'Statistik berhasil dihapus.');
    }

    private function manualAreaCode(string $area): string
    {
        return 'manual-'.substr(hash('sha256', Str::lower(Str::squish($area))), 0, 24);
    }
}

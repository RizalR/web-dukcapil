<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ExternalLink;
use App\Models\NewsArticle;
use App\Models\PopulationStatistic;
use App\Models\SupplyStatus;
use App\NewsStatus;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'counts' => [
                'news' => NewsArticle::query()->count(),
                'drafts' => NewsArticle::query()->where('status', NewsStatus::Draft)->count(),
                'statistics' => PopulationStatistic::query()->count(),
                'supplies' => SupplyStatus::query()->count(),
            ],
            'recentAudits' => AuditLog::query()->with('user')->latest()->limit(6)->get(),
            'brokenLinks' => ExternalLink::query()->where('last_check_ok', false)->count(),
        ]);
    }
}

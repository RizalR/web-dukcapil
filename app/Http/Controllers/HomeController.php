<?php

namespace App\Http\Controllers;

use App\AdministrativeAreaLevel;
use App\LinkCategory;
use App\Models\ExternalLink;
use App\Models\HeroSlide;
use App\Models\HomepageSetting;
use App\Models\LeadershipProfile;
use App\Models\NewsArticle;
use App\Models\PopulationStatistic;
use App\Models\PrintingStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $homepageSetting = HomepageSetting::query()->first();

        return view('public.home', [
            'homepageBackgroundUrl' => $homepageSetting?->background_image_path
                ? Storage::disk('public')->url($homepageSetting->background_image_path)
                : asset('images/dukcapil_.jpeg'),
            'heroSlides' => HeroSlide::query()->visible()->orderBy('sort_order')->orderBy('id')->get(),
            'leadershipProfiles' => LeadershipProfile::query()->published()->ordered()->get(),
            'featured' => NewsArticle::query()->published()->where('is_featured', true)->latest('published_at')->first(),
            'latestNews' => NewsArticle::query()->published()->with('author')->latest('published_at')->limit(3)->get(),
            'statistics' => $this->homepageStatistics(),
            'printingStatuses' => PrintingStatus::query()->orderBy('sort_order')->orderBy('id')->get(),
            'serviceLink' => ExternalLink::query()->where('category', LinkCategory::Service)->where('is_active', true)->orderBy('sort_order')->first(),
        ]);
    }

    /** @return Collection<int, PopulationStatistic> */
    private function homepageStatistics(): Collection
    {
        $latestPeriod = PopulationStatistic::query()
            ->published()
            ->where('area_level', AdministrativeAreaLevel::Regency->value)
            ->latest('published_at')
            ->value('period');

        if ($latestPeriod === null) {
            return PopulationStatistic::query()->published()->latest('published_at')->limit(3)->get();
        }

        $order = [
            PopulationStatistic::LABEL_TOTAL => 0,
            PopulationStatistic::LABEL_MALE => 1,
            PopulationStatistic::LABEL_FEMALE => 2,
        ];

        return PopulationStatistic::query()
            ->published()
            ->where('area_level', AdministrativeAreaLevel::Regency->value)
            ->where('period', $latestPeriod)
            ->whereIn('label', array_keys($order))
            ->get()
            ->sortBy(fn (PopulationStatistic $statistic): int => $order[$statistic->label])
            ->values();
    }
}

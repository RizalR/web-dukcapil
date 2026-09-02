<?php

namespace App\Actions;

use App\Models\WhistleblowingPublication;
use App\Models\WhistleblowingSyncState;
use App\Services\SiprimaWhistleblowingClient;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Throwable;

class SyncWhistleblowingPublications
{
    public function __construct(private SiprimaWhistleblowingClient $client) {}

    public function handle(): int
    {
        $state = WhistleblowingSyncState::query()->firstOrCreate(['source' => 'siprima']);
        $state->update(['last_attempted_at' => now()]);

        try {
            $snapshot = $this->client->fetchSnapshot();
            $syncedAt = now();
            $rows = array_map(function (array $item) use ($syncedAt): array {
                return [
                    'publication_id' => $item['publication_id'],
                    'category_code' => $item['category_code'],
                    'category_label' => $item['category_label'],
                    'summary' => $item['summary'],
                    'follow_up_result' => $item['follow_up_result'],
                    'published_at' => CarbonImmutable::parse($item['published_at'])->setTimezone(config('app.timezone')),
                    'source_updated_at' => CarbonImmutable::parse($item['updated_at'])->setTimezone(config('app.timezone')),
                    'last_synced_at' => $syncedAt,
                    'is_active' => true,
                    'withdrawn_at' => null,
                    'created_at' => $syncedAt,
                    'updated_at' => $syncedAt,
                ];
            }, $snapshot['items']);
            $publicationIds = array_column($rows, 'publication_id');

            DB::transaction(function () use ($rows, $publicationIds, $snapshot, $state, $syncedAt): void {
                if ($rows !== []) {
                    WhistleblowingPublication::query()->upsert(
                        $rows,
                        ['publication_id'],
                        [
                            'category_code',
                            'category_label',
                            'summary',
                            'follow_up_result',
                            'published_at',
                            'source_updated_at',
                            'last_synced_at',
                            'is_active',
                            'withdrawn_at',
                            'updated_at',
                        ],
                    );
                }

                $withdrawnQuery = WhistleblowingPublication::query()->where('is_active', true);
                if ($publicationIds !== []) {
                    $withdrawnQuery->whereNotIn('publication_id', $publicationIds);
                }
                $withdrawnQuery->update([
                    'is_active' => false,
                    'withdrawn_at' => $syncedAt,
                    'updated_at' => $syncedAt,
                ]);

                $state->update([
                    'last_succeeded_at' => $syncedAt,
                    'last_snapshot_generated_at' => CarbonImmutable::parse($snapshot['generated_at'])->setTimezone(config('app.timezone')),
                    'last_item_count' => count($rows),
                    'last_error_at' => null,
                    'last_error_code' => null,
                ]);
            });

            return count($rows);
        } catch (Throwable $exception) {
            $state->update([
                'last_error_at' => now(),
                'last_error_code' => class_basename($exception),
            ]);

            throw $exception;
        }
    }
}

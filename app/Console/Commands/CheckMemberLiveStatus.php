<?php

namespace App\Console\Commands;

use App\Models\AboutSettings;
use App\Models\LiveStreaming;
use App\Support\CheckMemberLive;
use App\Support\Timezone;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckMemberLiveStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-member-live';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the idol live status and store new live sessions to the database';

    /**
     * Execute the console command.
     */
    public function handle(CheckMemberLive $memberLive): int
    {
        $settings = AboutSettings::query()
            ->whereIn('key', ['idol_name', 'idol_shortname'])
            ->pluck('value', 'key');

        $memberNames = array_values(array_filter(
            [
                trim((string) $settings->get('idol_name')),
                trim((string) $settings->get('idol_shortname')),
            ],
            fn (string $name): bool => $name !== '',
        ));

        if ($memberNames === []) {
            $this->error('Idol name/shortname not found in about_settings.');

            return self::FAILURE;
        }

        $idolLabel = implode(' / ', $memberNames);
        $items = $memberLive->matchingItems($memberNames);

        if ($items === []) {
            $this->info("Tidak ada live streaming untuk {$idolLabel}.");

            return self::SUCCESS;
        }

        $saved = 0;
        $skipped = 0;

        foreach ($items as $item) {
            if ($this->store($item)) {
                $saved++;
            } else {
                $skipped++;
            }
        }

        $this->info("{$saved} live streaming disimpan, {$skipped} dilewati.");
        $this->info('Check completed.');

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function store(array $item): bool
    {
        $liveId = $item['slug'] ?? $item['_id'] ?? $item['data_id'] ?? null;

        if (! $liveId) {
            return false;
        }

        $liveId = (string) $liveId;

        if (LiveStreaming::query()->where('live_id', $liveId)->exists()) {
            return false;
        }

        $platform = match (strtolower(trim((string) ($item['platform'] ?? $item['type'] ?? '')))) {
            'idn', 'idn app' => 'IDN App',
            'showroom' => 'Showroom',
            default => null,
        };

        if ($platform === null) {
            return false;
        }

        $startedAt = $item['started_at'] ?? null;
        $liveDate = $startedAt
            ? Carbon::parse($startedAt)->timezone('Asia/Jakarta')->toDateString()
            : Timezone::nowLocal()->toDateString();

        $title = $item['title'] ?? ($item['idn']['title'] ?? ($item['notification_caption'] ?? null));

        LiveStreaming::query()->create([
            'live_id' => $liveId,
            'platform' => $platform,
            'live_date' => $liveDate,
            'duration' => null,
            'additional_info' => $title !== null ? trim((string) $title) : null,
        ]);

        $this->info("Saved live streaming: {$platform} - {$liveDate}");

        return true;
    }
}

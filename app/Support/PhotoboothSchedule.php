<?php

namespace App\Support;

use App\Models\Photobooth;
use Illuminate\Support\Carbon;
use Illuminate\Support\CarbonInterface;

final class PhotoboothSchedule
{
    public const OPEN = 'open';

    public const SCHEDULED = 'scheduled';

    public const CLOSED = 'closed';

    public const INACTIVE = 'inactive';

    /**
     * @return array{state: string, opens_at: ?CarbonInterface, closes_at: ?CarbonInterface}
     */
    public static function for(Photobooth $photobooth): array
    {
        if (! $photobooth->is_active) {
            return ['state' => self::INACTIVE, 'opens_at' => null, 'closes_at' => null];
        }

        if ($photobooth->is_full_open) {
            return ['state' => self::OPEN, 'opens_at' => null, 'closes_at' => null];
        }

        $now = Carbon::now();

        $opensAt = Timezone::toLocal($photobooth->start_at);
        $closesAt = Timezone::toLocal($photobooth->end_at);

        if ($photobooth->start_at && $now->lt($photobooth->start_at)) {
            return ['state' => self::SCHEDULED, 'opens_at' => $opensAt, 'closes_at' => $closesAt];
        }

        if ($photobooth->end_at && $now->gt($photobooth->end_at)) {
            return ['state' => self::CLOSED, 'opens_at' => $opensAt, 'closes_at' => $closesAt];
        }

        return ['state' => self::OPEN, 'opens_at' => $opensAt, 'closes_at' => $closesAt];
    }
}

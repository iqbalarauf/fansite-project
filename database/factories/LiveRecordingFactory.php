<?php

namespace Database\Factories;

use App\Enums\LiveRecordingStatus;
use App\Enums\LiveStreamingPlatform;
use App\Models\LiveRecording;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LiveRecording>
 */
class LiveRecordingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $slug = fake()->unique()->slug(3).'-'.now()->format('ymdHis');

        return [
            'live_slug' => $slug,
            'platform' => LiveStreamingPlatform::IdnApp->value,
            'member_name' => 'Oniel',
            'url_key' => 'jkt48_oniel',
            'source_url' => 'https://stream.example/'.$slug.'.m3u8',
            'status' => LiveRecordingStatus::Recording,
            'started_at' => now()->subHour(),
            'last_seen_live_at' => now(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => LiveRecordingStatus::Completed,
            'started_at' => now()->subHours(2),
            'ended_at' => now()->subHour(),
            'duration_seconds' => 3600,
            'file_size' => 1024,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'status' => LiveRecordingStatus::Failed,
            'error_message' => 'Gagal menjalankan ffmpeg.',
        ]);
    }
}

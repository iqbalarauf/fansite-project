<?php

namespace Tests\Feature;

use App\Enums\LiveRecordingStatus;
use App\Models\LiveRecording;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LiveRecordingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.jkt48connect.url' => 'https://jkt48connect.test',
            'services.jkt48connect.key' => 'test-key',
            'live-recording.enabled' => true,
            'live-recording.disk' => 'recordings',
            'live-recording.directory' => 'live',
            'live-recording.finalize_delay_minutes' => 5,
            'live-recording.stop_grace_seconds' => 0,
            'live-recording.max_duration_minutes' => 360,
        ]);

        Storage::fake('recordings');
        $this->setShortname('Oniel');
    }

    public function test_it_starts_recording_for_matching_live_items_only(): void
    {
        $this->fakeLive([
            $this->liveItem('saya-kembali-261127200819', 'Oniel'),
            $this->liveItem('lulu-live-261127200900', 'Lulu'),
            ['name' => 'Oniel', 'type' => 'idn', 'slug' => 'no-url-261127200901', 'url_key' => 'jkt48_oniel'],
        ]);

        Process::fake($this->processFake());

        $this->artisan('app:record-member-live')->assertExitCode(0);

        $this->assertDatabaseCount('live_recordings', 1);

        $recording = LiveRecording::query()->firstOrFail();

        $this->assertSame('saya-kembali-261127200819', $recording->live_slug);
        $this->assertSame('IDN App', $recording->platform);
        $this->assertSame('Oniel', $recording->member_name);
        $this->assertSame(LiveRecordingStatus::Recording, $recording->status);
        $this->assertSame(4242, $recording->pid);
        $this->assertSame('live/saya-kembali-261127200819.ts', $recording->segment_path);
        $this->assertNotNull($recording->started_at);
    }

    public function test_it_is_idempotent_for_the_same_live_slug(): void
    {
        $this->fakeLive([$this->liveItem('saya-kembali-261127200819', 'Oniel')]);

        Process::fake($this->processFake());

        $this->artisan('app:record-member-live')->assertExitCode(0);
        $this->artisan('app:record-member-live')->assertExitCode(0);

        $this->assertDatabaseCount('live_recordings', 1);
        $this->assertSame(LiveRecordingStatus::Recording, LiveRecording::query()->firstOrFail()->status);
    }

    public function test_it_retries_a_recording_that_failed_before_starting(): void
    {
        $this->fakeLive([$this->liveItem('saya-kembali-261127200819', 'Oniel')]);

        $recording = $this->makeRecording([
            'live_slug' => 'saya-kembali-261127200819',
            'status' => LiveRecordingStatus::Failed->value,
            'segment_path' => null,
            'error_message' => 'Gagal menjalankan ffmpeg.',
        ]);

        Process::fake($this->processFake());

        $this->artisan('app:record-member-live')->assertExitCode(0);

        $recording->refresh();

        $this->assertSame(LiveRecordingStatus::Recording, $recording->status);
        $this->assertSame(4242, $recording->pid);
        $this->assertNull($recording->error_message);
    }

    public function test_it_marks_the_recording_as_failed_when_ffmpeg_cannot_start(): void
    {
        $this->fakeLive([$this->liveItem('saya-kembali-261127200819', 'Oniel')]);

        Process::fake(fn (PendingProcess $process): mixed => str_contains($this->processCommand($process), '-f mpegts')
            ? Process::result(exitCode: 1)
            : Process::result());

        $this->artisan('app:record-member-live')->assertExitCode(0);

        $recording = LiveRecording::query()->firstOrFail();

        $this->assertSame(LiveRecordingStatus::Failed, $recording->status);
        $this->assertNotNull($recording->error_message);
        $this->assertNull($recording->pid);
    }

    public function test_it_does_nothing_when_the_feature_is_disabled(): void
    {
        config(['live-recording.enabled' => false]);
        $this->fakeLive([$this->liveItem('saya-kembali-261127200819', 'Oniel')]);

        Process::fake();

        $this->artisan('app:record-member-live')->assertExitCode(0);

        $this->assertDatabaseCount('live_recordings', 0);
        Process::assertNotRan(fn (PendingProcess $process): bool => str_contains($this->processCommand($process), '-f mpegts'));
    }

    public function test_finalize_stops_and_remuxes_a_finished_recording(): void
    {
        $this->fakeLive([]);

        $recording = $this->makeRecording([
            'live_slug' => 'saya-kembali-261127200819',
            'status' => LiveRecordingStatus::Recording->value,
            'pid' => 4242,
            'segment_path' => 'live/saya-kembali-261127200819.ts',
            'started_at' => now()->subHour(),
            'last_seen_live_at' => now()->subMinutes(10),
        ]);

        Storage::disk('recordings')->put('live/saya-kembali-261127200819.ts', 'fake-ts');

        $runningChecks = 0;

        Process::fake(function (PendingProcess $process) use (&$runningChecks): mixed {
            $command = $this->processCommand($process);

            if ($this->isRunningCheck($command)) {
                $runningChecks++;

                // Alive during the stop decision, gone during finalization.
                return $runningChecks === 1 ? Process::result(output: '4242') : Process::result(exitCode: 1);
            }

            if (str_contains($command, '-movflags')) {
                $this->createRemuxedFile($process);

                return Process::result();
            }

            return Process::result();
        });

        $this->artisan('app:finalize-live-recordings')->assertExitCode(0);

        $recording->refresh();

        $this->assertSame(LiveRecordingStatus::Completed, $recording->status);
        $this->assertSame('live/saya-kembali-261127200819.mp4', $recording->file_path);
        $this->assertNotNull($recording->duration_seconds);
        $this->assertNotNull($recording->file_size);
        Storage::disk('recordings')->assertMissing('live/saya-kembali-261127200819.ts');
        Storage::disk('recordings')->assertExists('live/saya-kembali-261127200819.mp4');
        Process::assertRan(fn (PendingProcess $process): bool => str_contains($this->processCommand($process), '-movflags'));
    }

    public function test_finalize_keeps_a_recording_while_the_live_is_still_online(): void
    {
        $this->fakeLive([$this->liveItem('saya-kembali-261127200819', 'Oniel')]);

        $recording = $this->makeRecording([
            'live_slug' => 'saya-kembali-261127200819',
            'status' => LiveRecordingStatus::Recording->value,
            'pid' => 4242,
            'segment_path' => 'live/saya-kembali-261127200819.ts',
            'started_at' => now()->subHour(),
            'last_seen_live_at' => now()->subHour(),
        ]);

        Process::fake(fn (PendingProcess $process): mixed => $this->isRunningCheck($this->processCommand($process))
            ? Process::result(output: '4242')
            : Process::result());

        $this->artisan('app:finalize-live-recordings')->assertExitCode(0);

        $this->assertSame(LiveRecordingStatus::Recording, $recording->fresh()->status);
    }

    public function test_finalize_waits_before_stopping_a_live_that_disappeared_briefly(): void
    {
        $this->fakeLive([]);

        $recording = $this->makeRecording([
            'live_slug' => 'saya-kembali-261127200819',
            'status' => LiveRecordingStatus::Recording->value,
            'pid' => 4242,
            'segment_path' => 'live/saya-kembali-261127200819.ts',
            'started_at' => now()->subHour(),
            'last_seen_live_at' => now()->subMinute(),
        ]);

        Process::fake(fn (PendingProcess $process): mixed => $this->isRunningCheck($this->processCommand($process))
            ? Process::result(output: '4242')
            : Process::result());

        $this->artisan('app:finalize-live-recordings')->assertExitCode(0);

        $this->assertSame(LiveRecordingStatus::Recording, $recording->fresh()->status);
    }

    public function test_finalize_does_not_stop_recordings_when_the_live_api_is_unavailable(): void
    {
        Http::fake([
            'https://jkt48connect.test/api/now_live*' => Http::response([], 500),
        ]);

        $recording = $this->makeRecording([
            'live_slug' => 'saya-kembali-261127200819',
            'status' => LiveRecordingStatus::Recording->value,
            'pid' => 4242,
            'segment_path' => 'live/saya-kembali-261127200819.ts',
            'started_at' => now()->subHour(),
            'last_seen_live_at' => now()->subHour(),
        ]);

        Process::fake(fn (PendingProcess $process): mixed => $this->isRunningCheck($this->processCommand($process))
            ? Process::result(output: '4242')
            : Process::result());

        $this->artisan('app:finalize-live-recordings')->assertExitCode(0);

        $this->assertSame(LiveRecordingStatus::Recording, $recording->fresh()->status);
    }

    public function test_finalize_stops_a_recording_that_exceeds_the_max_duration(): void
    {
        $this->fakeLive([$this->liveItem('saya-kembali-261127200819', 'Oniel')]);

        $recording = $this->makeRecording([
            'live_slug' => 'saya-kembali-261127200819',
            'status' => LiveRecordingStatus::Recording->value,
            'pid' => 4242,
            'segment_path' => 'live/saya-kembali-261127200819.ts',
            'started_at' => now()->subHours(8),
            'last_seen_live_at' => now(),
        ]);

        Process::fake(fn (PendingProcess $process): mixed => $this->isRunningCheck($this->processCommand($process))
            ? Process::result(output: '4242')
            : Process::result());

        $this->artisan('app:finalize-live-recordings')->assertExitCode(0);

        $this->assertSame(LiveRecordingStatus::Stopping, $recording->fresh()->status);
    }

    public function test_finalize_marks_missing_segments_as_failed(): void
    {
        $this->fakeLive([]);

        $recording = $this->makeRecording([
            'live_slug' => 'saya-kembali-261127200819',
            'status' => LiveRecordingStatus::Stopping->value,
            'pid' => 4242,
            'segment_path' => 'live/saya-kembali-261127200819.ts',
            'started_at' => now()->subHour(),
            'ended_at' => now()->subMinutes(5),
            'last_seen_live_at' => now()->subHour(),
        ]);

        Process::fake(fn (): mixed => Process::result(exitCode: 1));

        $this->artisan('app:finalize-live-recordings')->assertExitCode(0);

        $this->assertSame(LiveRecordingStatus::Failed, $recording->fresh()->status);
    }

    public function test_prune_deletes_old_recordings_and_their_files(): void
    {
        $this->insertRecording('old-completed-261101000000', 'completed', now()->subDays(20), 'live/old-completed.mp4', 'live/old-completed.ts');
        $this->insertRecording('recent-completed-261126000000', 'completed', now()->subDay(), 'live/recent-completed.mp4', null);
        $this->insertRecording('old-failed-261101000000', 'failed', now()->subDays(20), null, 'live/old-failed.ts');

        foreach (['live/old-completed.mp4', 'live/old-completed.ts', 'live/recent-completed.mp4', 'live/old-failed.ts', 'live/old-completed-261101000000.log', 'live/old-failed-261101000000.log'] as $path) {
            Storage::disk('recordings')->put($path, 'data');
        }

        $this->artisan('app:prune-live-recordings')->assertExitCode(0);

        $this->assertDatabaseMissing('live_recordings', ['live_slug' => 'old-completed-261101000000']);
        $this->assertDatabaseMissing('live_recordings', ['live_slug' => 'old-failed-261101000000']);
        $this->assertDatabaseHas('live_recordings', ['live_slug' => 'recent-completed-261126000000']);

        Storage::disk('recordings')->assertMissing('live/old-completed.mp4');
        Storage::disk('recordings')->assertMissing('live/old-completed.ts');
        Storage::disk('recordings')->assertMissing('live/old-completed-261101000000.log');
        Storage::disk('recordings')->assertMissing('live/old-failed.ts');
        Storage::disk('recordings')->assertMissing('live/old-failed-261101000000.log');
        Storage::disk('recordings')->assertExists('live/recent-completed.mp4');
    }

    public function test_admin_can_open_the_recordings_page(): void
    {
        $user = User::factory()->create();

        $this->makeRecording([
            'live_slug' => 'saya-kembali-261127200819',
            'member_name' => 'Oniel',
            'status' => LiveRecordingStatus::Completed->value,
            'file_path' => 'live/saya-kembali-261127200819.mp4',
            'started_at' => now()->subHour(),
        ]);

        $this->actingAs($user)
            ->get(route('live-recordings.index'))
            ->assertOk()
            ->assertSee('Rekaman Live')
            ->assertSee('Oniel')
            ->assertSee('saya-kembali-261127200819')
            ->assertSee('Selesai');
    }

    public function test_view_only_user_can_view_but_cannot_delete_recordings(): void
    {
        $viewer = User::factory()->viewOnly()->create();

        $recording = $this->makeRecording([
            'live_slug' => 'saya-kembali-261127200819',
            'status' => LiveRecordingStatus::Completed->value,
            'file_path' => 'live/saya-kembali-261127200819.mp4',
            'started_at' => now()->subHour(),
        ]);

        $this->actingAs($viewer)->get(route('live-recordings.index'))->assertOk();

        $this->actingAs($viewer)
            ->delete(route('live-recordings.destroy', $recording))
            ->assertForbidden();

        $this->assertDatabaseHas('live_recordings', ['id' => $recording->id]);
    }

    public function test_admin_can_preview_and_download_a_completed_recording(): void
    {
        $user = User::factory()->create();

        $recording = $this->makeRecording([
            'live_slug' => 'saya-kembali-261127200819',
            'status' => LiveRecordingStatus::Completed->value,
            'file_path' => 'live/saya-kembali-261127200819.mp4',
            'started_at' => now()->subHour(),
        ]);

        Storage::disk('recordings')->put('live/saya-kembali-261127200819.mp4', 'video-bytes');

        $this->actingAs($user)
            ->get(route('live-recordings.preview', $recording))
            ->assertOk()
            ->assertHeader('content-type', 'video/mp4');

        $this->actingAs($user)
            ->get(route('live-recordings.download', $recording))
            ->assertOk()
            ->assertDownload($recording->downloadName());
    }

    public function test_preview_returns_not_found_when_the_file_is_missing(): void
    {
        $user = User::factory()->create();

        $recording = $this->makeRecording([
            'live_slug' => 'saya-kembali-261127200819',
            'status' => LiveRecordingStatus::Completed->value,
            'file_path' => 'live/missing.mp4',
            'started_at' => now()->subHour(),
        ]);

        $this->actingAs($user)
            ->get(route('live-recordings.preview', $recording))
            ->assertNotFound();
    }

    public function test_admin_can_delete_a_recording_and_its_file(): void
    {
        $user = User::factory()->create();

        $recording = $this->makeRecording([
            'live_slug' => 'saya-kembali-261127200819',
            'status' => LiveRecordingStatus::Completed->value,
            'file_path' => 'live/saya-kembali-261127200819.mp4',
            'started_at' => now()->subHour(),
        ]);

        Storage::disk('recordings')->put('live/saya-kembali-261127200819.mp4', 'video-bytes');

        $this->actingAs($user)
            ->delete(route('live-recordings.destroy', $recording))
            ->assertRedirect(route('live-recordings.index'));

        $this->assertDatabaseMissing('live_recordings', ['id' => $recording->id]);
        Storage::disk('recordings')->assertMissing('live/saya-kembali-261127200819.mp4');
    }

    public function test_guests_cannot_access_recordings(): void
    {
        $this->get(route('live-recordings.index'))->assertRedirect();
    }

    private function setShortname(string $value): void
    {
        DB::table('about_settings')->updateOrInsert(
            ['key' => 'idol_shortname'],
            ['value' => $value, 'updated_at' => now()],
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function fakeLive(array $items): void
    {
        Http::fake([
            'https://jkt48connect.test/api/now_live*' => Http::response(['data' => $items], 200),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function liveItem(string $slug, string $name): array
    {
        return [
            'name' => $name,
            'type' => 'idn',
            'url_key' => 'jkt48_'.strtolower($name),
            'slug' => $slug,
            'started_at' => '2026-11-27T10:00:00.000Z',
            'streaming_url_list' => [
                ['label' => 'original', 'quality' => 1, 'url' => 'https://stream.example/'.$slug.'.m3u8'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function makeRecording(array $attributes): LiveRecording
    {
        return LiveRecording::query()->create(array_merge([
            'platform' => 'IDN App',
            'member_name' => 'Oniel',
            'url_key' => 'jkt48_oniel',
            'source_url' => 'https://stream.example/live.m3u8',
            'status' => LiveRecordingStatus::Recording,
        ], $attributes));
    }

    private function insertRecording(string $slug, string $status, mixed $createdAt, ?string $filePath, ?string $segmentPath): void
    {
        DB::table('live_recordings')->insert([
            'live_slug' => $slug,
            'platform' => 'IDN App',
            'member_name' => 'Oniel',
            'url_key' => 'jkt48_oniel',
            'source_url' => 'https://stream.example/live.m3u8',
            'status' => $status,
            'file_path' => $filePath,
            'segment_path' => $segmentPath,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    private function processFake(): callable
    {
        return function (PendingProcess $process): mixed {
            $command = $this->processCommand($process);

            if (str_contains($command, '-f mpegts')) {
                return Process::result(output: "4242\n");
            }

            if ($this->isRunningCheck($command)) {
                return Process::result(output: '4242');
            }

            if (str_contains($command, '-movflags')) {
                $this->createRemuxedFile($process);

                return Process::result();
            }

            return Process::result();
        };
    }

    private function isRunningCheck(string $command): bool
    {
        return str_contains($command, 'tasklist')
            || (str_contains($command, 'kill') && str_contains($command, '-0'));
    }

    private function processCommand(PendingProcess $process): string
    {
        if (is_array($process->command)) {
            return implode(' ', array_map('strval', $process->command));
        }

        return (string) $process->command;
    }

    private function createRemuxedFile(PendingProcess $process): void
    {
        if (! is_array($process->command)) {
            return;
        }

        $outputPath = end($process->command);

        if (! is_string($outputPath) || $outputPath === '') {
            return;
        }

        if (! is_dir(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0777, true);
        }

        file_put_contents($outputPath, 'fake-mp4');
    }
}

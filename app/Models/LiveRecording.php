<?php

namespace App\Models;

use App\Enums\LiveRecordingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LiveRecording extends Model
{
    use HasFactory;

    protected $table = 'live_recordings';

    protected $fillable = [
        'live_slug',
        'platform',
        'member_name',
        'url_key',
        'source_url',
        'status',
        'file_path',
        'segment_path',
        'file_size',
        'duration_seconds',
        'pid',
        'started_at',
        'ended_at',
        'last_seen_live_at',
        'error_message',
    ];

    protected $casts = [
        'status' => LiveRecordingStatus::class,
        'file_size' => 'integer',
        'duration_seconds' => 'integer',
        'pid' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'last_seen_live_at' => 'datetime',
    ];

    /**
     * Recordings that still have an ffmpeg process attached.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            LiveRecordingStatus::Starting->value,
            LiveRecordingStatus::Recording->value,
            LiveRecordingStatus::Stopping->value,
        ]);
    }

    public function isCompleted(): bool
    {
        return $this->status === LiveRecordingStatus::Completed;
    }

    public function hasFile(): bool
    {
        return filled($this->file_path)
            && Storage::disk($this->disk())->exists((string) $this->file_path);
    }

    /**
     * Absolute path of the final MP4 file, or null when it is not available.
     */
    public function absolutePath(): ?string
    {
        if (! $this->hasFile()) {
            return null;
        }

        return Storage::disk($this->disk())->path((string) $this->file_path);
    }

    public function downloadName(): string
    {
        $member = Str::slug((string) $this->member_name);
        $date = $this->started_at?->format('Ymd-His') ?? $this->created_at?->format('Ymd-His') ?? 'recording';

        return trim(($member !== '' ? $member.'-' : '').$date, '-').'.mp4';
    }

    public function durationLabel(): string
    {
        if ($this->duration_seconds === null) {
            return '–';
        }

        return sprintf(
            '%02d:%02d:%02d',
            intdiv($this->duration_seconds, 3600),
            intdiv($this->duration_seconds % 3600, 60),
            $this->duration_seconds % 60,
        );
    }

    public function fileSizeLabel(): string
    {
        if ($this->file_size === null) {
            return '–';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = (float) $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return number_format($size, $unit === 0 ? 0 : 1, ',', '.').' '.$units[$unit];
    }

    private function disk(): string
    {
        return (string) config('live-recording.disk', 'recordings');
    }
}

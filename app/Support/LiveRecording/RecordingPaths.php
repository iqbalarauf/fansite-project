<?php

namespace App\Support\LiveRecording;

final class RecordingPaths
{
    public static function diskName(): string
    {
        return (string) config('live-recording.disk', 'recordings');
    }

    public static function directory(): string
    {
        return trim((string) config('live-recording.directory', 'live'), '/');
    }

    /**
     * Path (relative to the recordings disk) for a recording artifact.
     */
    public static function file(string $slug, string $extension): string
    {
        $base = preg_replace('/[^A-Za-z0-9._-]/', '-', $slug) ?: 'recording';
        $directory = self::directory();

        return ($directory !== '' ? $directory.'/' : '').$base.'.'.$extension;
    }

    /**
     * Convert a ".ts" segment path into its final ".mp4" path.
     */
    public static function mp4For(string $segmentPath): string
    {
        $mp4 = preg_replace('/\.ts$/i', '.mp4', $segmentPath);

        return is_string($mp4) && $mp4 !== $segmentPath ? $mp4 : $segmentPath.'.mp4';
    }
}

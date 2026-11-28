<?php

namespace App\Support\LiveRecording;

use Illuminate\Support\Facades\Process;

final class Ffmpeg
{
    /**
     * Start a detached ffmpeg process and return its PID.
     *
     * Recording is written to MPEG-TS first so a sudden stop never leaves a
     * corrupted MP4 behind. The process is detached from the scheduler command
     * and keeps running until it is signalled to stop.
     */
    public function start(string $sourceUrl, string $segmentPath, string $logPath): ?int
    {
        $result = Process::timeout(30)->run($this->startCommand($sourceUrl, $segmentPath, $logPath));

        if (! $result->successful()) {
            return null;
        }

        $pid = (int) trim($result->output());

        if ($pid <= 0) {
            return null;
        }

        // A detached shell returns the PID before ffmpeg actually initializes.
        // Give it a moment so a missing binary or an invalid URL is reported as
        // a failed start instead of a healthy recording.
        usleep(300_000);

        return $this->isRunning($pid) ? $pid : null;
    }

    public function isRunning(?int $pid): bool
    {
        if ($pid === null || $pid <= 0) {
            return false;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $result = Process::timeout(10)->run(['tasklist', '/FI', 'PID eq '.$pid, '/NH']);

            return $result->successful() && preg_match('/\b'.$pid.'\b/', $result->output()) === 1;
        }

        return Process::timeout(10)->run(['kill', '-0', (string) $pid])->successful();
    }

    /**
     * Ask ffmpeg to finish gracefully. SIGINT lets it close the TS file cleanly.
     */
    public function stop(?int $pid): void
    {
        if ($pid === null || $pid <= 0) {
            return;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            Process::timeout(15)->run(['taskkill', '/PID', (string) $pid, '/T', '/F']);

            return;
        }

        Process::timeout(15)->run(['kill', '-INT', (string) $pid]);
    }

    /**
     * Remux the recorded MPEG-TS file into an MP4 without re-encoding.
     */
    public function remux(string $segmentPath, string $outputPath): bool
    {
        $result = Process::timeout((int) config('live-recording.remux_timeout', 1800))->run([
            $this->binary(),
            '-hide_banner',
            '-loglevel',
            'warning',
            '-nostdin',
            '-y',
            '-i',
            $segmentPath,
            '-c',
            'copy',
            '-movflags',
            '+faststart',
            $outputPath,
        ]);

        return $result->successful();
    }

    private function startCommand(string $sourceUrl, string $segmentPath, string $logPath): string
    {
        $binary = escapeshellarg($this->binary());
        $log = escapeshellarg($logPath);

        $options = implode(' ', [
            '-hide_banner',
            '-loglevel',
            'warning',
            '-nostdin',
            '-y',
            '-reconnect',
            '1',
            '-reconnect_streamed',
            '1',
            '-reconnect_delay_max',
            '5',
        ]);

        $userAgent = trim((string) config('live-recording.user_agent', ''));

        if ($userAgent !== '') {
            $options .= ' -user_agent '.escapeshellarg($userAgent);
        }

        $headers = array_values(array_filter((array) config('live-recording.headers', []), 'is_string'));

        if ($headers !== []) {
            $options .= ' -headers '.escapeshellarg(implode("\r\n", $headers)."\r\n");
        }

        $arguments = $options
            .' -i '.escapeshellarg($sourceUrl)
            .' -c copy -f mpegts '.escapeshellarg($segmentPath);

        if (PHP_OS_FAMILY === 'Windows') {
            // Windows is only used for local development; ffmpeg must be in PATH.
            return 'start /B "" '.$binary.$arguments.' >> '.$log.' 2>&1';
        }

        return 'nohup '.$binary.$arguments.' >> '.$log.' 2>&1 & echo $!';
    }

    private function binary(): string
    {
        $path = trim((string) config('live-recording.ffmpeg_path', 'ffmpeg'));

        return $path !== '' ? $path : 'ffmpeg';
    }
}

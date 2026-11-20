<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Kernel;
use Tests\TestCase;

class ScheduledTasksTest extends TestCase
{
    public function test_scheduled_commands_run_at_their_required_intervals(): void
    {
        $this->app->make(Kernel::class);

        $events = collect($this->app->make(Schedule::class)->events());

        $recent = $events->first(
            fn ($event): bool => str_contains((string) $event->command, 'app:fetch-streaming-info'),
        );

        $this->assertNotNull($recent);
        $this->assertSame('0 0,12 * * *', $recent->expression);

        $nowLive = $events->first(
            fn ($event): bool => str_contains((string) $event->command, 'app:check-member-live'),
        );

        $this->assertNotNull($nowLive);
        $this->assertSame('*/10 * * * *', $nowLive->expression);

        $theater = $events->first(
            fn ($event): bool => str_contains((string) $event->command, 'app:fetch-theater-shows'),
        );

        $this->assertNotNull($theater);
        $this->assertSame('0 0 * * *', $theater->expression);
    }
}

<?php

namespace Tests\Unit;

use App\Support\ShowDate;
use PHPUnit\Framework\TestCase;

class ShowDateTest extends TestCase
{
    public function test_it_normalizes_slash_and_dash_dates(): void
    {
        $this->assertSame('2026-08-24', ShowDate::normalize('2026/08/24'));
        $this->assertSame('2026-08-24', ShowDate::normalize('2026-08-24'));
    }

    public function test_it_builds_the_sql_expression_for_show_dates(): void
    {
        $this->assertSame("REPLACE(show_date, '/', '-')", ShowDate::sqlExpression());
        $this->assertSame("REPLACE(event_date, '/', '-')", ShowDate::sqlExpression('event_date'));
    }
}

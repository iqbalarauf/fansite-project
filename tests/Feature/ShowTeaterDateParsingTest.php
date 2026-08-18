<?php

namespace Tests\Feature;

use Tests\TestCase;

class ShowTeaterDateParsingTest extends TestCase
{
    public function test_show_date_parser_accepts_database_and_indonesian_formats(): void
    {
        $this->assertNotNull(parseShowDate('2026/08/08'));
        $this->assertSame('2026-08-08', parseShowDate('2026/08/08')->format('Y-m-d'));
        $this->assertSame('2024-11-28', parseShowDate('Jumat, 28 November 2024')->format('Y-m-d'));
    }
}

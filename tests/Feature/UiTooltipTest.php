<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UiTooltipTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_teater_predictor_has_explanatory_popover(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('show-teater.index'))
            ->assertOk()
            ->assertSee('data-info-popover', false)
            ->assertSee('Cara Kerja Predictor Unit Song')
            ->assertSee('Predictor Unit Song');
    }

    public function test_admin_pages_use_flux_tooltips_for_sort_buttons(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('show-teater.index'))
            ->assertOk()
            ->assertSee('data-flux-tooltip', false)
            ->assertDontSee('title="Ascending"', false);
    }
}

<?php

namespace Tests\Feature;

use App\Models\CustomPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlertModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_layout_includes_modal_based_alert_component(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('pages.index'))
            ->assertOk()
            ->assertSee('data-alert-modal', false)
            ->assertSee('openConfirm', false);
    }

    public function test_public_layout_includes_modal_based_alert_component(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-alert-modal', false);
    }

    public function test_delete_form_confirmation_attribute_does_not_leak_javascript(): void
    {
        $this->actingAs(User::factory()->create());

        CustomPage::query()->create([
            'title' => 'Halaman Uji',
            'slug' => 'halaman-uji',
            'blocks' => [],
        ]);

        $this->get(route('pages.index'))
            ->assertOk()
            ->assertSee('data-confirm-message', false)
            ->assertSee('data-confirm-label', false)
            ->assertDontSee("\$el.submit())'>", false);
    }

    public function test_delete_forms_use_modal_confirmation_instead_of_native_confirm(): void
    {
        $this->actingAs(User::factory()->create());

        CustomPage::query()->create([
            'title' => 'Halaman Uji',
            'slug' => 'halaman-uji',
            'blocks' => [],
        ]);

        $this->get(route('pages.index'))
            ->assertOk()
            ->assertSee('appConfirm', false)
            ->assertDontSee('onsubmit="return confirm', false);

        foreach ([route('magazines.index'), route('concert-events.index'), route('meet-greet-events.index')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertDontSee('onsubmit="return confirm', false);
        }
    }

    public function test_admin_scripts_no_longer_use_native_alert_or_confirm(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('show-teater.index'))
            ->assertOk()
            ->assertDontSee('if (!confirm(', false)
            ->assertSee('window.appConfirm', false)
            ->assertSee('window.appAlert', false);

        $this->get(route('live-streaming.index'))
            ->assertOk()
            ->assertSee('window.appAlert', false);
    }
}

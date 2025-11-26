<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_update_settings()
    {
        $user = User::factory()->create([ 'id' => 2 ]);

        $response = $this->actingAs($user)->put(route('settings.update'), [
            'app_name' => 'Try Change'
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_app_name()
    {
        $admin = User::factory()->create([ 'id' => 1 ]);

        $response = $this->actingAs($admin)->put(route('settings.update'), [
            'app_name' => 'My Fancy App'
        ]);

        $response->assertRedirect();

        $row = DB::table('app_settings')->where('key', 'app_name')->first();
        $this->assertNotNull($row, 'app_name row exists');
        $this->assertEquals('My Fancy App', $row->value);
    }

    public function test_admin_can_upload_logo_file()
    {
        Storage::fake('public');

        $admin = User::factory()->create([ 'id' => 1 ]);

        $file = UploadedFile::fake()->image('logo.png');

        $response = $this->actingAs($admin)->call('PUT', route('settings.update'), [
            'app_name' => 'Upload Test',
        ], [], [
            'logo' => $file,
        ]);

        $response->assertRedirect();

        $row = DB::table('app_settings')->where('key', 'logo')->first();
        $this->assertNotNull($row, 'logo row exists');
        $this->assertStringContainsString('/storage/settings/', $row->value);

        // verify file was stored under public disk
        // extract storage path portion
        $path = preg_replace('#^.*?/storage/#', '', $row->value);
        $this->assertTrue(Storage::disk('public')->exists($path));
    }

    public function test_runtime_config_app_name_reflects_setting()
    {
        // Save a setting then call the service provider boot method and
        // assert that config('app.name') was updated for the current runtime.
        \App\Models\Setting::set('app_name', 'Runtime App Name');

        $provider = $this->app->getProvider(\App\Providers\AppServiceProvider::class);
        $provider->boot();

        $this->assertEquals('Runtime App Name', config('app.name'));
    }

    public function test_admin_can_update_sidebar_name()
    {
        $admin = User::factory()->create([ 'id' => 1 ]);

        $response = $this->actingAs($admin)->put(route('settings.update'), [
            'sidebar_name' => 'TabName'
        ]);

        $response->assertRedirect();

        $row = DB::table('app_settings')->where('key', 'sidebar_name')->first();
        $this->assertNotNull($row, 'sidebar_name row exists');
        $this->assertEquals('TabName', $row->value);

        // Ensure service provider sets runtime config when booted
        $provider = $this->app->getProvider(\App\Providers\AppServiceProvider::class);
        $provider->boot();

        $this->assertEquals('TabName', config('app.sidebar_name'));
    }

    public function test_admin_can_remove_sidebar_name()
    {
        $admin = User::factory()->create([ 'id' => 1 ]);

        // set value
        \App\Models\Setting::set('sidebar_name', 'ToRemove');

        $response = $this->actingAs($admin)->put(route('settings.update'), [
            'remove_sidebar_name' => true
        ]);

        $response->assertRedirect();

        $row = DB::table('app_settings')->where('key', 'sidebar_name')->first();
        $this->assertNotNull($row, 'sidebar_name row exists');
        $this->assertNull($row->value);
    }
}

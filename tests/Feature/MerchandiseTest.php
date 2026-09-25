<?php

namespace Tests\Feature;

use App\Models\MerchandiseProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MerchandiseTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalog_lists_active_products_with_shop_button(): void
    {
        MerchandiseProduct::factory()->create(['name' => 'T-Shirt Oniel', 'shop_url' => 'https://tokoku.example/tshirt']);
        MerchandiseProduct::factory()->inactive()->create(['name' => 'Produk Nonaktif']);

        $this->get(route('merchandise.index'))
            ->assertOk()
            ->assertSee('T-Shirt Oniel')
            ->assertSee('https://tokoku.example/tshirt', false)
            ->assertSee('Belanja')
            ->assertDontSee('Produk Nonaktif');
    }

    public function test_product_detail_shows_photo_collage_and_description(): void
    {
        $product = MerchandiseProduct::factory()->create([
            'name' => 'Photocard Set',
            'description' => 'Deskripsi produk photocard.',
            'images' => ['merchandise/a.jpg', 'merchandise/b.jpg'],
        ]);

        $this->get(route('merchandise.show', $product))
            ->assertOk()
            ->assertSee('Photocard Set')
            ->assertSee('Deskripsi produk photocard.')
            ->assertSee('/storage/merchandise/a.jpg', false)
            ->assertSee('/storage/merchandise/b.jpg', false);
    }

    public function test_detail_uses_global_shop_url_when_product_has_none(): void
    {
        DB::table('app_settings')->updateOrInsert(
            ['key' => 'merchandise_shop_url'],
            ['value' => 'https://global.example/toko', 'updated_at' => now()],
        );
        Cache::forget('app_settings');

        $product = MerchandiseProduct::factory()->create(['shop_url' => null]);

        $this->get(route('merchandise.show', $product))
            ->assertOk()
            ->assertSee('https://global.example/toko', false);
    }

    public function test_inactive_product_detail_is_not_found(): void
    {
        $product = MerchandiseProduct::factory()->inactive()->create();

        $this->get(route('merchandise.show', $product))->assertNotFound();
    }

    public function test_catalog_hidden_when_feature_disabled(): void
    {
        MerchandiseProduct::factory()->create();

        DB::table('app_settings')->updateOrInsert(
            ['key' => 'merchandise_enabled'],
            ['value' => 'false', 'updated_at' => now()],
        );
        Cache::forget('app_settings');

        $this->get(route('merchandise.index'))->assertNotFound();
    }

    public function test_super_admin_can_create_update_and_delete_product(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('merchandise.admin.store'), [
                'name' => 'Keychain Lucu',
                'description' => 'Gantungan kunci.',
                'price' => 25000,
                'shop_url' => 'https://tokoku.example/keychain',
                'is_active' => 1,
                'sort_order' => 3,
                'images' => [UploadedFile::fake()->image('produk.jpg', 600, 600)],
            ])
            ->assertRedirect(route('merchandise.admin.index'));

        $product = MerchandiseProduct::query()->firstOrFail();
        $this->assertSame('keychain-lucu', $product->slug);
        $this->assertCount(1, $product->imagePaths());
        Storage::disk('public')->assertExists($product->imagePaths()[0]);

        $this->actingAs($admin)
            ->put(route('merchandise.admin.update', $product), [
                'name' => 'Keychain Lucu v2',
                'slug' => 'keychain-lucu-v2',
                'is_active' => 1,
            ])
            ->assertRedirect(route('merchandise.admin.index'));

        $this->assertSame('Keychain Lucu v2', $product->fresh()->name);

        $this->actingAs($admin)
            ->delete(route('merchandise.admin.destroy', $product))
            ->assertRedirect(route('merchandise.admin.index'));

        $this->assertSoftDeleted($product);
    }

    public function test_non_super_admin_cannot_manage_merchandise(): void
    {
        $creator = User::factory()->contentCreator()->create();

        $this->actingAs($creator)->get(route('merchandise.admin.index'))->assertForbidden();
        $this->actingAs($creator)->post(route('merchandise.admin.store'), ['name' => 'X'])->assertForbidden();
    }
}

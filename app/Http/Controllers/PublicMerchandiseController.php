<?php

namespace App\Http\Controllers;

use App\Models\MerchandiseProduct;
use App\Support\SettingBag;
use Illuminate\View\View;

class PublicMerchandiseController extends Controller
{
    public function index(): View
    {
        $products = MerchandiseProduct::query()
            ->active()
            ->ordered()
            ->get();

        return view('merchandise.public.index', [
            'products' => $products,
            'shopUrl' => SettingBag::merchandiseShopUrl(),
        ]);
    }

    public function show(MerchandiseProduct $merchandiseProduct): View
    {
        abort_unless($merchandiseProduct->is_active, 404);

        return view('merchandise.public.show', [
            'product' => $merchandiseProduct,
            'shopUrl' => $merchandiseProduct->shop_url ?: SettingBag::merchandiseShopUrl(),
        ]);
    }
}

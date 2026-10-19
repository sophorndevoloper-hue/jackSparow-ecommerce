<?php

namespace Database\Seeders;

use App\Models\BestSeller;
use App\Models\FeaturedComponent;
use App\Models\FlashDeal;
use App\Models\Product;
use App\Models\ProductPromotion;
use App\Models\StorefrontProduct;
use Illuminate\Database\Seeder;

class StorefrontProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::query()->where('is_active', true)->get();

        if ($products->isEmpty()) {
            return;
        }

        // 1. Featured Components Section Model & Items
        $featuredSection = FeaturedComponent::firstOrCreate(
            ['title' => 'Featured High-Performance Components'],
            [
                'description' => 'Top tier graphics cards, processors, and high-efficiency motherboards.',
                'badge' => 'FEATURED',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $featuredSlice = $products->take(8);
        $sort = 1;
        $badges = ['HOT', 'NEW', 'TOP PICK', 'BESTSELLER', 'EXCLUSIVE', 'PREMIUM', 'HOT', 'NEW'];

        foreach ($featuredSlice as $index => $product) {
            StorefrontProduct::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'section' => 'featured',
                ],
                [
                    'showcaseable_type' => FeaturedComponent::class,
                    'showcaseable_id' => $featuredSection->id,
                    'badge' => $badges[$index % count($badges)],
                    'custom_title' => null,
                    'sort_order' => $sort++,
                    'is_active' => true,
                ]
            );
        }

        // 2. Flash Deals & Clearance Section Model & Items
        $flashDealSection = FlashDeal::firstOrCreate(
            ['title' => 'Flash Deals & Hardware Clearance'],
            [
                'description' => 'Limited-time price cuts on enthusiast-grade components.',
                'badge' => 'FLASH SALE',
                'starts_at' => now()->subDays(2),
                'ends_at' => now()->addDays(7),
                'discount_percentage' => 25,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $dealSlice = $products->whereNotNull('sale_price');
        if ($dealSlice->count() < 4) {
            $dealSlice = $products->slice(2, 4);
        } else {
            $dealSlice = $dealSlice->take(4);
        }

        $dealSort = 1;
        foreach ($dealSlice as $product) {
            $savings = $product->sale_price ? '$'.number_format($product->price - $product->sale_price, 0).' OFF' : 'SALE';

            StorefrontProduct::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'section' => 'deals',
                ],
                [
                    'showcaseable_type' => FlashDeal::class,
                    'showcaseable_id' => $flashDealSection->id,
                    'badge' => $savings,
                    'custom_title' => null,
                    'sort_order' => $dealSort++,
                    'is_active' => true,
                ]
            );
        }

        // 3. Best Sellers Section Model & Items
        $bestSellerSection = BestSeller::firstOrCreate(
            ['title' => 'Community Best Sellers'],
            [
                'description' => 'Most popular and highly-rated components chosen by PC builders worldwide.',
                'badge' => 'COMMUNITY FAVORITE',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $bestsellerSlice = $products->slice(4, 4);
        $bsSort = 1;
        foreach ($bestsellerSlice as $product) {
            StorefrontProduct::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'section' => 'bestseller',
                ],
                [
                    'showcaseable_type' => BestSeller::class,
                    'showcaseable_id' => $bestSellerSection->id,
                    'badge' => 'TOP SELLER',
                    'custom_title' => null,
                    'sort_order' => $bsSort++,
                    'is_active' => true,
                ]
            );
        }

        // 4. Product Promotions Campaigns & Items
        $promo1 = ProductPromotion::firstOrCreate(
            ['title' => 'CYBER EXTREME GAMING PROMOTION'],
            [
                'subtitle' => 'Special discount on cutting-edge processors, GPUs, and high-frequency memory modules.',
                'badge' => 'HOT PROMOTION',
                'cta_text' => 'Upgrade Rig Now',
                'cta_url' => '/products',
                'start_date' => now()->subDays(3)->toDateString(),
                'end_date' => now()->addDays(14)->toDateString(),
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $promoSlice = $products->take(4);
        $promoSort = 1;
        foreach ($promoSlice as $product) {
            StorefrontProduct::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'section' => 'promotions',
                    'showcaseable_type' => ProductPromotion::class,
                    'showcaseable_id' => $promo1->id,
                ],
                [
                    'badge' => 'SPECIAL DEAL',
                    'custom_title' => null,
                    'sort_order' => $promoSort++,
                    'is_active' => true,
                ]
            );
        }

        StorefrontProduct::flushCache();
    }
}

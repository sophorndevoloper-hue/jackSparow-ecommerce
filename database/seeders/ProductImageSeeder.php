<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class ProductImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productImages = [
            // CPU
            'CPU-AMD-7950X3D' => [
                ['path' => 'products/0E9SIUDaxWHzEpRHee9JUbIwuiy3gBNHhFPsO5nZ.jpg', 'primary' => true],
                ['path' => 'products/slZE7cecTpElhAKHDp87OL1h6omEztqAszST8irL.jpg', 'primary' => false],
            ],
            'CPU-INT-14900K' => [
                ['path' => 'products/1Z0HLWRNFoJfSrmtWYNpdaAIN9irePqTTgpwcDcN.jpg', 'primary' => true],
            ],
            // GPU
            'GPU-ASUS-4080S-OC' => [
                ['path' => 'products/MqXRZtoOtRvDLFkxkQN6Macuy8yipEhSbjGm2Pcq.jpg', 'primary' => true],
            ],
            'GPU-MSI-4070TIS-V3' => [
                ['path' => 'products/9rYdElXsnaP7C9Em4pubFNNsMuGSGdyIlrNifwYp.jpg', 'primary' => true],
            ],
            'GPU-ASUS-4090-OC' => [
                ['path' => 'products/sOvTWjmgWpLHEhZ8EOxbFcGnH5ySTPW88aQSVPgV.jpg', 'primary' => true],
            ],
            // Motherboard
            'MB-MSI-B650-TOMA' => [
                ['path' => 'products/OTIQErxP23FbpdpCH9vOiW13g2uDTVVBWB7I6yDy.jpg', 'primary' => true],
            ],
            'MB-MSI-X670E-ACE' => [
                ['path' => 'products/I54Uoe7WYmKfIhATl8FLjPjTcfNScvUa9vxT6yor.jpg', 'primary' => true],
            ],
            'mother-boad-33k' => [
                ['path' => 'products/OTIQErxP23FbpdpCH9vOiW13g2uDTVVBWB7I6yDy.jpg', 'primary' => true],
            ],
            // RAM
            'RAM-COR-32G-6000C30' => [
                ['path' => 'products/ax1dVXL80BB0Jx5nr3qptTFlCBSKS8rPbbEh5mEG.jpg', 'primary' => true],
            ],
            'RAM-COR-DOM-64G' => [
                ['path' => 'products/ax1dVXL80BB0Jx5nr3qptTFlCBSKS8rPbbEh5mEG.jpg', 'primary' => true],
            ],
            // Storage
            'SSD-SAM-990PRO-2TB' => [
                ['path' => 'products/iTMtjO6ExPaENfHVFaERfANMh0ytXnkyw4AY0DJW.jpg', 'primary' => true],
            ],
            'SSD-SAM-990PRO-4TB-HS' => [
                ['path' => 'products/iTMtjO6ExPaENfHVFaERfANMh0ytXnkyw4AY0DJW.jpg', 'primary' => true],
            ],
            // PSU
            'PSU-COR-RM850X-SH' => [
                ['path' => 'products/KNNBv33anuT9Zr54J9Tg7fpIcQhkJFqEBbKXQS5n.jpg', 'primary' => true],
            ],
            'PSU-COR-HX1000I' => [
                ['path' => 'products/tIcSeDFZqFMp1YUJ4cldHgkQt8sqpdg1f1qmhben.jpg', 'primary' => true],
            ],
            // Cooling
            'COOL-NZXT-KRAK360' => [
                ['path' => 'products/ODJMgatmKV4FNUDpzn1i7F0ajjGjMSCVCOjgIc5s.jpg', 'primary' => true],
            ],
            // Cases
            'CASE-LIAN-O11V-BK' => [
                ['path' => 'products/POZRuYmaxCEd312fZRkanTNzl0axsBe6mgu6zbmR.jpg', 'primary' => true],
            ],
        ];

        foreach ($productImages as $sku => $images) {
            $product = Product::where('sku', $sku)->first();
            if (! $product) {
                continue;
            }

            foreach ($images as $index => $imageData) {
                ProductImage::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'image_path' => $imageData['path'],
                    ],
                    [
                        'is_primary' => $imageData['primary'] ?? ($index === 0),
                        'sort_order' => $index,
                    ]
                );
            }
        }
    }
}

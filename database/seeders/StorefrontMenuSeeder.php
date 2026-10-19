<?php

namespace Database\Seeders;

use App\Models\StorefrontMenu;
use Illuminate\Database\Seeder;

class StorefrontMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StorefrontMenu::truncate();

        // 1. TOP MENU (Utility links in top bar)
        $topMenus = [
            [
                'title' => 'About Us',
                'url' => '/about-us',
                'route_name' => 'about',
                'icon' => 'bi-info-circle',
                'sort_order' => 1,
            ],
            [
                'title' => 'Contact Support',
                'url' => '/contact-us',
                'route_name' => 'contact',
                'icon' => 'bi-headset',
                'sort_order' => 2,
            ],
            [
                'title' => 'Track Order',
                'url' => '/my-orders',
                'route_name' => 'my-orders',
                'icon' => 'bi-truck',
                'sort_order' => 3,
            ],
            [
                'title' => 'Official Warranty',
                'url' => '/about-us#warranty',
                'route_name' => null,
                'icon' => 'bi-shield-check',
                'sort_order' => 4,
            ],
        ];

        foreach ($topMenus as $menu) {
            StorefrontMenu::create([
                'location' => 'top',
                'parent_id' => null,
                'title' => $menu['title'],
                'url' => $menu['url'],
                'route_name' => $menu['route_name'],
                'icon' => $menu['icon'],
                'sort_order' => $menu['sort_order'],
                'is_active' => true,
            ]);
        }

        // 2. MAIN MENU (Primary navbar with parent & child dropdowns)
        $home = StorefrontMenu::create([
            'location' => 'main',
            'parent_id' => null,
            'title' => 'Home',
            'url' => '/',
            'route_name' => 'home',
            'icon' => 'bi-house-door',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $catalog = StorefrontMenu::create([
            'location' => 'main',
            'parent_id' => null,
            'title' => 'Shop Catalog',
            'url' => '/shop',
            'route_name' => 'shop',
            'icon' => 'bi-grid',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        // Parent: PC Components
        $components = StorefrontMenu::create([
            'location' => 'main',
            'parent_id' => null,
            'title' => 'PC Components',
            'url' => '/shop',
            'route_name' => 'shop',
            'icon' => 'bi-cpu',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        $compChildren = [
            ['title' => 'Processors (CPUs)', 'url' => '/shop?category=processors-cpu', 'icon' => 'bi-cpu', 'badge' => 'HOT', 'sort_order' => 1],
            ['title' => 'Graphics Cards (GPUs)', 'url' => '/shop?category=graphics-cards-gpu', 'icon' => 'bi-gpu-card', 'badge' => 'NEW', 'sort_order' => 2],
            ['title' => 'Motherboards (AM5 & Z790)', 'url' => '/shop?category=motherboards', 'icon' => 'bi-motherboard', 'sort_order' => 3],
            ['title' => 'Memory (DDR5 RAM)', 'url' => '/shop?category=memory-ram', 'icon' => 'bi-memory', 'sort_order' => 4],
            ['title' => 'Fast NVMe SSDs', 'url' => '/shop?category=storage-ssd-hdd', 'icon' => 'bi-device-ssd', 'sort_order' => 5],
        ];

        foreach ($compChildren as $child) {
            StorefrontMenu::create([
                'location' => 'main',
                'parent_id' => $components->id,
                'title' => $child['title'],
                'url' => $child['url'],
                'icon' => $child['icon'] ?? null,
                'badge' => $child['badge'] ?? null,
                'sort_order' => $child['sort_order'],
                'is_active' => true,
            ]);
        }

        // Parent: Pre-Built Systems
        $prebuilts = StorefrontMenu::create([
            'location' => 'main',
            'parent_id' => null,
            'title' => 'Pre-Built Systems',
            'url' => '/shop?search=system',
            'icon' => 'bi-pc-display',
            'sort_order' => 4,
            'is_active' => true,
        ]);

        $prebuiltChildren = [
            ['title' => 'Flagship Esports PCs', 'url' => '/shop?search=gaming', 'icon' => 'bi-joystick', 'sort_order' => 1],
            ['title' => 'Creator & 3D Workstations', 'url' => '/shop?search=workstation', 'icon' => 'bi-code-square', 'sort_order' => 2],
            ['title' => 'Custom Watercooled Rigs', 'url' => '/shop?search=watercooled', 'icon' => 'bi-droplet-half', 'sort_order' => 3],
        ];

        foreach ($prebuiltChildren as $child) {
            StorefrontMenu::create([
                'location' => 'main',
                'parent_id' => $prebuilts->id,
                'title' => $child['title'],
                'url' => $child['url'],
                'icon' => $child['icon'],
                'sort_order' => $child['sort_order'],
                'is_active' => true,
            ]);
        }

        // Single links in main menu
        StorefrontMenu::create([
            'location' => 'main',
            'parent_id' => null,
            'title' => 'Clearance Deals',
            'url' => '/shop?filter=sale',
            'icon' => 'bi-tag',
            'badge' => 'SALE',
            'sort_order' => 5,
            'is_active' => true,
        ]);

        StorefrontMenu::create([
            'location' => 'main',
            'parent_id' => null,
            'title' => 'Contact & Quotes',
            'url' => '/contact-us',
            'route_name' => 'contact',
            'icon' => 'bi-chat-dots',
            'sort_order' => 6,
            'is_active' => true,
        ]);

        // 3. FOOTER MENU (Parent columns with child links)
        // Column 1: Company Info
        $colCompany = StorefrontMenu::create([
            'location' => 'footer',
            'parent_id' => null,
            'title' => 'Company Info',
            'url' => '#',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $colCompanyChildren = [
            ['title' => 'About JackSparow TECH', 'url' => '/about-us', 'sort_order' => 1],
            ['title' => 'Official Brand Warranty', 'url' => '/about-us#warranty', 'sort_order' => 2],
            ['title' => 'Terms of Service', 'url' => '/about-us#terms', 'sort_order' => 3],
            ['title' => 'Privacy Policy', 'url' => '/about-us#privacy', 'sort_order' => 4],
        ];

        foreach ($colCompanyChildren as $child) {
            StorefrontMenu::create([
                'location' => 'footer',
                'parent_id' => $colCompany->id,
                'title' => $child['title'],
                'url' => $child['url'],
                'sort_order' => $child['sort_order'],
                'is_active' => true,
            ]);
        }

        // Column 2: Popular Hardware
        $colHardware = StorefrontMenu::create([
            'location' => 'footer',
            'parent_id' => null,
            'title' => 'Popular Hardware',
            'url' => '#',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $colHardwareChildren = [
            ['title' => 'Desktop Processors (CPU)', 'url' => '/shop?category=processors-cpu', 'sort_order' => 1],
            ['title' => 'GeForce RTX 40 GPUs', 'url' => '/shop?category=graphics-cards-gpu', 'sort_order' => 2],
            ['title' => 'DDR5 Gaming RAM', 'url' => '/shop?category=memory-ram', 'sort_order' => 3],
            ['title' => 'PCIe 4.0 & 5.0 SSDs', 'url' => '/shop?category=storage-ssd-hdd', 'sort_order' => 4],
        ];

        foreach ($colHardwareChildren as $child) {
            StorefrontMenu::create([
                'location' => 'footer',
                'parent_id' => $colHardware->id,
                'title' => $child['title'],
                'url' => $child['url'],
                'sort_order' => $child['sort_order'],
                'is_active' => true,
            ]);
        }

        // Column 3: Customer Service
        $colSupport = StorefrontMenu::create([
            'location' => 'footer',
            'parent_id' => null,
            'title' => 'Customer Service',
            'url' => '#',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        $colSupportChildren = [
            ['title' => 'View Shopping Cart', 'url' => '/cart', 'sort_order' => 1],
            ['title' => 'Track Your Orders', 'url' => '/my-orders', 'sort_order' => 2],
            ['title' => 'Contact Help Desk', 'url' => '/contact-us', 'sort_order' => 3],
            ['title' => 'Express Shipping Guide', 'url' => '/about-us#shipping', 'sort_order' => 4],
        ];

        foreach ($colSupportChildren as $child) {
            StorefrontMenu::create([
                'location' => 'footer',
                'parent_id' => $colSupport->id,
                'title' => $child['title'],
                'url' => $child['url'],
                'sort_order' => $child['sort_order'],
                'is_active' => true,
            ]);
        }

        StorefrontMenu::flushCache();
    }
}

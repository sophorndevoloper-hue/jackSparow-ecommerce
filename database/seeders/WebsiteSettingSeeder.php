<?php

namespace Database\Seeders;

use App\Models\WebsiteSetting;
use Illuminate\Database\Seeder;

class WebsiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Announcement Bar
            [
                'key' => 'announcement_enabled',
                'value' => '1',
                'group' => 'announcement',
                'type' => 'boolean',
                'label' => 'Enable Announcement Bar',
                'description' => 'Show or hide the top announcement bar on responsive storefront',
            ],
            [
                'key' => 'announcement_text',
                'value' => 'Fast Express Shipping on orders over $150',
                'group' => 'announcement',
                'type' => 'text',
                'label' => 'Primary Announcement Text',
                'description' => 'Featured promotional message displayed on the top bar',
            ],
            [
                'key' => 'announcement_subtext',
                'value' => '100% Genuine Components & Official Brand Warranty',
                'group' => 'announcement',
                'type' => 'text',
                'label' => 'Secondary Announcement Subtext',
                'description' => 'Trust badge or guarantee statement on top bar',
            ],

            // Branding & Contact
            [
                'key' => 'site_name',
                'value' => 'JackSparow TECH',
                'group' => 'branding',
                'type' => 'text',
                'label' => 'Store Name / Brand',
                'description' => 'Primary brand name rendered in the header and metadata',
            ],
            [
                'key' => 'site_tagline',
                'value' => 'Computer Hardware & Parts',
                'group' => 'branding',
                'type' => 'text',
                'label' => 'Store Tagline / Slogan',
                'description' => 'Subtitle text below store logo',
            ],
            [
                'key' => 'contact_phone',
                'value' => '+1 (800) 555-PC-RIG',
                'group' => 'branding',
                'type' => 'text',
                'label' => 'Support Hotline Phone',
                'description' => 'Public customer service contact number',
            ],
            [
                'key' => 'contact_email',
                'value' => 'support@jacksparow-tech.test',
                'group' => 'branding',
                'type' => 'text',
                'label' => 'Support Email Address',
                'description' => 'Public support email displayed in footer and checkout',
            ],
            [
                'key' => 'contact_address',
                'value' => '100 Technology Plaza, Silicon Way',
                'group' => 'branding',
                'type' => 'text',
                'label' => 'Store Physical Address / Headquarters',
                'description' => 'Store location displayed in footer',
            ],
            [
                'key' => 'free_shipping_threshold',
                'value' => '150.00',
                'group' => 'branding',
                'type' => 'number',
                'label' => 'Free Shipping Threshold ($)',
                'description' => 'Minimum cart amount eligible for free delivery',
            ],

            // Hero Section
            [
                'key' => 'hero_badge',
                'value' => 'Next-Gen PC Hardware In Stock',
                'group' => 'hero',
                'type' => 'text',
                'label' => 'Hero Badge Text',
                'description' => 'Small glowing pill tag above main headline',
            ],
            [
                'key' => 'hero_title',
                'value' => 'Build Your Ultimate Gaming Rig',
                'group' => 'hero',
                'type' => 'text',
                'label' => 'Hero Main Headline',
                'description' => 'Large high-impact headline on homepage hero banner',
            ],
            [
                'key' => 'hero_subtitle',
                'value' => 'Explore top-tier desktop processors, NVIDIA RTX 40-Series graphics cards, blazing fast Gen4 NVMe storage, and high-frequency DDR5 memory with official warranty.',
                'group' => 'hero',
                'type' => 'textarea',
                'label' => 'Hero Subtitle Description',
                'description' => 'Detailed paragraph describing store offerings',
            ],
            [
                'key' => 'hero_primary_btn_text',
                'value' => 'Browse All Components',
                'group' => 'hero',
                'type' => 'text',
                'label' => 'Hero Primary Button Text',
                'description' => 'Label for the main call-to-action button',
            ],
            [
                'key' => 'hero_primary_btn_url',
                'value' => '/shop',
                'group' => 'hero',
                'type' => 'text',
                'label' => 'Hero Primary Button URL',
                'description' => 'Target link for primary CTA button',
            ],
            [
                'key' => 'hero_secondary_btn_text',
                'value' => 'Shop GPUs',
                'group' => 'hero',
                'type' => 'text',
                'label' => 'Hero Secondary Button Text',
                'description' => 'Label for secondary CTA button',
            ],
            [
                'key' => 'hero_secondary_btn_url',
                'value' => '/shop?category=graphics-cards-gpu',
                'group' => 'hero',
                'type' => 'text',
                'label' => 'Hero Secondary Button URL',
                'description' => 'Target link for secondary CTA button',
            ],

            // Feature Highlights (4 Cards)
            [
                'key' => 'feature_1_icon',
                'value' => '⚡',
                'group' => 'features',
                'type' => 'text',
                'label' => 'Card 1 Icon / Emoji',
                'description' => 'Icon or emoji for highlight card 1',
            ],
            [
                'key' => 'feature_1_title',
                'value' => 'Flagship Processors',
                'group' => 'features',
                'type' => 'text',
                'label' => 'Card 1 Title',
                'description' => 'Heading for highlight card 1',
            ],
            [
                'key' => 'feature_1_desc',
                'value' => 'AMD Ryzen 7000X3D & Intel 14th Gen Core i9 ready for max FPS and intense rendering.',
                'group' => 'features',
                'type' => 'textarea',
                'label' => 'Card 1 Description',
                'description' => 'Explanation text for highlight card 1',
            ],

            [
                'key' => 'feature_2_icon',
                'value' => '🎮',
                'group' => 'features',
                'type' => 'text',
                'label' => 'Card 2 Icon / Emoji',
                'description' => 'Icon or emoji for highlight card 2',
            ],
            [
                'key' => 'feature_2_title',
                'value' => 'RTX 40 SUPER GPUs',
                'group' => 'features',
                'type' => 'text',
                'label' => 'Card 2 Title',
                'description' => 'Heading for highlight card 2',
            ],
            [
                'key' => 'feature_2_desc',
                'value' => 'Ray tracing, DLSS 3 frame generation, and massive 16GB VRAM for 4K ray-traced gaming.',
                'group' => 'features',
                'type' => 'textarea',
                'label' => 'Card 2 Description',
                'description' => 'Explanation text for highlight card 2',
            ],

            [
                'key' => 'feature_3_icon',
                'value' => '🚀',
                'group' => 'features',
                'type' => 'text',
                'label' => 'Card 3 Icon / Emoji',
                'description' => 'Icon or emoji for highlight card 3',
            ],
            [
                'key' => 'feature_3_title',
                'value' => 'Ultra NVMe SSDs',
                'group' => 'features',
                'type' => 'text',
                'label' => 'Card 3 Title',
                'description' => 'Heading for highlight card 3',
            ],
            [
                'key' => 'feature_3_desc',
                'value' => 'Up to 7,450 MB/s read speeds with Samsung 990 Pro & WD_BLACK for instant game loading.',
                'group' => 'features',
                'type' => 'textarea',
                'label' => 'Card 3 Description',
                'description' => 'Explanation text for highlight card 3',
            ],

            [
                'key' => 'feature_4_icon',
                'value' => '🛡️',
                'group' => 'features',
                'type' => 'text',
                'label' => 'Card 4 Icon / Emoji',
                'description' => 'Icon or emoji for highlight card 4',
            ],
            [
                'key' => 'feature_4_title',
                'value' => 'Official Warranty',
                'group' => 'features',
                'type' => 'text',
                'label' => 'Card 4 Title',
                'description' => 'Heading for highlight card 4',
            ],
            [
                'key' => 'feature_4_desc',
                'value' => 'Up to 10-year manufacturer warranty with guaranteed authentic retail hardware packaging.',
                'group' => 'features',
                'type' => 'textarea',
                'label' => 'Card 4 Description',
                'description' => 'Explanation text for highlight card 4',
            ],

            // Footer & Social Links
            [
                'key' => 'footer_about',
                'value' => 'Your trusted destination for custom gaming computer parts, next-gen CPUs, GPUs, high-speed RAM, and extreme PC cooling systems.',
                'group' => 'footer',
                'type' => 'textarea',
                'label' => 'Footer About Description',
                'description' => 'Summary text displayed under the brand logo in the footer',
            ],
            [
                'key' => 'footer_copyright',
                'value' => 'JackSparow TECH E-Commerce. All rights reserved.',
                'group' => 'footer',
                'type' => 'text',
                'label' => 'Copyright Statement',
                'description' => 'Bottom copyright statement',
            ],
            [
                'key' => 'social_facebook',
                'value' => 'https://facebook.com',
                'group' => 'footer',
                'type' => 'text',
                'label' => 'Facebook Page URL',
                'description' => 'Social media profile link',
            ],
            [
                'key' => 'social_twitter',
                'value' => 'https://twitter.com',
                'group' => 'footer',
                'type' => 'text',
                'label' => 'Twitter / X Profile URL',
                'description' => 'Social media profile link',
            ],
            [
                'key' => 'social_instagram',
                'value' => 'https://instagram.com',
                'group' => 'footer',
                'type' => 'text',
                'label' => 'Instagram Profile URL',
                'description' => 'Social media profile link',
            ],
            [
                'key' => 'social_discord',
                'value' => 'https://discord.gg',
                'group' => 'footer',
                'type' => 'text',
                'label' => 'Discord Server Invite URL',
                'description' => 'Community gaming discord link',
            ],
            [
                'key' => 'social_youtube',
                'value' => 'https://youtube.com',
                'group' => 'footer',
                'type' => 'text',
                'label' => 'YouTube Channel URL',
                'description' => 'Hardware reviews & unboxing channel link',
            ],
        ];

        foreach ($settings as $setting) {
            WebsiteSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        WebsiteSetting::flushCache();
    }
}

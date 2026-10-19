<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\UserActivity;
use App\Models\WebsiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WebsiteSettingController extends Controller
{
    /**
     * Display the website content management setup dashboard.
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('admin.storefront-menus.index');
    }

    /**
     * Show hero and announcement tab (redirects).
     */
    public function hero(Request $request): RedirectResponse
    {
        return redirect()->route('admin.storefront-menus.index');
    }

    /**
     * Show branding and contact tab (redirects).
     */
    public function branding(Request $request): RedirectResponse
    {
        return redirect()->route('admin.storefront-menus.index');
    }

    /**
     * Show feature highlights tab (redirects).
     */
    public function features(Request $request): RedirectResponse
    {
        return redirect()->route('admin.storefront-menus.index');
    }

    /**
     * Show footer and social links tab (redirects).
     */
    public function footer(Request $request): RedirectResponse
    {
        return redirect()->route('admin.storefront-menus.index');
    }

    /**
     * Update website settings in database and refresh cache.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Tab identifier
            'active_tab' => ['nullable', 'string', 'in:hero,features,footer'],

            // Announcement
            'announcement_enabled' => ['nullable'],
            'announcement_text' => ['nullable', 'string', 'max:255'],
            'announcement_subtext' => ['nullable', 'string', 'max:255'],

            // Branding & Contact
            'site_name' => ['nullable', 'string', 'max:255'],
            'site_tagline' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'string', 'email', 'max:100'],
            'contact_address' => ['nullable', 'string', 'max:255'],
            'free_shipping_threshold' => ['nullable', 'numeric', 'min:0'],

            // Hero Section
            'hero_badge' => ['nullable', 'string', 'max:100'],
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_subtitle' => ['nullable', 'string', 'max:1000'],
            'hero_primary_btn_text' => ['nullable', 'string', 'max:100'],
            'hero_primary_btn_url' => ['nullable', 'string', 'max:255'],
            'hero_secondary_btn_text' => ['nullable', 'string', 'max:100'],
            'hero_secondary_btn_url' => ['nullable', 'string', 'max:255'],

            // Features (1 - 4)
            'feature_1_icon' => ['nullable', 'string', 'max:50'],
            'feature_1_title' => ['nullable', 'string', 'max:100'],
            'feature_1_desc' => ['nullable', 'string', 'max:255'],

            'feature_2_icon' => ['nullable', 'string', 'max:50'],
            'feature_2_title' => ['nullable', 'string', 'max:100'],
            'feature_2_desc' => ['nullable', 'string', 'max:255'],

            'feature_3_icon' => ['nullable', 'string', 'max:50'],
            'feature_3_title' => ['nullable', 'string', 'max:100'],
            'feature_3_desc' => ['nullable', 'string', 'max:255'],

            'feature_4_icon' => ['nullable', 'string', 'max:50'],
            'feature_4_title' => ['nullable', 'string', 'max:100'],
            'feature_4_desc' => ['nullable', 'string', 'max:255'],

            // Footer & Social
            'footer_about' => ['nullable', 'string', 'max:1000'],
            'footer_copyright' => ['nullable', 'string', 'max:255'],
            'social_facebook' => ['nullable', 'string', 'max:255'],
            'social_twitter' => ['nullable', 'string', 'max:255'],
            'social_instagram' => ['nullable', 'string', 'max:255'],
            'social_discord' => ['nullable', 'string', 'max:255'],
            'social_youtube' => ['nullable', 'string', 'max:255'],
        ]);

        $activeTab = $validated['active_tab'] ?? 'hero';

        // Map keys to their group
        $groupMap = [
            'announcement_enabled' => 'announcement',
            'announcement_text' => 'announcement',
            'announcement_subtext' => 'announcement',
            'site_name' => 'branding',
            'site_tagline' => 'branding',
            'contact_phone' => 'branding',
            'contact_email' => 'branding',
            'contact_address' => 'branding',
            'free_shipping_threshold' => 'branding',
            'hero_badge' => 'hero',
            'hero_title' => 'hero',
            'hero_subtitle' => 'hero',
            'hero_primary_btn_text' => 'hero',
            'hero_primary_btn_url' => 'hero',
            'hero_secondary_btn_text' => 'hero',
            'hero_secondary_btn_url' => 'hero',
            'feature_1_icon' => 'features',
            'feature_1_title' => 'features',
            'feature_1_desc' => 'features',
            'feature_2_icon' => 'features',
            'feature_2_title' => 'features',
            'feature_2_desc' => 'features',
            'feature_3_icon' => 'features',
            'feature_3_title' => 'features',
            'feature_3_desc' => 'features',
            'feature_4_icon' => 'features',
            'feature_4_title' => 'features',
            'feature_4_desc' => 'features',
            'footer_about' => 'footer',
            'footer_copyright' => 'footer',
            'social_facebook' => 'footer',
            'social_twitter' => 'footer',
            'social_instagram' => 'footer',
            'social_discord' => 'footer',
            'social_youtube' => 'footer',
        ];

        // Handle checkbox toggles if on the relevant tab
        if ($activeTab === 'hero' || ! isset($validated['active_tab'])) {
            $validated['announcement_enabled'] = $request->has('announcement_enabled') ? '1' : '0';
        }

        foreach ($validated as $key => $val) {
            if ($key === 'active_tab') {
                continue;
            }

            $group = $groupMap[$key] ?? 'general';
            WebsiteSetting::set($key, $val, $group);
        }

        WebsiteSetting::flushCache();

        UserActivity::log(
            'UPDATE',
            'Website Settings',
            "Updated storefront configuration tab '{$activeTab}'",
            null,
            ['tab' => $activeTab]
        );

        return redirect()->route('admin.storefront-menus.index')
            ->with('success', 'Website settings updated successfully! Storefront has been updated.');
    }
}

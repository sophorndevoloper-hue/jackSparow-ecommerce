<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\WebsiteSetting;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    /**
     * Display the About Us page.
     */
    public function about(): View
    {
        $siteName = WebsiteSetting::get('site_name', 'JackSparow TECH');
        $siteTagline = WebsiteSetting::get('site_tagline', 'Computer Hardware & Parts');
        $footerAbout = WebsiteSetting::get('footer_about', 'Your trusted destination for custom gaming computer parts, next-gen CPUs, GPUs, high-speed RAM, and extreme PC cooling systems.');
        $contactPhone = WebsiteSetting::get('contact_phone', '+1 (800) 555-PC-RIG');
        $contactEmail = WebsiteSetting::get('contact_email', 'support@jacksparow-tech.test');
        $contactAddress = WebsiteSetting::get('contact_address', '100 Technology Plaza, Silicon Way');

        return view('frontend.pages.about', compact(
            'siteName',
            'siteTagline',
            'footerAbout',
            'contactPhone',
            'contactEmail',
            'contactAddress'
        ));
    }

    /**
     * Display the Contact Support & RFQ page.
     */
    public function contact(): View
    {
        $siteName = WebsiteSetting::get('site_name', 'JackSparow TECH');
        $contactPhone = WebsiteSetting::get('contact_phone', '+1 (800) 555-PC-RIG');
        $contactEmail = WebsiteSetting::get('contact_email', 'support@jacksparow-tech.test');
        $contactAddress = WebsiteSetting::get('contact_address', '100 Technology Plaza, Silicon Way');

        return view('frontend.pages.contact', compact(
            'siteName',
            'contactPhone',
            'contactEmail',
            'contactAddress'
        ));
    }
}

<?php namespace RainLab\GoogleAnalytics\Components;

use Cms\Classes\ComponentBase;
use RainLab\GoogleAnalytics\Models\Settings;

/**
 * Tracker component
 */
class Tracker extends ComponentBase
{
    /**
     * componentDetails
     */
    public function componentDetails()
    {
        return [
            'name' => 'rainlab.googleanalytics::lang.strings.tracker',
            'description' => 'rainlab.googleanalytics::lang.strings.tracker_desc'
        ];
    }

    /**
     * trackingId
     */
    public function trackingId()
    {
        return Settings::get('tracking_id');
    }

    /**
     * domainName
     */
    public function domainName()
    {
        return Settings::get('domain_name');
    }

    /**
     * anonymizeIp
     */
    public function anonymizeIp()
    {
        return Settings::get('anonymize_ip');
    }

    /**
     * forceSSL
     */
    public function forceSSL()
    {
        return Settings::get('force_ssl');
    }
}

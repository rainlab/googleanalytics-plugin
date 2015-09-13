<?php namespace RainLab\GoogleAnalytics\Components;

use Cms\Classes\ComponentBase;
use RainLab\GoogleAnalytics\Models\Settings;

class Tracker extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'rainlab.googleanalytics::lang.strings.tracker',
            'description' => 'rainlab.googleanalytics::lang.strings.tracker_desc'
        ];
    }

    public function trackingId()
    {
        return Settings::get('tracking_id');
    }

    public function domainName()
    {
        return Settings::get('domain_name');
    }
}
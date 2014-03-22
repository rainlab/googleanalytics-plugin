<?php namespace RainLab\GoogleAnalytics\Components;

use Cms\Classes\ComponentBase;
use RainLab\GoogleAnalytics\Models\Settings;

class Tracker extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Google Analytics tracker',
            'description' => 'Outputs a tracking code on a page.'
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
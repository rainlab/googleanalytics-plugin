<?php namespace RainLab\GoogleAnalytics\Classes;

use App;

use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use ApplicationException;
use RainLab\GoogleAnalytics\Models\Settings;

class Analytics
{
    use \October\Rain\Support\Traits\Singleton;

    /**
     * @var Google\Analytics\Data\V1beta\BetaAnalyticsDataClient
     */
    public $client;

    /**
     * @var string Google Analytics Property ID
     */
    public $propertyId;

    protected function init()
    {
        $settings = Settings::instance();
        if (!strlen($settings->profile_id)) {
            throw new ApplicationException(trans('rainlab.googleanalytics::lang.strings.notconfigured'));
        }

        if (!$settings->gapi_key) {
            throw new ApplicationException(trans('rainlab.googleanalytics::lang.strings.keynotuploaded'));
        }

        $this->client = new BetaAnalyticsDataClient([
            'credentials' => $settings->gapi_key->getLocalPath()
        ]);

        $this->propertyId = $settings->profile_id;
    }
}
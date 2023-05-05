<?php namespace RainLab\GoogleAnalytics\Classes;

use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use RainLab\GoogleAnalytics\Models\Settings;
use ApplicationException;

/**
 * Analytics
 *
 * @package rainlab/googleanalytics
 * @author Alexey Bobkov, Samuel Georges
 */
class Analytics
{
    use \October\Rain\Support\Traits\Singleton;

    /**
     * @var \Google\Analytics\Data\V1beta\BetaAnalyticsDataClient client
     */
    public $client;

    /**
     * @var string propertyId for Google Analytics
     */
    public $propertyId;

    /**
     * init
     */
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

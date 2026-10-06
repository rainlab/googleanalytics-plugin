<?php namespace RainLab\GoogleAnalytics\Classes;

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Auth\Credentials\ServiceAccountCredentials;
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
     * @var \Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient client
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
            throw new ApplicationException(__("Google Analytics API access is not configured. Please configure it on the System / Settings / Google Analytics page."));
        }

        if (!$settings->gapi_key) {
            throw new ApplicationException(__("Google Analytics API private key is not uploaded. Please configure Google Analytics access on the System / Settings / Google Analytics page."));
        }

        $credentials = new ServiceAccountCredentials(
            'https://www.googleapis.com/auth/analytics.readonly',
            $settings->gapi_key->getLocalPath()
        );

        // Sign tokens locally, as the client library does for key files
        $credentials->useJwtAccessWithScope();

        $this->client = new BetaAnalyticsDataClient([
            'credentials' => $credentials
        ]);

        $this->propertyId = $settings->profile_id;
    }
}

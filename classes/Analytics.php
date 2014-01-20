<?php namespace RainLab\GoogleAnalytics\Classes;

use RainLab\GoogleAnalytics\Models\Settings;
use System\Classes\ApplicationException;
use Google_Auth_AssertionCredentials;
use Google_Service_Analytics;
use Google_Client;

class Analytics
{
    use \October\Rain\Support\Traits\Singleton;

    /**
     * @var Google_Client Google API client
     */
    public $client; 

    /**
     * @var Google_Service_Analytics Google API analytics service
     */
    public $service; 

    /**
     * @var string Google Analytics View ID
     */
    public $viewId;

    protected function init()
    {
        $settings = Settings::instance();
        if (!strlen($settings->projectName))
            throw new ApplicationException('Google Analytics API access is not configured. Please configure it on the System / Settings / Google Analytics page.');

        if (!$settings->gapiKey)
            throw new ApplicationException('Google Analytics API private key is not uploaded. Please configure Google Analytics access on the System / Settings / Google Analytics page.');

        $this->client = new Google_Client();
        $this->client->setApplicationName($settings->projectName);

        // set assertion credentials
        $this->client->setAssertionCredentials(
          new Google_Auth_AssertionCredentials(
            $settings->appEmail,
            array(Google_Service_Analytics::ANALYTICS_READONLY),
            $settings->gapi_key->getContents()
        ));

        $this->client->setClientId($settings->clientId);
        $this->service = new Google_Service_Analytics($this->client);
        $this->viewId = 'ga:'.$settings->profileId;
    }
}
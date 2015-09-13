<?php namespace RainLab\GoogleAnalytics\Classes;

use Config;
use Google_Client;
use Google_Service_Analytics;
use Google_Auth_AssertionCredentials;
use ApplicationException;
use RainLab\GoogleAnalytics\Models\Settings;

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
        if (!strlen($settings->project_name))
            throw new ApplicationException(trans('rainlab.googleanalytics::lang.strings.notconfigured'));

        if (!$settings->gapi_key)
            throw new ApplicationException(trans('rainlab.googleanalytics::lang.strings.keynotuploaded'));

        $tmpDir = temp_path() . '/Google_Client';

        $this->client = new Google_Client();
        $this->client->setApplicationName($settings->project_name);
        $this->client->setClassConfig('Google_Cache_File', 'directory', $tmpDir);

        /*
         * Set assertion credentials
         */
        $this->client->setAssertionCredentials(
          new Google_Auth_AssertionCredentials(
            $settings->app_email,
            array(Google_Service_Analytics::ANALYTICS_READONLY),
            $settings->gapi_key->getContents()
        ));

        $this->client->setClientId($settings->client_id);
        $this->service = new Google_Service_Analytics($this->client);
        $this->viewId = 'ga:'.$settings->profile_id;
    }
}
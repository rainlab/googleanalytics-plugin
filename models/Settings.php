<?php namespace RainLab\GoogleAnalytics\Models;

use Config;
use System\Models\SettingModel;

/**
 * Settings for Google Analytics
 *
 * @package rainlab/googleanalytics
 * @author Alexey Bobkov, Samuel Georges
 */
class Settings extends SettingModel
{
    use \October\Rain\Database\Traits\Multisite;
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string settingsCode
     */
    public $settingsCode = 'rainlab_googleanalytics_settings';

    /**
     * @var string settingsFields
     */
    public $settingsFields = 'fields.yaml';

    /**
     * @var array propagatable fields
     */
    protected $propagatable = [];

    /**
     * @var array attachOne
     */
    public $attachOne = [
        'gapi_key' => [
            \System\Models\File::class,
            'public' => false
        ]
    ];

    /**
     * rules for validation
     */
    public $rules = [
        'gapi_key' => 'required_with:profile_id',
        'profile_id' => 'required_with:gapi_key'
    ];

    /**
     * initSettingsData
     */
    public function initSettingsData()
    {
        $this->domain_name = 'auto';
        $this->anonymize_ip = false;
        $this->force_ssl = false;
    }

    /**
     * isMultisiteEnabled allows for programmatic toggling
     * @return bool
     */
    public function isMultisiteEnabled()
    {
        return (bool) Config::get('multisite.features.rainlab_googleanalytics_setting', false);
    }
}

<?php namespace RainLab\GoogleAnalytics\Models;

use October\Rain\Database\Model;

/**
 * Settings for Google Analytics
 *
 * @package rainlab/googleanalytics
 * @author Alexey Bobkov, Samuel Georges
 */
class Settings extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var array implement
     */
    public $implement = [
        \System\Behaviors\SettingsModel::class
    ];

    /**
     * @var string settingsCode
     */
    public $settingsCode = 'rainlab_googleanalytics_settings';

    /**
     * @var string settingsFields
     */
    public $settingsFields = 'fields.yaml';

    /**
     * @var array attachOne
     */
    public $attachOne = [
        'gapi_key' => ['System\Models\File', 'public' => false]
    ];

    /**
     * rules for validation
     */
    public $rules = [
        'gapi_key'   => 'required_with:profile_id',
        'profile_id'   => 'required_with:gapi_key'
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
}

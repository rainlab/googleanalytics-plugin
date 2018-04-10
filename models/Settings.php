<?php namespace RainLab\GoogleAnalytics\Models;

use October\Rain\Database\Model;

/**
 * Google Analytics settings model
 *
 * @package system
 * @author Alexey Bobkov, Samuel Georges
 *
 */
class Settings extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'rainlab_googleanalytics_settings';

    public $settingsFields = 'fields.yaml';

    public $attachOne = [
        'gapi_key' => ['System\Models\File', 'public' => false]
    ];

    /**
     * Validation rules
     */
    public $rules = [
        'gapi_key'   => 'required_with:profile_id',
        'profile_id'   => 'required_with:gapi_key'
    ];

    public function initSettingsData()
    {
        $this->domain_name = 'auto';
        $this->anonymize_ip = false;
        $this->force_ssl = false;
    }
}
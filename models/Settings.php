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
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'rainlab_googleanalytics_settings';

    public $settingsFields = 'fields.yaml';

    public $morphOne = [
        'gapiKey' => ['System\Models\File', 'name' => 'attachment']
    ];

    /**
     * Validation rules
     */

    // public $rules = [
    //     'projectName' => 'required',
    //     'clientId' => 'required',
    //     'appEmail' => 'required|email',
    //     'profileId' => 'required'
    // ];
}
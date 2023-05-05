<?php namespace RainLab\GoogleAnalytics;

use System\Classes\PluginBase;

/**
 * Plugin base class
 *
 * @package rainlab/googleanalytics
 * @author Alexey Bobkov, Samuel Georges
 */
class Plugin extends PluginBase
{
    /**
     * pluginDetails
     */
    public function pluginDetails()
    {
        return [
            'name' => 'Google Analytics',
            'description' => 'rainlab.googleanalytics::lang.strings.plugin_desc',
            'author' => 'Alexey Bobkov, Samuel Georges',
            'icon' => 'icon-bar-chart-o',
            'homepage' => 'https://github.com/rainlab/googleanalytics-plugin'
        ];
    }

    /**
     * boot
     */
    public function boot()
    {
        set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__.'/vendor/google/apiclient/src');
    }

    /**
     * registerComponents
     */
    public function registerComponents()
    {
        return [
            \RainLab\GoogleAnalytics\Components\Tracker::class => 'googleTracker'
        ];
    }

    /**
     * registerPermissions
     */
    public function registerPermissions()
    {
        return [
            'rainlab.googleanalytics.access_settings' => [
                'tab' => 'rainlab.googleanalytics::lang.permissions.tab',
                'label' => 'rainlab.googleanalytics::lang.permissions.settings'
            ],
            'rainlab.googleanalytics.view_widgets' => [
                'tab' => 'rainlab.googleanalytics::lang.permissions.tab',
                'label' => 'rainlab.googleanalytics::lang.permissions.widgets'
            ]
        ];
    }

    /**
     * registerReportWidgets
     */
    public function registerReportWidgets()
    {
        return [
            'RainLab\GoogleAnalytics\ReportWidgets\TrafficOverview' => [
                'label' => 'Google Analytics traffic overview',
                'context' => 'dashboard',
                'permissions' => ['rainlab.googleanalytics.view_widgets']
            ],
            'RainLab\GoogleAnalytics\ReportWidgets\TrafficSources' => [
                'label' => 'Google Analytics traffic sources',
                'context' => 'dashboard',
                'permissions' => ['rainlab.googleanalytics.view_widgets']
            ],
            'RainLab\GoogleAnalytics\ReportWidgets\Browsers' => [
                'label' => 'Google Analytics browsers',
                'context' => 'dashboard',
                'permissions' => ['rainlab.googleanalytics.view_widgets']
            ],
            'RainLab\GoogleAnalytics\ReportWidgets\TrafficGoal' => [
                'label' => 'Google Analytics traffic goal',
                'context' => 'dashboard',
                'permissions' => ['rainlab.googleanalytics.view_widgets']
            ],
            'RainLab\GoogleAnalytics\ReportWidgets\TopPages' => [
                'label' => 'Google Analytics top pages',
                'context' => 'dashboard',
                'permissions' => ['rainlab.googleanalytics.view_widgets']
            ]
        ];
    }

    /**
     * registerSettings
     */
    public function registerSettings()
    {
        return [
            'config' => [
                'label' => 'Google Analytics',
                'icon' => 'icon-bar-chart-o',
                'description' => 'rainlab.googleanalytics::lang.strings.settings_desc',
                'class' => \RainLab\GoogleAnalytics\Models\Settings::class,
                'permissions' => ['rainlab.googleanalytics.access_settings'],
                'order' => 600
            ]
        ];
    }
}

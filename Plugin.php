<?php namespace RainLab\GoogleAnalytics;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name'        => 'Google Analytics',
            'description' => 'rainlab.googleanalytics::lang.strings.plugin_desc',
            'author'      => 'Alexey Bobkov, Samuel Georges',
            'icon'        => 'icon-bar-chart-o',
            'homepage'    => 'https://github.com/rainlab/googleanalytics-plugin'
        ];
    }

    public function registerComponents()
    {
        return [
            '\RainLab\GoogleAnalytics\Components\Tracker' => 'googleTracker'
        ];
    }

    public function registerPermissions()
    {
        return [
            'rainlab.googleanalytics.access_settings' => [
                'tab'   => 'rainlab.googleanalytics::lang.permissions.tab',
                'label' => 'rainlab.googleanalytics::lang.permissions.settings'
            ]
        ];
    }

    public function registerReportWidgets()
    {
        return [
            'RainLab\GoogleAnalytics\ReportWidgets\TrafficOverview'=>[
                'label'   => 'Google Analytics traffic overview',
                'context' => 'dashboard'
            ],
            'RainLab\GoogleAnalytics\ReportWidgets\TrafficSources'=>[
                'label'   => 'Google Analytics traffic sources',
                'context' => 'dashboard'
            ],
            'RainLab\GoogleAnalytics\ReportWidgets\Browsers'=>[
                'label'   => 'Google Analytics browsers',
                'context' => 'dashboard'
            ],
            'RainLab\GoogleAnalytics\ReportWidgets\TrafficGoal'=>[
                'label'   => 'Google Analytics traffic goal',
                'context' => 'dashboard'
            ],
            'RainLab\GoogleAnalytics\ReportWidgets\TopPages'=>[
                'label'   => 'Google Analytics top pages',
                'context' => 'dashboard'
            ]
        ];
    }

    public function registerSettings()
    {
        return [
            'config' => [
                'label'       => 'Google Analytics',
                'icon'        => 'icon-bar-chart-o',
                'description' => 'rainlab.googleanalytics::lang.strings.settings_desc',
                'class'       => 'RainLab\GoogleAnalytics\Models\Settings',
                'permissions' => ['rainlab.googleanalytics.access_settings'],
                'order'       => 600
            ]
        ];
    }

    public function boot()
    {
        set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__.'/vendor/google/apiclient/src');
    }
}

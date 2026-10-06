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
            'description' => 'Provides the Google Analytics tracking and reporting.',
            'author' => 'October CMS',
            'icon' => 'icon-bar-chart-o',
            'homepage' => 'https://github.com/rainlab/googleanalytics-plugin'
        ];
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
                'tab' => 'Google Analytics Plugin',
                'label' => 'Settings access'
            ],
            'rainlab.googleanalytics.view_widgets' => [
                'tab' => 'Google Analytics Plugin',
                'label' => 'View dashboard widgets'
            ]
        ];
    }

    /**
     * registerDashboards
     */
    public function registerDashboards()
    {
        return [
            'dashboards' => [
                'google-analytics' => '$/rainlab/googleanalytics/dashboards/google-analytics.yaml'
            ],
            'widgets' => [
                \RainLab\GoogleAnalytics\ReportWidgets\TrafficOverview::class => [
                    'label' => 'Google Analytics traffic overview',
                    'permissions' => ['rainlab.googleanalytics.view_widgets']
                ],
                \RainLab\GoogleAnalytics\ReportWidgets\TrafficSources::class => [
                    'label' => 'Google Analytics traffic sources',
                    'permissions' => ['rainlab.googleanalytics.view_widgets']
                ],
                \RainLab\GoogleAnalytics\ReportWidgets\Browsers::class => [
                    'label' => 'Google Analytics browsers',
                    'permissions' => ['rainlab.googleanalytics.view_widgets']
                ],
                \RainLab\GoogleAnalytics\ReportWidgets\TrafficGoal::class => [
                    'label' => 'Google Analytics traffic goal',
                    'permissions' => ['rainlab.googleanalytics.view_widgets']
                ],
                \RainLab\GoogleAnalytics\ReportWidgets\TopPages::class => [
                    'label' => 'Google Analytics top pages',
                    'permissions' => ['rainlab.googleanalytics.view_widgets']
                ]
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
                'description' => 'Configure Google Analytics API code and tracking options.',
                'class' => \RainLab\GoogleAnalytics\Models\Settings::class,
                'permissions' => ['rainlab.googleanalytics.access_settings'],
                'order' => 600
            ]
        ];
    }
}

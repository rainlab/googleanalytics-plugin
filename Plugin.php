<?php namespace RainLab\GoogleAnalytics;

/**
 * The plugin.php file (called the plugin initialization script) defines the plugin information class.
 */

use System\Classes\PluginBase;

class Plugin extends PluginBase
{

    public function pluginDetails()
    {
        return [
            'name' => 'Google Analytics',
            'description' => 'Provides the Google Analytics tracking and reporting.',
            'author' => 'Alexey Bobkov, Samuel Georges',
            'icon' => 'icon-bar-chart-o'
        ];
    }

    public function registerComponents()
    {
        return [
            '\RainLab\GoogleAnalytics\Components\Tracker' => 'googleTracker'
        ];
    }

    public function registerReportWidgets()
    {
        return [
            'RainLab\GoogleAnalytics\ReportWidgets\TrafficOverview'=>[
                'name'=>'Google Analytics traffic overview',
                'context'=>'dashboard'
            ],
            'RainLab\GoogleAnalytics\ReportWidgets\TrafficSources'=>[
                'name'=>'Google Analytics traffic sources',
                'context'=>'dashboard'
            ],
            'RainLab\GoogleAnalytics\ReportWidgets\Browsers'=>[
                'name'=>'Google Analytics browsers',
                'context'=>'dashboard'
            ],
            'RainLab\GoogleAnalytics\ReportWidgets\TrafficGoal'=>[
                'name'=>'Google Analytics traffic goal',
                'context'=>'dashboard'
            ],
            'RainLab\GoogleAnalytics\ReportWidgets\TopPages'=>[
                'name'=>'Google Analytics top pages',
                'context'=>'dashboard'
            ]
        ];
    }

    public function registerSettings()
    {
        return [
            'config' => [
                'label' => 'Google Analytics',
                'icon' => 'icon-bar-chart-o',
                'description' => 'Configure Google Analytics API code and tracking options.',
                'class' => 'RainLab\GoogleAnalytics\Models\Settings',
                'order' => 100
            ]
        ];
    }
}
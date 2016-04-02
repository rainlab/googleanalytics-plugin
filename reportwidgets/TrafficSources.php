<?php

namespace RainLab\GoogleAnalytics\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use RainLab\GoogleAnalytics\Classes\Analytics;
use ApplicationException;
use Exception;

/**
 * Google Analytics traffic sources widget.
 *
 * @package backend
 * @author Alexey Bobkov, Samuel Georges
 */
class TrafficSources extends ReportWidgetBase
{

    /**
     * Renders the widget.
     */
    public function render()
    {
        try
        {
            $this->loadData();
        } catch (Exception $ex)
        {
            $this->vars['error'] = $ex->getMessage();
        }

        return $this->makePartial('widget');
    }

    public function defineProperties()
    {
        return [
            'title' => [
                'title' => 'backend::lang.dashboard.widget_title_label',
                'default' => e(trans('rainlab.googleanalytics::lang.widgets.title_traffic_sources')),
                'type' => 'string',
                'validationPattern' => '^.+$',
                'validationMessage' => 'backend::lang.dashboard.widget_title_error'
            ],
            'reportSize' => [
                'title' => 'Chart radius',
                'default' => '150',
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Please specify the chart size as an integer value.'
            ],
            'center' => [
                'title' => 'Center the chart',
                'type' => 'checkbox'
            ],
            'legendAsTable' => [
                'title' => 'Display legend as a table',
                'type' => 'checkbox',
                'default' => 1
            ],
            'days' => [
                'title' => 'Number of days to display data for',
                'default' => '30',
                'type' => 'string',
                'validationPattern' => '^[0-9]+$'
            ],
            'number' => [
                'title' => 'Number of sources to display',
                'default' => '10',
                'type' => 'string',
                'validationPattern' => '^[0-9]+$'
            ],
            'displayDescription' => [
                'title' => 'Display the report description',
                'type' => 'checkbox',
                'default' => 1
            ],
            'blacklist' => [
                'title' => 'Hide sources',
                'type' => 'string',
                'description' => 'add sources to hide with ; delimiter',
                'default' => 'traffic2cash.xyz;trafficgenius.xyz'
            ]
        ];
    }

    protected function loadData()
    {
        $days = $this->property('days');
        if (!$days)
            throw new ApplicationException('Invalid days value: ' . $days);

        $obj = Analytics::instance();
        $data = $obj->service->data_ga->get(
                $obj->viewId, $days . 'daysAgo', 'today', 'ga:visits', ['dimensions' => 'ga:source', 'sort' => '-ga:visits']
        );

        $rows = $data->getRows() ? : [];

        $theblacklist = explode(";", $this->property('blacklist'));
        $filtered_rows = array();
        $filtered_visit = 0;
        foreach ($rows as $row)
        {
            if (!in_array($row[0], $theblacklist))
            {
                array_push($filtered_rows, $row);
            }
            else
            {
                $filtered_visit = $filtered_visit + $row[1];
            }
        }

        $this->vars['rows'] = array_slice($filtered_rows, 0, $this->property('number'));
        $this->vars['total'] = $data->getTotalsForAllResults()['ga:visits'] - $filtered_visit;
    }

}

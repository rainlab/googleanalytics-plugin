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
                'title' => 'Widget title',
                'default' => 'Traffic Sources',
                'type' => 'string',
                'validationPattern' => '^.+$',
                'validationMessage' => 'The Widget Title is required.'
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
                'default' => 'traffic2cash.xyz;googlemare.com;',
                'type' => 'string',
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
        $this->vars['total'] = $data->getTotalsForAllResults()['ga:visits'];

        $blacklist = explode(';', $this->property('blacklist'));
        $i = 0;
        foreach ($rows as $source)
        {
            if (sizeof($blacklist) > 0 && in_array($source[0], $blacklist))
            {
                $this->vars['total'] = $this->vars['total'] - $source[1];
                unset($rows[$i]);
            }
            $i++;
        }

        $this->vars['rows'] = array_slice($rows, 0, $this->property('number'));
    }

}

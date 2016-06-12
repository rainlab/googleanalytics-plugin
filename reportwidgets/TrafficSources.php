<?php namespace RainLab\GoogleAnalytics\ReportWidgets;

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
        try {
            $this->loadData();
        }
        catch (Exception $ex) {
            $this->vars['error'] = $ex->getMessage();
        }

        return $this->makePartial('widget');
    }

    public function defineProperties()
    {
        return [
            'title' => [
                'title'             => 'backend::lang.dashboard.widget_title_label',
                'default'           => e(trans('rainlab.googleanalytics::lang.widgets.title_traffic_sources')),
                'type'              => 'string',
                'validationPattern' => '^.+$',
                'validationMessage' => 'backend::lang.dashboard.widget_title_error'
            ],
            'reportSize' => [
                'title'             => 'rainlab.googleanalytics::lang.widgets.traffic_sources_report_size',
                'default'           => '150',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'rainlab.googleanalytics::lang.widgets.traffic_sources_report_size_validation'
            ],
            'center' => [
                'title'             => 'rainlab.googleanalytics::lang.widgets.traffic_sources_center',
                'type'              => 'checkbox'
            ],
            'legendAsTable' => [
                'title'             => 'rainlab.googleanalytics::lang.widgets.legend_as_table',
                'type'              => 'checkbox',
                'default'           => 1
            ],
            'days' => [
                'title'             => 'rainlab.googleanalytics::lang.widgets.days',
                'default'           => '30',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$'
            ],
            'number' => [
                'title'             => 'rainlab.googleanalytics::lang.widgets.traffic_sources_number',
                'default'           => '10',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$'
            ],
            'displayDescription' => [
                'title'             => 'rainlab.googleanalytics::lang.widgets.display_description',
                'type'              => 'checkbox',
                'default'           => 1
            ]
        ];
    }

    protected function loadData()
    {
        $days = $this->property('days');
        if (!$days)
            throw new ApplicationException('Invalid days value: '.$days);

        $obj = Analytics::instance();
        $data = $obj->service->data_ga->get(
            $obj->viewId,
            $days.'daysAgo',
            'today',
            'ga:visits',
            ['dimensions' => 'ga:source', 'sort' => '-ga:visits']
        );

        $rows = $data->getRows() ?: [];

        $this->vars['rows'] = array_slice($rows, 0, $this->property('number'));
        $this->vars['total'] = $data->getTotalsForAllResults()['ga:visits'];
    }
}

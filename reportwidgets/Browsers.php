<?php namespace RainLab\GoogleAnalytics\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use RainLab\GoogleAnalytics\Classes\Analytics;
use ApplicationException;
use Exception;

/**
 * Google Analytics browsers overview widget.
 *
 * @package backend
 * @author Alexey Bobkov, Samuel Georges
 */
class Browsers extends ReportWidgetBase
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
                'default'           => e(trans('rainlab.googleanalytics::lang.widgets.title_browsers')),
                'type'              => 'string',
                'validationPattern' => '^.+$',
                'validationMessage' => 'backend::lang.dashboard.widget_title_error'
            ],
            'reportHeight' => [
                'title'             => 'rainlab.googleanalytics::lang.widgets.browsers_report_height',
                'default'           => '200',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'rainlab.googleanalytics::lang.widgets.browsers_report_height_validation'
            ],
            'legendAsTable' => [
                'title'             => 'rainlab.googleanalytics::lang.widgets.legend_as_table',
                'type'              => 'checkbox',
                'default'           => 1
            ],
            'days' => [
                'title'             => 'rainlab.googleanalytics::lang.widgets.days',
                'default'           => '7',
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
        $data = $obj->service->data_ga->get($obj->viewId, $days.'daysAgo', 'today', 'ga:visits', ['dimensions'=>'ga:browser', 'sort'=>'-ga:visits']);
        $this->vars['rows'] = $data->getRows();
    }
}

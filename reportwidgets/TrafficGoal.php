<?php namespace RainLab\GoogleAnalytics\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use RainLab\GoogleAnalytics\Classes\Analytics;
use ApplicationException;
use Exception;

/**
 * Google Analytics traffic goal widget.
 *
 * @package backend
 * @author Alexey Bobkov, Samuel Georges
 */
class TrafficGoal extends ReportWidgetBase
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
                'default'           => e(trans('rainlab.googleanalytics::lang.widgets.title_traffic_goal')),
                'type'              => 'string',
                'validationPattern' => '^.+$',
                'validationMessage' => 'backend::lang.dashboard.widget_title_error'
            ],
            'days' => [
                'title'             => 'Period',
                'default'           => '7',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$'
            ],
            'goal' => [
                'title'             => 'Traffic goal, visits',
                'description'       => 'Specify the total number of desired visits per the period defined with the Period parameter',
                'default'           => '100',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Please specify the traffic goal as an integer value.'
            ]
        ];
    }

    protected function loadData()
    {
        $days = $this->property('days');
        if (!$days)
            throw new ApplicationException('Invalid days value: '.$days);

        $goal = $this->property('goal');
        if (!$goal)
            throw new ApplicationException('Invalid goal value: '.$goal);

        $obj = Analytics::instance();
        $data = $obj->service->data_ga->get(
            $obj->viewId,
            $days.'daysAgo',
            'today',
            'ga:visits'
        )->getRows();

        $total = $this->vars['total'] = isset($data[0][0]) ? $data[0][0] : 0;
        $this->vars['percentage'] = min(round($total/$goal*100), 100);
    }
}
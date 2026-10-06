<?php namespace RainLab\GoogleAnalytics\ReportWidgets;

use RainLab\GoogleAnalytics\Classes\Analytics;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportRequest;
use ApplicationException;
use Exception;

/**
 * Google Analytics traffic goal widget.
 *
 * @deprecated use an indicator report with the AnalyticsDataSource instead
 * @package backend
 * @author Alexey Bobkov, Samuel Georges
 */
class TrafficGoal extends WidgetBase
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

    /**
     * defineProperties
     */
    public function defineProperties()
    {
        return [
            'title' => [
                'title' => 'backend::lang.dashboard.widget_title_label',
                'default' => 'Traffic Goal',
                'type' => 'string',
                'validationPattern' => '^.+$',
                'validationMessage' => 'backend::lang.dashboard.widget_title_error'
            ],
            'days' => [
                'title' => 'Period',
                'default' => '7',
                'type' => 'string',
                'validationPattern' => '^[0-9]+$'
            ],
            'goal' => [
                'title' => 'Traffic goal, visits',
                'description' => 'Specify the total number of desired visits per the period defined with the Period parameter',
                'default' => '100',
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Please specify the traffic goal as an integer value.'
            ]
        ];
    }

    /**
     * loadData
     */
    protected function loadData()
    {
        $days = $this->property('days');
        if (!$days) {
            throw new ApplicationException('Invalid days value: '.$days);
        }

        $goal = $this->property('goal');
        if (!$goal) {
            throw new ApplicationException('Invalid goal value: '.$goal);
        }

        $this->loadCached(['days', 'goal'], ['total', 'percentage'], function($widget) use ($days, $goal) {
            $obj = Analytics::instance();
            $data = $obj->client->runReport(new RunReportRequest([
                'property' => 'properties/' . $obj->propertyId,
                'date_ranges' => [
                    new DateRange([
                        'start_date' => $days.'daysAgo',
                        'end_date' => 'today',
                    ]),
                ],
                'metrics' => [new Metric(['name' => 'sessions'])]
            ]));

            $total = 0;
            $rows = $data->getRows();
            if (count($rows)) {
                $row = $rows[0];
                $total = $row->getMetricValues()[0]->getValue();
            }

            $widget->vars['total'] = $total;
            $widget->vars['percentage'] = min(round($total/$goal*100), 100);
        });
    }
}

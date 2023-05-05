<?php namespace RainLab\GoogleAnalytics\ReportWidgets;

use RainLab\GoogleAnalytics\Classes\Analytics;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use ApplicationException;
use Exception;

/**
 * Google Analytics traffic goal widget.
 *
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
                'default' => 'rainlab.googleanalytics::lang.widgets.title_traffic_goal',
                'type' => 'string',
                'validationPattern' => '^.+$',
                'validationMessage' => 'backend::lang.dashboard.widget_title_error'
            ],
            'days' => [
                'title' => 'rainlab.googleanalytics::lang.widgets.traffic_goal_days',
                'default' => '7',
                'type' => 'string',
                'validationPattern' => '^[0-9]+$'
            ],
            'goal' => [
                'title' => 'rainlab.googleanalytics::lang.widgets.traffic_goal_goal',
                'description' => 'rainlab.googleanalytics::lang.widgets.traffic_goal_goal_description',
                'default' => '100',
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'rainlab.googleanalytics::lang.widgets.traffic_goal_goal_validation'
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
            $data = $obj->client->runReport([
                'property' => 'properties/' . $obj->propertyId,
                'dateRanges' => [
                    new DateRange([
                        'start_date' => $days.'daysAgo',
                        'end_date' => 'today',
                    ]),
                ],
                'dimensions' => [new Dimension(['name' => 'pagePath'])],
                'metrics' => [new Metric(['name' => 'screenPageViews'])]
            ]);

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

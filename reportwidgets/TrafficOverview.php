<?php namespace RainLab\GoogleAnalytics\ReportWidgets;

use RainLab\GoogleAnalytics\Classes\Analytics;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use ApplicationException;
use Exception;

/**
 * Google Analytics traffic overview widget.
 *
 * @package backend
 * @author Alexey Bobkov, Samuel Georges
 */
class TrafficOverview extends WidgetBase
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
                'default' => 'rainlab.googleanalytics::lang.widgets.title_traffic_overview',
                'type' => 'string',
                'validationPattern' => '^.+$',
                'validationMessage' => 'backend::lang.dashboard.widget_title_error'
            ],
            'days' => [
                'title' => 'rainlab.googleanalytics::lang.widgets.days',
                'default' => '30',
                'type' => 'string',
                'validationPattern' => '^[0-9]+$'
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

        $this->loadCached(['days', 'goal'], ['rows'], function($widget) use ($days) {
            $obj = Analytics::instance();
            $data = $obj->client->runReport([
                'property' => 'properties/' . $obj->propertyId,
                'dateRanges' => [
                    new DateRange([
                        'start_date' => $days.'daysAgo',
                        'end_date' => 'today',
                    ]),
                ],
                'dimensions' => [new Dimension(['name' => 'date'])],
                'metrics' => [new Metric(['name' => 'screenPageViews'])]
            ]);

            $rows = $data->getRows();
            if (!$rows) {
                throw new ApplicationException('No traffic found yet.');
            }

            $points = [];
            foreach ($rows as $row) {
                $date = $row->getDimensionValues()[0]->getValue();
                $views = $row->getMetricValues()[0]->getValue();
                $point = [
                    strtotime($date)*1000,
                    $views
                ];

                $points[] = $point;
            }

            usort($points, function($a, $b) {
                return $a[0] - $b[0];
            });

            $widget->vars['rows'] = str_replace('"', '', substr(substr(json_encode($points), 1), 0, -1));
        });
    }
}

<?php namespace RainLab\GoogleAnalytics\ReportWidgets;

use RainLab\GoogleAnalytics\Classes\Analytics;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportRequest;
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
                'default' => 'Traffic overview',
                'type' => 'string',
                'validationPattern' => '^.+$',
                'validationMessage' => 'backend::lang.dashboard.widget_title_error'
            ],
            'days' => [
                'title' => 'Number of days to display data for',
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
            $data = $obj->client->runReport(new RunReportRequest([
                'property' => 'properties/' . $obj->propertyId,
                'date_ranges' => [
                    new DateRange([
                        'start_date' => $days.'daysAgo',
                        'end_date' => 'today',
                    ]),
                ],
                'dimensions' => [new Dimension(['name' => 'date'])],
                'metrics' => [new Metric(['name' => 'screenPageViews'])]
            ]));

            $rows = $data->getRows();

            // Index returned rows by date
            $pointsByDate = [];
            if ($rows) {
                foreach ($rows as $row) {
                    $date = $row->getDimensionValues()[0]->getValue();
                    $views = $row->getMetricValues()[0]->getValue();
                    $pointsByDate[$date] = (int) $views;
                }
            }

            // Fill in all dates in the range, defaulting to 0
            $points = [];
            $period = new \DatePeriod(
                new \DateTime("-{$days} days"),
                new \DateInterval('P1D'),
                new \DateTime('tomorrow')
            );

            foreach ($period as $date) {
                $key = $date->format('Ymd');
                $points[] = [
                    $date->getTimestamp() * 1000,
                    $pointsByDate[$key] ?? 0
                ];
            }

            $widget->vars['rows'] = str_replace('"', '', substr(substr(json_encode($points), 1), 0, -1));
        });
    }
}

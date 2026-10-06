<?php namespace RainLab\GoogleAnalytics\ReportWidgets;

use RainLab\GoogleAnalytics\Classes\Analytics;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportRequest;
use ApplicationException;
use Exception;

/**
 * Google Analytics traffic sources widget.
 *
 * @package backend
 * @author Alexey Bobkov, Samuel Georges
 */
class TrafficSources extends WidgetBase
{
    /**
     * render the widget
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
                'default' => 'Traffic Sources',
                'type' => 'string',
                'validationPattern' => '^.+$',
                'validationMessage' => 'backend::lang.dashboard.widget_title_error'
            ],
            'reportSize' => [
                'title' => 'Chart radius',
                'default' => '150',
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Please specify the chart size as an integer value'
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

        $number = $this->property('number');
        if (!$number) {
            throw new ApplicationException('Invalid traffic sources number value: '.$days);
        }

        $this->loadCached(['days', 'number'], ['rows', 'total'], function($widget) use ($days, $number) {
            $obj = Analytics::instance();
            $data = $obj->client->runReport(new RunReportRequest([
                'property' => 'properties/' . $obj->propertyId,
                'date_ranges' => [
                    new DateRange([
                        'start_date' => $days.'daysAgo',
                        'end_date' => 'today',
                    ]),
                ],
                'dimensions' => [new Dimension(['name' => 'firstUserSource'])],
                'metrics' => [new Metric(['name' => 'totalUsers'])]
            ]));

            $rows = [];
            $total = 0;

            foreach ($data->getRows() as $row) {
                $value = $row->getMetricValues()[0]->getValue();

                $rows[] = [
                    $row->getDimensionValues()[0]->getValue(),
                    $value
                ];

                $total += $value;
            }

            $widget->vars['rows'] = array_slice($rows, 0, $number);
            $widget->vars['total'] = $total;
        });
    }
}

<?php namespace RainLab\GoogleAnalytics\ReportWidgets;

use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportRequest;
use RainLab\GoogleAnalytics\Classes\Analytics;
use ApplicationException;
use Exception;

/**
 * Google Analytics browsers overview widget.
 *
 * @package backend
 * @author Alexey Bobkov, Samuel Georges
 */
class Browsers extends WidgetBase
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
                'default' => 'Browsers',
                'type' => 'string',
                'validationPattern' => '^.+$',
                'validationMessage' => 'backend::lang.dashboard.widget_title_error'
            ],
            'reportHeight' => [
                'title' => 'Chart height',
                'default' => '200',
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Please specify the chart height as an integer value.'
            ],
            'legendAsTable' => [
                'title' => 'Display legend as a table',
                'type' => 'checkbox',
                'default' => 1
            ],
            'days' => [
                'title' => 'Number of days to display data for',
                'default' => '7',
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

        $this->loadCached(['days'], ['rows'], function($widget) use ($days) {
            $obj = Analytics::instance();

            $data = $obj->client->runReport(new RunReportRequest([
                'property' => 'properties/' . $obj->propertyId,
                'date_ranges' => [
                    new DateRange([
                        'start_date' => $days.'daysAgo',
                        'end_date' => 'today',
                    ]),
                ],
                'dimensions' => [new Dimension(['name' => 'browser'])],
                'metrics' => [new Metric(['name' => 'totalUsers'])]
            ]));

            $rows = [];

            foreach ($data->getRows() as $row) {
                $rows[] = [
                    $row->getDimensionValues()[0]->getValue(),
                    $row->getMetricValues()[0]->getValue()
                ];
            }

            $widget->vars['rows'] = $rows;
        });
    }
}

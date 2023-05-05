<?php namespace RainLab\GoogleAnalytics\ReportWidgets;

use RainLab\GoogleAnalytics\Classes\Analytics;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
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
                'default' => 'rainlab.googleanalytics::lang.widgets.title_traffic_sources',
                'type' => 'string',
                'validationPattern' => '^.+$',
                'validationMessage' => 'backend::lang.dashboard.widget_title_error'
            ],
            'reportSize' => [
                'title' => 'rainlab.googleanalytics::lang.widgets.traffic_sources_report_size',
                'default' => '150',
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'rainlab.googleanalytics::lang.widgets.traffic_sources_report_size_validation'
            ],
            'center' => [
                'title' => 'rainlab.googleanalytics::lang.widgets.traffic_sources_center',
                'type' => 'checkbox'
            ],
            'legendAsTable' => [
                'title' => 'rainlab.googleanalytics::lang.widgets.legend_as_table',
                'type' => 'checkbox',
                'default' => 1
            ],
            'days' => [
                'title' => 'rainlab.googleanalytics::lang.widgets.days',
                'default' => '30',
                'type' => 'string',
                'validationPattern' => '^[0-9]+$'
            ],
            'number' => [
                'title' => 'rainlab.googleanalytics::lang.widgets.traffic_sources_number',
                'default' => '10',
                'type' => 'string',
                'validationPattern' => '^[0-9]+$'
            ],
            'displayDescription' => [
                'title' => 'rainlab.googleanalytics::lang.widgets.display_description',
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
            $data = $obj->client->runReport([
                'property' => 'properties/' . $obj->propertyId,
                'dateRanges' => [
                    new DateRange([
                        'start_date' => $days.'daysAgo',
                        'end_date' => 'today',
                    ]),
                ],
                'dimensions' => [new Dimension(['name' => 'firstUserSource'])],
                'metrics' => [new Metric(['name' => 'totalUsers'])]
            ]);

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

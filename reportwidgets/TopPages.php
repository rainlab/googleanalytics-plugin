<?php namespace RainLab\GoogleAnalytics\ReportWidgets;

use RainLab\GoogleAnalytics\Classes\Analytics;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use ApplicationException;
use Exception;

/**
 * Google Analytics top pages widget.
 *
 * @package backend
 * @author Alexey Bobkov, Samuel Georges
 */
class TopPages extends WidgetBase
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
                'default' => 'rainlab.googleanalytics::lang.widgets.title_toppages',
                'type' => 'string',
                'validationPattern' => '^.+$',
                'validationMessage' => 'backend::lang.dashboard.widget_title_error'
            ],
            'days' => [
                'title' => 'rainlab.googleanalytics::lang.widgets.days',
                'default' => '7',
                'type' => 'string',
                'validationPattern' => '^[0-9]+$'
            ],
            'number' => [
                'title' => 'rainlab.googleanalytics::lang.widgets.toppages_number',
                'default' => '5',
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

        $numRows = $this->property('number');
        $this->loadCached(['number'], ['rows', 'total'], function($widget) use ($numRows, $days) {
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

            $widget->vars['rows'] = array_slice($rows, 0, $numRows);
            $widget->vars['total'] = $total;
        });
    }
}

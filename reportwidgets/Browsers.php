<?php namespace RainLab\GoogleAnalytics\ReportWidgets;

use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
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
                'default' => 'rainlab.googleanalytics::lang.widgets.title_browsers',
                'type' => 'string',
                'validationPattern' => '^.+$',
                'validationMessage' => 'backend::lang.dashboard.widget_title_error'
            ],
            'reportHeight' => [
                'title' => 'rainlab.googleanalytics::lang.widgets.browsers_report_height',
                'default' => '200',
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'rainlab.googleanalytics::lang.widgets.browsers_report_height_validation'
            ],
            'legendAsTable' => [
                'title' => 'rainlab.googleanalytics::lang.widgets.legend_as_table',
                'type' => 'checkbox',
                'default' => 1
            ],
            'days' => [
                'title' => 'rainlab.googleanalytics::lang.widgets.days',
                'default' => '7',
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

        $this->loadCached(['days'], ['rows'], function($widget) use ($days) {
            $obj = Analytics::instance();

            $data = $obj->client->runReport([
                'property' => 'properties/' . $obj->propertyId,
                'dateRanges' => [
                    new DateRange([
                        'start_date' => $days.'daysAgo',
                        'end_date' => 'today',
                    ]),
                ],
                'dimensions' => [new Dimension(['name' => 'browser'])],
                'metrics' => [new Metric(['name' => 'totalUsers'])]
            ]);

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

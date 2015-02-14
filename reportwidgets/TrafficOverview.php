<?php namespace RainLab\GoogleAnalytics\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use RainLab\GoogleAnalytics\Classes\Analytics;
use ApplicationException;
use Exception;

/**
 * Google Analytics traffic overview widget.
 *
 * @package backend
 * @author Alexey Bobkov, Samuel Georges
 */
class TrafficOverview extends ReportWidgetBase
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
                'title'             => 'Widget title',
                'default'           => 'Traffic overview',
                'type'              => 'string',
                'validationPattern' => '^.+$',
                'validationMessage' => 'The Widget Title is required.'
            ],
            'days' => [
                'title'             => 'Number of days to display data for',
                'default'           => '30',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$'
            ]
        ];
    }

    protected function loadData()
    {
        $obj = Analytics::instance();

        $days = $this->property('days');
        if (!$days)
            throw new ApplicationException('Invalid days value: '.$days);

        $data = $obj->service->data_ga->get(
            $obj->viewId,
            $days.'daysAgo',
            'today',
            'ga:visits',
            ['dimensions' => 'ga:date']
        );
        $points = [];
        foreach ($data->getRows() as $row) {
            $point = [
                strtotime($row[0])*1000,
                $row[1]
            ];
            
            $points[] = $point;
        }

        $this->vars['rows'] = str_replace('"', '', substr(substr(json_encode($points), 1), 0, -1));
    }

}
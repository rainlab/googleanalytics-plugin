<?php namespace RainLab\GoogleAnalytics\ReportWidgets;

use RainLab\GoogleAnalytics\Classes\Analytics;
use Dashboard\Classes\ReportWidgetBase;
use Exception;
use Cache;

/**
 * WidgetBase
 *
 * @deprecated use the AnalyticsDataSource with the built-in dashboard widgets instead
 */
class WidgetBase extends ReportWidgetBase
{
    /**
     * loadCached data
     */
    protected function loadCached(array $keyProperties, array $pageVars, callable $callback)
    {
        $obj = Analytics::instance();

        $cacheKey = get_class($this) . $obj->propertyId;
        foreach ($keyProperties as $keyProperty) {
            $cacheKey .= $this->property($keyProperty);
        }

        if ($cached = Cache::get($cacheKey)) {
            try {
                $data = json_decode($cached, true);

                foreach ($pageVars as $pageVar) {
                    $this->vars[$pageVar] = $data[$pageVar];
                }
                return;
            }
            catch (Exception $ex) {
                // Do nothing
            }
        }

        $callback($this);

        $cacheData = [];
        foreach ($pageVars as $pageVar) {
            $cacheData[$pageVar] = $this->vars[$pageVar];
        }

        Cache::put($cacheKey, json_encode($cacheData), 60);
    }
}

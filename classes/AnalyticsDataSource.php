<?php namespace RainLab\GoogleAnalytics\Classes;

use Cache;
use Carbon\Carbon;
use Dashboard\Classes\ReportMetric;
use Dashboard\Classes\ReportDimension;
use Dashboard\Classes\ReportFetchData;
use Dashboard\Classes\ReportDataOrderRule;
use Dashboard\Classes\ReportDataSourceBase;
use Dashboard\Classes\ReportDimensionFilter;
use Dashboard\Classes\ReportFetchDataResult;
use Google\Analytics\Data\V1beta\Filter;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\FilterExpression;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Analytics\Data\V1beta\MetricAggregation;
use Google\Analytics\Data\V1beta\RunReportResponse;
use Google\Analytics\Data\V1beta\FilterExpressionList;
use Google\Analytics\Data\V1beta\Filter\EmptyFilter;
use Google\Analytics\Data\V1beta\Filter\InListFilter;
use Google\Analytics\Data\V1beta\Filter\StringFilter;
use Google\Analytics\Data\V1beta\Filter\StringFilter\MatchType;
use Google\Analytics\Data\V1beta\OrderBy\MetricOrderBy;
use Google\Analytics\Data\V1beta\OrderBy\DimensionOrderBy;
use Google\Analytics\Data\V1beta\OrderBy\DimensionOrderBy\OrderType;
use Google\ApiCore\ApiException;
use ApplicationException;

/**
 * AnalyticsDataSource provides Google Analytics reports to the dashboard widgets.
 *
 * @package rainlab/googleanalytics
 * @author Alexey Bobkov, Samuel Georges
 */
class AnalyticsDataSource extends ReportDataSourceBase
{
    const DIMENSION_PAGE_PATH = 'page_path';
    const DIMENSION_SOURCE = 'source';
    const DIMENSION_CHANNEL = 'channel';
    const DIMENSION_BROWSER = 'browser';
    const DIMENSION_DEVICE = 'device';
    const DIMENSION_COUNTRY = 'country';

    const METRIC_PAGEVIEWS = 'pageviews';
    const METRIC_SESSIONS = 'sessions';
    const METRIC_USERS = 'users';
    const METRIC_NEW_USERS = 'new_users';
    const METRIC_BOUNCE_RATE = 'bounce_rate';
    const METRIC_SESSION_DURATION = 'session_duration';

    /**
     * @var string NOT_SET_VALUE is the value Google Analytics reports for a dimension it could not set, besides an empty value.
     */
    const NOT_SET_VALUE = '(not set)';

    /**
     * @var int cacheSeconds to keep a report before asking Google Analytics again.
     */
    protected $cacheSeconds = 600;

    /**
     * __construct registers the dimensions and metrics, named after their Google Analytics fields.
     */
    public function __construct()
    {
        $this->registerDimension(new ReportDimension(ReportDimension::CODE_DATE, 'date', "Date"));
        $this->registerDimension(new ReportDimension(self::DIMENSION_PAGE_PATH, 'pagePath', "Page Path"));
        $this->registerDimension(new ReportDimension(self::DIMENSION_SOURCE, 'sessionSource', "Source"));
        $this->registerDimension(new ReportDimension(self::DIMENSION_CHANNEL, 'sessionDefaultChannelGroup', "Channel"));
        $this->registerDimension(new ReportDimension(self::DIMENSION_BROWSER, 'browser', "Browser"));
        $this->registerDimension(new ReportDimension(self::DIMENSION_DEVICE, 'deviceCategory', "Device"));
        $this->registerDimension(new ReportDimension(self::DIMENSION_COUNTRY, 'country', "Country"));

        $this->registerMetric(new ReportMetric(self::METRIC_PAGEVIEWS, 'screenPageViews', "Pageviews", ReportMetric::AGGREGATE_SUM));
        $this->registerMetric(new ReportMetric(self::METRIC_SESSIONS, 'sessions', "Sessions", ReportMetric::AGGREGATE_SUM));
        $this->registerMetric(new ReportMetric(self::METRIC_USERS, 'totalUsers', "Users", ReportMetric::AGGREGATE_COUNT_DISTINCT));
        $this->registerMetric(new ReportMetric(self::METRIC_NEW_USERS, 'newUsers', "New Users", ReportMetric::AGGREGATE_SUM));

        $this->registerMetric(new ReportMetric(
            self::METRIC_BOUNCE_RATE,
            'bounceRate',
            "Bounce Rate",
            ReportMetric::AGGREGATE_AVG,
            ['style' => 'percent', 'maximumFractionDigits' => 1]
        ));

        $this->registerMetric((new ReportMetric(
            self::METRIC_SESSION_DURATION,
            'averageSessionDuration',
            "Avg. Session Duration",
            ReportMetric::AGGREGATE_AVG
        ))->setDisplayFormatter(fn($seconds) => $this->formatDuration((float) $seconds)));
    }

    /**
     * fetchData runs the report on Google Analytics, which also groups dates and computes totals so non-additive metrics stay correct.
     */
    protected function fetchData(ReportFetchData $data): ReportFetchDataResult
    {
        if (!$data->dateStart || !$data->dateEnd) {
            throw new ApplicationException(__("Google Analytics does not support the last hour interval."));
        }

        if ($data->dimension->isDate() && $data->groupInterval === self::GROUP_INTERVAL_QUARTER) {
            return $this->fetchQuarterData($data);
        }

        $report = $this->fetchReport($this->makeReportRequest($data), $data->resetCache);

        $result = new ReportFetchDataResult($data->totalsOnly ? [] : $this->makeRows($data, $report['rows']));
        $result->setMetricTotals($this->makeMetricTotals($data, $report['totals']));

        if ($data->paginationParams) {
            $result->setTotalRecords($report['rowCount']);
        }

        if ($data->dimension->isDate()) {
            $result->setPreAggregated();
        }

        return $result;
    }

    /**
     * fetchQuarterData runs one report per quarter, since Google Analytics has no quarter dimension.
     */
    protected function fetchQuarterData(ReportFetchData $data): ReportFetchDataResult
    {
        $rows = [];
        $quarter = $data->dateStart->copy()->startOfQuarter();

        while ($quarter->lte($data->dateEnd)) {
            $quarterData = clone $data;
            $quarterData->dateStart = $quarter->max($data->dateStart);
            $quarterData->dateEnd = $quarter->copy()->endOfQuarter()->min($data->dateEnd);
            $quarterData->groupInterval = self::GROUP_INTERVAL_FULL;

            $report = $this->fetchReport($this->makeReportRequest($quarterData), $data->resetCache);
            if ($report['rows']) {
                $rows[] = $this->makeRow($data, $quarter->toDateString(), $report['rows'][0]['metrics']);
            }

            $quarter = $quarter->copy()->addQuarter();
        }

        $result = new ReportFetchDataResult($data->totalsOnly ? [] : $rows);

        if ($this->listTotalsMetricCodes($data)) {
            $periodData = clone $data;
            $periodData->groupInterval = self::GROUP_INTERVAL_FULL;
            $report = $this->fetchReport($this->makeReportRequest($periodData), $data->resetCache);

            $result->setMetricTotals($this->makeMetricTotals($data, $report['totals']));
        }

        return $result->setPreAggregated();
    }

    /**
     * makeReportRequest translates the dashboard request to a Google Analytics report request.
     */
    protected function makeReportRequest(ReportFetchData $data): RunReportRequest
    {
        $request = new RunReportRequest([
            'date_ranges' => [new DateRange([
                'start_date' => $data->dateStart->toDateString(),
                'end_date' => $data->dateEnd->toDateString()
            ])],
            'metrics' => array_map(fn($metric) => new Metric(['name' => $metric->getDatabaseColumnName()]), $data->metrics)
        ]);

        $dimensionName = $this->getReportDimensionName($data);
        if ($dimensionName) {
            $request->setDimensions([new Dimension(['name' => $dimensionName])]);
            $request->setOrderBys($this->makeOrderBys($data, $dimensionName));

            if ($filter = $this->makeDimensionFilter($data, $dimensionName)) {
                $request->setDimensionFilter($filter);
            }

            if ($data->paginationParams) {
                $request->setLimit($data->paginationParams->getRecordsPerPage());
                $request->setOffset($data->paginationParams->getOffset());
            }
            elseif ($data->limit) {
                $request->setLimit($data->limit);
            }
        }

        if ($this->listTotalsMetricCodes($data)) {
            $request->setMetricAggregations([MetricAggregation::TOTAL]);
        }

        return $request;
    }

    /**
     * getReportDimensionName returns the Google Analytics dimension for the request, or null to report the whole period.
     */
    protected function getReportDimensionName(ReportFetchData $data): ?string
    {
        if (!$data->dimension->isDate()) {
            return $data->dimension->getDatabaseColumnName();
        }

        return [
            self::GROUP_INTERVAL_DAY => 'date',
            self::GROUP_INTERVAL_WEEK => 'isoYearIsoWeek',
            self::GROUP_INTERVAL_MONTH => 'yearMonth',
            self::GROUP_INTERVAL_YEAR => 'year'
        ][$data->groupInterval] ?? null;
    }

    /**
     * makeOrderBys translates the order rule, where a metric must be one of the requested metrics and date values sort as numbers.
     */
    protected function makeOrderBys(ReportFetchData $data, string $dimensionName): array
    {
        $rule = $data->orderRule;
        if (!$rule) {
            return [];
        }

        if ($rule->getDataAttributeType() === ReportDataOrderRule::ATTR_TYPE_METRIC) {
            $metric = ReportMetric::findMetricByCodeStrict($data->metrics, $rule->getAttributeName());

            return [new OrderBy([
                'metric' => new MetricOrderBy(['metric_name' => $metric->getDatabaseColumnName()]),
                'desc' => !$rule->isAscending()
            ])];
        }

        if ($rule->getDataAttributeType() === ReportDataOrderRule::ATTR_TYPE_DIMENSION) {
            return [new OrderBy([
                'dimension' => new DimensionOrderBy([
                    'dimension_name' => $dimensionName,
                    'order_type' => $data->dimension->isDate()
                        ? OrderType::NUMERIC
                        : OrderType::CASE_INSENSITIVE_ALPHANUMERIC
                ]),
                'desc' => !$rule->isAscending()
            ])];
        }

        return [];
    }

    /**
     * makeDimensionFilter combines the widget filters and the empty value setting into one filter expression.
     */
    protected function makeDimensionFilter(ReportFetchData $data, string $dimensionName): ?FilterExpression
    {
        if ($data->dimension->isDate()) {
            return null;
        }

        $expressions = [];
        foreach ((array) $data->dimensionFilters as $filter) {
            $expressions[] = new FilterExpression(['filter' => $this->makeFilter($dimensionName, $filter)]);
        }

        if ($data->hideEmptyDimensionValues) {
            $expressions[] = new FilterExpression(['not_expression' => new FilterExpression([
                'or_group' => new FilterExpressionList(['expressions' => [
                    new FilterExpression(['filter' => $this->makeStringFilter($dimensionName, self::NOT_SET_VALUE, MatchType::EXACT)]),
                    new FilterExpression(['filter' => new Filter(['field_name' => $dimensionName, 'empty_filter' => new EmptyFilter])])
                ]])
            ])]);
        }

        if (count($expressions) < 2) {
            return $expressions[0] ?? null;
        }

        return new FilterExpression(['and_group' => new FilterExpressionList(['expressions' => $expressions])]);
    }

    /**
     * makeFilter translates a widget filter, where Google Analytics compares dimension values as text.
     */
    protected function makeFilter(string $dimensionName, ReportDimensionFilter $filter): Filter
    {
        $value = $filter->getValue();

        switch ($filter->getOperation()) {
            case ReportDimensionFilter::OPERATION_EQUALS:
                return $this->makeStringFilter($dimensionName, (string) $value, MatchType::EXACT);
            case ReportDimensionFilter::OPERATION_STARTS_WITH:
                return $this->makeStringFilter($dimensionName, (string) $value, MatchType::BEGINS_WITH);
            case ReportDimensionFilter::OPERATION_STRING_INCLUDES:
                return $this->makeStringFilter($dimensionName, (string) $value, MatchType::CONTAINS);
            case ReportDimensionFilter::OPERATION_ONE_OF:
                return new Filter([
                    'field_name' => $dimensionName,
                    'in_list_filter' => new InListFilter(['values' => array_values(array_map('strval', (array) $value))])
                ]);
            default:
                throw new ApplicationException(__("Google Analytics cannot filter by the :operation operation.", ['operation' => $filter->getOperation()]));
        }
    }

    /**
     * makeStringFilter returns a case insensitive text filter on a dimension.
     */
    protected function makeStringFilter(string $dimensionName, string $value, int $matchType): Filter
    {
        return new Filter([
            'field_name' => $dimensionName,
            'string_filter' => new StringFilter(['value' => $value, 'match_type' => $matchType])
        ]);
    }

    /**
     * listTotalsMetricCodes returns the metrics shown with totals or relative bars, which need totals.
     */
    protected function listTotalsMetricCodes(ReportFetchData $data): array
    {
        $result = [];
        foreach ((array) $data->metricsConfiguration as $metricCode => $configuration) {
            if ($configuration && ($configuration->getDisplayTotals() || $configuration->getDisplayRelativeBar())) {
                $result[] = $metricCode;
            }
        }

        return $result;
    }

    /**
     * makeRows converts the report rows to dashboard rows, skipping date values that are not dates such as "(other)".
     */
    protected function makeRows(ReportFetchData $data, array $reportRows): array
    {
        $rows = [];
        foreach ($reportRows as $reportRow) {
            $dimensionValue = $this->makeDimensionValue($data, $reportRow['dimensions'][0] ?? null);
            if ($dimensionValue !== null) {
                $rows[] = $this->makeRow($data, $dimensionValue, $reportRow['metrics']);
            }
        }

        return $rows;
    }

    /**
     * makeRow returns a dashboard row from a dimension value and metric values in request order.
     */
    protected function makeRow(ReportFetchData $data, string $dimensionValue, array $metricValues): object
    {
        $row = ['oc_dimension' => $dimensionValue];
        foreach ($data->metrics as $index => $metric) {
            $row[$metric->getDataSetColumnName()] = $this->makeMetricValue($metricValues[$index] ?? null);
        }

        return (object) $row;
    }

    /**
     * makeDimensionValue converts Google Analytics date values to the first date of their group, as dashboard rows expect.
     */
    protected function makeDimensionValue(ReportFetchData $data, ?string $value): ?string
    {
        if (!$data->dimension->isDate()) {
            return $value;
        }

        if ($value === null) {
            return $data->dateStart->toDateString();
        }

        if (!ctype_digit($value)) {
            return null;
        }

        $year = (int) substr($value, 0, 4);
        $part = (int) substr($value, 4, 2);

        switch ($data->groupInterval) {
            case self::GROUP_INTERVAL_WEEK:
                return Carbon::create($year)->setISODate($year, $part)->toDateString();
            case self::GROUP_INTERVAL_MONTH:
                return Carbon::create($year, $part)->toDateString();
            case self::GROUP_INTERVAL_YEAR:
                return Carbon::create($year)->toDateString();
            default:
                return Carbon::create($year, $part, (int) substr($value, 6, 2))->toDateString();
        }
    }

    /**
     * makeMetricTotals returns the totals computed by Google Analytics for the metrics that need them.
     */
    protected function makeMetricTotals(ReportFetchData $data, array $totals): array
    {
        $totalsMetricCodes = $this->listTotalsMetricCodes($data);

        $result = [];
        foreach ($data->metrics as $index => $metric) {
            if (in_array($metric->getCode(), $totalsMetricCodes, true)) {
                $result[$metric->getCode()] = $this->makeMetricValue($totals[$index] ?? null);
            }
        }

        return $result;
    }

    /**
     * makeMetricValue converts a metric value, which Google Analytics returns as text, to a number.
     */
    protected function makeMetricValue(?string $value): int|float|null
    {
        return is_numeric($value) ? $value + 0 : null;
    }

    /**
     * formatDuration displays seconds as minutes and seconds.
     */
    protected function formatDuration(float $seconds): string
    {
        $seconds = (int) round($seconds);

        return $seconds < 60
            ? $seconds.'s'
            : intdiv($seconds, 60).'m '.($seconds % 60).'s';
    }

    /**
     * fetchReport returns the report as an array, cached per request until the dashboard asks for fresh data.
     */
    protected function fetchReport(RunReportRequest $request, bool $resetCache = false): array
    {
        $request->setProperty($this->getPropertyName());

        $cacheKey = 'rainlab.googleanalytics.report.'.md5($request->serializeToJsonString());
        if ($resetCache) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->cacheSeconds, function() use ($request) {
            try {
                return $this->makeReportArray($this->runReport($request));
            }
            catch (ApiException $ex) {
                throw new ApplicationException($ex->getBasicMessage() ?: $ex->getMessage());
            }
        });
    }

    /**
     * makeReportArray converts a report response to plain values that can be cached.
     */
    protected function makeReportArray(RunReportResponse $response): array
    {
        $values = fn($items) => array_map(fn($item) => $item->getValue(), iterator_to_array($items));

        $rows = [];
        foreach ($response->getRows() as $row) {
            $rows[] = [
                'dimensions' => $values($row->getDimensionValues()),
                'metrics' => $values($row->getMetricValues())
            ];
        }

        $totals = [];
        foreach ($response->getTotals() as $row) {
            $totals = $values($row->getMetricValues());
            break;
        }

        return [
            'rows' => $rows,
            'totals' => $totals,
            'rowCount' => $response->getRowCount()
        ];
    }

    /**
     * getPropertyName returns the configured Google Analytics property.
     */
    protected function getPropertyName(): string
    {
        return 'properties/'.Analytics::instance()->propertyId;
    }

    /**
     * runReport sends the report request to Google Analytics.
     */
    protected function runReport(RunReportRequest $request): RunReportResponse
    {
        return Analytics::instance()->client->runReport($request);
    }
}

<?php

use Carbon\Carbon;
use Dashboard\Classes\ReportMetric;
use Dashboard\Classes\ReportFetchData;
use Dashboard\Classes\ReportDataOrderRule;
use Dashboard\Classes\ReportDataSourceBase;
use Dashboard\Classes\ReportDimensionFilter;
use Dashboard\Classes\ReportMetricConfiguration;
use Dashboard\Classes\ReportDataPaginationParams;
use Google\Analytics\Data\V1beta\Row;
use Google\Analytics\Data\V1beta\MetricValue;
use Google\Analytics\Data\V1beta\DimensionValue;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Analytics\Data\V1beta\MetricAggregation;
use Google\Analytics\Data\V1beta\RunReportResponse;
use Google\Analytics\Data\V1beta\Filter\StringFilter\MatchType;
use Google\Analytics\Data\V1beta\OrderBy\DimensionOrderBy\OrderType;
use RainLab\GoogleAnalytics\Classes\AnalyticsDataSource;
use Google\ApiCore\ApiException;

/**
 * AnalyticsDataSourceTest covers how dashboard requests become Google Analytics reports and back.
 */
class AnalyticsDataSourceTest extends PluginTestCase
{
    /**
     * testDailyReportFillsDashboardDates ensures daily rows are keyed by date and missing days are filled.
     */
    public function testDailyReportFillsDashboardDates()
    {
        $source = new AnalyticsDataSourceFixture;
        $source->responses[] = $this->makeResponse([
            [['20260902'], [12, 7]],
            [['20260901'], [5, 3]]
        ]);

        $rows = $source->getData($this->makeFetchData('date', ['pageviews', 'users'], [
            'dateStart' => Carbon::parse('2026-09-01'),
            'dateEnd' => Carbon::parse('2026-09-03')
        ]))->getRows();

        $request = $source->requests[0];
        $this->assertEquals('properties/123', $request->getProperty());
        $this->assertEquals(['date'], $this->listNames($request->getDimensions()));
        $this->assertEquals(['screenPageViews', 'totalUsers'], $this->listNames($request->getMetrics()));
        $this->assertEquals('2026-09-01', $request->getDateRanges()[0]->getStartDate());
        $this->assertEquals('2026-09-03', $request->getDateRanges()[0]->getEndDate());
        $this->assertEquals(OrderType::NUMERIC, $request->getOrderBys()[0]->getDimension()->getOrderType());

        $this->assertEquals(['2026-09-01', '2026-09-02', '2026-09-03'], array_column($rows, 'oc_dimension'));
        $this->assertSame([5, 12, null], array_column($rows, 'oc_metric_pageviews'));
        $this->assertSame([3, 7, null], array_column($rows, 'oc_metric_users'));
    }

    /**
     * testWeeklyReportKeepsUserCountsFromGoogleAnalytics ensures weeks are grouped by Google Analytics, since users cannot be added up by day.
     */
    public function testWeeklyReportKeepsUserCountsFromGoogleAnalytics()
    {
        $source = new AnalyticsDataSourceFixture;
        $source->responses[] = $this->makeResponse([
            [['202636'], [60]],
            [['202637'], [45]]
        ]);

        $rows = $source->getData($this->makeFetchData('date', ['users'], [
            'dateStart' => Carbon::parse('2026-08-31'),
            'dateEnd' => Carbon::parse('2026-09-13'),
            'groupInterval' => ReportDataSourceBase::GROUP_INTERVAL_WEEK
        ]))->getRows();

        $this->assertEquals(['isoYearIsoWeek'], $this->listNames($source->requests[0]->getDimensions()));
        $this->assertEquals(['2026-08-31', '2026-09-07'], array_column($rows, 'oc_dimension'));
        $this->assertSame([60, 45], array_column($rows, 'oc_metric_users'));
    }

    /**
     * testMonthAndYearValuesUseFirstDates ensures month and year values are keyed by the first date of the group.
     */
    public function testMonthAndYearValuesUseFirstDates()
    {
        $source = new AnalyticsDataSourceFixture;
        $source->responses[] = $this->makeResponse([[['202609'], [100]]]);
        $source->responses[] = $this->makeResponse([[['2026'], [900]]]);

        $monthRows = $source->getData($this->makeFetchData('date', ['sessions'], [
            'dateStart' => Carbon::parse('2026-09-01'),
            'dateEnd' => Carbon::parse('2026-09-30'),
            'groupInterval' => ReportDataSourceBase::GROUP_INTERVAL_MONTH
        ]))->getRows();

        $yearRows = $source->getData($this->makeFetchData('date', ['sessions'], [
            'dateStart' => Carbon::parse('2026-01-01'),
            'dateEnd' => Carbon::parse('2026-12-31'),
            'groupInterval' => ReportDataSourceBase::GROUP_INTERVAL_YEAR
        ]))->getRows();

        $this->assertEquals(['yearMonth'], $this->listNames($source->requests[0]->getDimensions()));
        $this->assertEquals(['year'], $this->listNames($source->requests[1]->getDimensions()));
        $this->assertEquals('2026-09-01', $monthRows[0]->oc_dimension);
        $this->assertEquals(100, $monthRows[0]->oc_metric_sessions);
        $this->assertEquals('2026-01-01', $yearRows[0]->oc_dimension);
        $this->assertEquals(900, $yearRows[0]->oc_metric_sessions);
    }

    /**
     * testQuarterReportRunsOneRequestPerQuarter ensures each quarter and the period total are reported by Google Analytics.
     */
    public function testQuarterReportRunsOneRequestPerQuarter()
    {
        $source = new AnalyticsDataSourceFixture;
        $source->responses[] = $this->makeResponse([[[], [10]]]);
        $source->responses[] = $this->makeResponse([[[], [20]]]);
        $source->responses[] = $this->makeResponse([]);
        $source->responses[] = $this->makeResponse([[[], [30]]], [30]);

        $result = $source->getData($this->makeFetchData('date', ['sessions'], [
            'dateStart' => Carbon::parse('2026-02-15'),
            'dateEnd' => Carbon::parse('2026-08-10'),
            'groupInterval' => ReportDataSourceBase::GROUP_INTERVAL_QUARTER,
            'metricsConfiguration' => ['sessions' => new ReportMetricConfiguration(true, false)]
        ]));

        $ranges = array_map(function($request) {
            $this->assertCount(0, $request->getDimensions());
            return $request->getDateRanges()[0]->getStartDate().' '.$request->getDateRanges()[0]->getEndDate();
        }, $source->requests);

        $this->assertEquals([
            '2026-02-15 2026-03-31',
            '2026-04-01 2026-06-30',
            '2026-07-01 2026-08-10',
            '2026-02-15 2026-08-10'
        ], $ranges);

        $rows = $result->getRows();
        $this->assertEquals(['2026-01-01', '2026-04-01', '2026-07-01'], array_column($rows, 'oc_dimension'));
        $this->assertSame([10, 20, null], array_column($rows, 'oc_metric_sessions'));
        $this->assertSame(['sessions' => 30], $result->getMetricTotals());
    }

    /**
     * testTableReportUsesOrderLimitAndTotals ensures sorting, limits and totals are left to Google Analytics.
     */
    public function testTableReportUsesOrderLimitAndTotals()
    {
        $source = new AnalyticsDataSourceFixture;
        $source->responses[] = $this->makeResponse([
            [['/'], [50]],
            [['/about'], [20]]
        ], [70]);

        $result = $source->getData($this->makeFetchData('page_path', ['pageviews'], [
            'orderRule' => new ReportDataOrderRule(ReportDataOrderRule::ATTR_TYPE_METRIC, 'pageviews', false),
            'limit' => 10,
            'metricsConfiguration' => ['pageviews' => new ReportMetricConfiguration(false, true)]
        ]));

        $request = $source->requests[0];
        $this->assertEquals(['pagePath'], $this->listNames($request->getDimensions()));
        $this->assertEquals('screenPageViews', $request->getOrderBys()[0]->getMetric()->getMetricName());
        $this->assertTrue($request->getOrderBys()[0]->getDesc());
        $this->assertEquals(10, $request->getLimit());
        $this->assertEquals([MetricAggregation::TOTAL], iterator_to_array($request->getMetricAggregations()));

        $this->assertEquals(['/', '/about'], array_column($result->getRows(), 'oc_dimension'));
        $this->assertSame([50, 20], array_column($result->getRows(), 'oc_metric_pageviews'));
        $this->assertSame(['pageviews' => 70], $result->getMetricTotals());
    }

    /**
     * testPaginationUsesOffsetAndRowCount ensures pages are requested by offset and counted by Google Analytics.
     */
    public function testPaginationUsesOffsetAndRowCount()
    {
        $source = new AnalyticsDataSourceFixture;
        $source->responses[] = $this->makeResponse([[['Chrome'], [5]]], [], 25);

        $result = $source->getData($this->makeFetchData('browser', ['users'], [
            'paginationParams' => new ReportDataPaginationParams(10, 1)
        ]));

        $this->assertEquals(10, $source->requests[0]->getLimit());
        $this->assertEquals(10, $source->requests[0]->getOffset());
        $this->assertEquals(25, $result->getTotalRecords());
        $this->assertEquals(OrderType::CASE_INSENSITIVE_ALPHANUMERIC, $source->requests[0]->getOrderBys()[0]->getDimension()->getOrderType());
    }

    /**
     * testFiltersAndHiddenEmptyValuesAreCombined ensures widget filters and hidden "(not set)" and empty values are sent as one filter.
     */
    public function testFiltersAndHiddenEmptyValuesAreCombined()
    {
        $source = new AnalyticsDataSourceFixture;
        $source->responses[] = $this->makeResponse([]);
        $source->responses[] = $this->makeResponse([]);

        $source->getData($this->makeFetchData('browser', ['users'], [
            'dimensionFilters' => [
                new ReportDimensionFilter(ReportDimensionFilter::ATTR_TYPE_DIMENSION, null, ReportDimensionFilter::OPERATION_ONE_OF, ['Chrome', 'Safari'])
            ],
            'hideEmptyDimensionValues' => true
        ]));

        $source->getData($this->makeFetchData('page_path', ['pageviews'], [
            'dimensionFilters' => [
                new ReportDimensionFilter(ReportDimensionFilter::ATTR_TYPE_DIMENSION, null, ReportDimensionFilter::OPERATION_STARTS_WITH, '/blog')
            ]
        ]));

        $expressions = $source->requests[0]->getDimensionFilter()->getAndGroup()->getExpressions();
        $this->assertCount(2, $expressions);
        $this->assertEquals(['Chrome', 'Safari'], iterator_to_array($expressions[0]->getFilter()->getInListFilter()->getValues()));
        $hidden = $expressions[1]->getNotExpression()->getOrGroup()->getExpressions();
        $this->assertEquals('(not set)', $hidden[0]->getFilter()->getStringFilter()->getValue());
        $this->assertNotNull($hidden[1]->getFilter()->getEmptyFilter());

        $filter = $source->requests[1]->getDimensionFilter()->getFilter();
        $this->assertEquals('pagePath', $filter->getFieldName());
        $this->assertEquals(MatchType::BEGINS_WITH, $filter->getStringFilter()->getMatchType());
        $this->assertEquals('/blog', $filter->getStringFilter()->getValue());
    }

    /**
     * testNumericFilterIsReported ensures a comparison Google Analytics cannot run on text is reported to the user.
     */
    public function testNumericFilterIsReported()
    {
        $source = new AnalyticsDataSourceFixture;

        $this->expectException(ApplicationException::class);

        $source->getData($this->makeFetchData('browser', ['users'], [
            'dimensionFilters' => [
                new ReportDimensionFilter(ReportDimensionFilter::ATTR_TYPE_DIMENSION, null, ReportDimensionFilter::OPERATION_MORE, 5)
            ]
        ]));
    }

    /**
     * testReportsAreCachedUntilReset ensures repeated requests reuse the report until the dashboard asks for fresh data.
     */
    public function testReportsAreCachedUntilReset()
    {
        $source = new AnalyticsDataSourceFixture;
        $source->responses[] = $this->makeResponse([[['Chrome'], [5]]]);
        $source->responses[] = $this->makeResponse([[['Chrome'], [8]]]);

        $first = $source->getData($this->makeFetchData('browser', ['users']));
        $cached = $source->getData($this->makeFetchData('browser', ['users']));
        $reset = $source->getData($this->makeFetchData('browser', ['users'], ['resetCache' => true]));

        $this->assertCount(2, $source->requests);
        $this->assertEquals(5, $first->getRows()[0]->oc_metric_users);
        $this->assertEquals(5, $cached->getRows()[0]->oc_metric_users);
        $this->assertEquals(8, $reset->getRows()[0]->oc_metric_users);
    }

    /**
     * testApiErrorsAreShownAsMessages ensures Google Analytics errors reach the widget as a readable message.
     */
    public function testApiErrorsAreShownAsMessages()
    {
        $source = new AnalyticsDataSourceFixture;
        $source->responses[] = ApiException::createFromApiResponse('User does not have sufficient permissions for this property.', 7);

        $this->expectException(ApplicationException::class);
        $this->expectExceptionMessage('User does not have sufficient permissions for this property.');

        $source->getData($this->makeFetchData('browser', ['users']));
    }

    /**
     * testLastHourIntervalIsReported ensures the hourly interval, which has no date range, is reported to the user.
     */
    public function testLastHourIntervalIsReported()
    {
        $source = new AnalyticsDataSourceFixture;

        $this->expectException(ApplicationException::class);

        $source->getData($this->makeFetchData('browser', ['users'], [
            'dateStart' => null,
            'dateEnd' => null,
            'startTimestamp' => time() - 3600
        ]));
    }

    /**
     * testSessionDurationIsFormatted ensures durations display as minutes and seconds.
     */
    public function testSessionDurationIsFormatted()
    {
        $metric = ReportMetric::findMetricByCode((new AnalyticsDataSource)->getAvailableMetrics(), 'session_duration');

        $this->assertEquals('45s', $metric->formatDisplayValue('45.2'));
        $this->assertEquals('1m 23s', $metric->formatDisplayValue('83.4'));
    }

    /**
     * testDashboardReportsUseRegisteredFields ensures the plugin dashboard only refers to dimensions and metrics of the data source.
     */
    public function testDashboardReportsUseRegisteredFields()
    {
        $source = new AnalyticsDataSource;
        $dimensions = array_map(fn($dimension) => $dimension->getCode(), $source->getAvailableDimensions());
        $metrics = array_map(fn($metric) => $metric->getCode(), $source->getAvailableMetrics());

        $reports = Yaml::parseFile(plugins_path('rainlab/googleanalytics/dashboards/google-analytics.yaml'))['reports'];
        $dataReports = array_filter($reports, fn($report) => isset($report['dataSource']));

        $this->assertCount(8, $dataReports);

        foreach ($dataReports as $name => $report) {
            $this->assertEquals(AnalyticsDataSource::class, $report['dataSource'], $name);
            $this->assertContains($report['dimension'], $dimensions, $name);

            $reportMetrics = isset($report['metric']) ? [$report['metric']] : array_keys($report['metrics']);
            foreach ($reportMetrics as $metric) {
                $this->assertContains($metric, $metrics, $name);
            }

            if (str_starts_with($report['sortBy'] ?? '', 'oc_metric-')) {
                $this->assertContains(substr($report['sortBy'], 10), $reportMetrics, $name);
            }
        }
    }

    /**
     * makeFetchData returns a dashboard request for the first week of September 2026 with the given overrides.
     */
    protected function makeFetchData(string $dimension, array $metrics, array $attributes = []): ReportFetchData
    {
        $data = new ReportFetchData;
        $data->dimensionCode = $dimension;
        $data->metricCodes = $metrics;
        $data->dateStart = Carbon::parse('2026-09-01');
        $data->dateEnd = Carbon::parse('2026-09-07');
        $data->dimensionFilters = [];
        $data->metricsConfiguration = [];
        $data->orderRule = new ReportDataOrderRule(ReportDataOrderRule::ATTR_TYPE_DIMENSION);

        foreach ($attributes as $name => $value) {
            $data->$name = $value;
        }

        return $data;
    }

    /**
     * makeResponse returns a report response from rows of dimension and metric values.
     */
    protected function makeResponse(array $rows, array $totals = [], ?int $rowCount = null): RunReportResponse
    {
        $metricValues = fn($values) => array_map(fn($value) => new MetricValue(['value' => (string) $value]), $values);

        return new RunReportResponse([
            'rows' => array_map(fn($row) => new Row([
                'dimension_values' => array_map(fn($value) => new DimensionValue(['value' => $value]), $row[0]),
                'metric_values' => $metricValues($row[1])
            ]), $rows),
            'totals' => $totals ? [new Row(['metric_values' => $metricValues($totals)])] : [],
            'row_count' => $rowCount ?? count($rows)
        ]);
    }

    /**
     * listNames returns the names of the requested dimensions or metrics.
     */
    protected function listNames(iterable $items): array
    {
        return array_map(fn($item) => $item->getName(), iterator_to_array($items));
    }
}

/**
 * AnalyticsDataSourceFixture replays queued responses instead of calling Google Analytics.
 */
class AnalyticsDataSourceFixture extends AnalyticsDataSource
{
    /**
     * @var RunReportRequest[] requests sent to Google Analytics.
     */
    public $requests = [];

    /**
     * @var array responses to return, or exceptions to throw, in order.
     */
    public $responses = [];

    /**
     * getPropertyName returns a fixed property.
     */
    protected function getPropertyName(): string
    {
        return 'properties/123';
    }

    /**
     * runReport records the request and returns the next queued response.
     */
    protected function runReport(RunReportRequest $request): RunReportResponse
    {
        $this->requests[] = $request;

        $response = array_shift($this->responses) ?? new RunReportResponse;
        if ($response instanceof Exception) {
            throw $response;
        }

        return $response;
    }
}

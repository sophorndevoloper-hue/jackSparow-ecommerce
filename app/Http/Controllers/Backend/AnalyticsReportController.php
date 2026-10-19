<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\Analytics\B2BReportingService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsReportController extends Controller
{
    public function __construct(
        protected B2BReportingService $reportingService
    ) {}

    /**
     * Display the interactive B2B Sales, Inventory, and Financial Analytics dashboard.
     */
    public function index(Request $request): View
    {
        $dates = $this->resolveDateRange($request);
        $startDate = $dates['start_date'];
        $endDate = $dates['end_date'];
        $preset = $dates['preset'];

        $activeTab = $request->input('tab', 'gross-margin');

        // Executive KPI summary for dashboard header cards
        $kpis = $this->reportingService->getExecutiveKpiSummary($startDate, $endDate);

        // Fetch data based on active tab (lazy loads active view, or all tabs can be previewed)
        $grossMarginData = $this->reportingService->getGrossMarginReport($startDate, $endDate);
        $arAgingData = $this->reportingService->getAgingAccountsReceivableReport();
        $stockAgingData = $this->reportingService->getStockAgingReport();
        $rfqData = $this->reportingService->getRfqWinRateReport($startDate, $endDate);
        $rmaData = $this->reportingService->getRmaDefectRateReport();

        return view('backend.reports.index', compact(
            'startDate',
            'endDate',
            'preset',
            'activeTab',
            'kpis',
            'grossMarginData',
            'arAgingData',
            'stockAgingData',
            'rfqData',
            'rmaData'
        ));
    }

    /**
     * JSON API Endpoint returning structured analytics payload for frontend charts & dashboards.
     */
    public function data(Request $request): JsonResponse
    {
        $dates = $this->resolveDateRange($request);
        $startDate = $dates['start_date'];
        $endDate = $dates['end_date'];

        $reportType = $request->input('report', 'all');

        $payload = [
            'status' => 'success',
            'meta' => [
                'generated_at' => Carbon::now()->toIso8601String(),
                'currency' => 'USD',
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'data' => [],
        ];

        switch ($reportType) {
            case 'gross-margin':
                $payload['data']['gross_margin'] = $this->reportingService->getGrossMarginReport($startDate, $endDate);
                break;

            case 'ar-aging':
                $payload['data']['ar_aging'] = $this->reportingService->getAgingAccountsReceivableReport();
                break;

            case 'stock-aging':
                $payload['data']['stock_aging'] = $this->reportingService->getStockAgingReport();
                break;

            case 'rfq-winrate':
                $payload['data']['rfq_performance'] = $this->reportingService->getRfqWinRateReport($startDate, $endDate);
                break;

            case 'rma-defects':
                $payload['data']['rma_defects'] = $this->reportingService->getRmaDefectRateReport();
                break;

            default:
                $payload['data'] = [
                    'kpis' => $this->reportingService->getExecutiveKpiSummary($startDate, $endDate),
                    'gross_margin' => $this->reportingService->getGrossMarginReport($startDate, $endDate),
                    'ar_aging' => $this->reportingService->getAgingAccountsReceivableReport(),
                    'stock_aging' => $this->reportingService->getStockAgingReport(),
                    'rfq_performance' => $this->reportingService->getRfqWinRateReport($startDate, $endDate),
                    'rma_defects' => $this->reportingService->getRmaDefectRateReport(),
                ];
                break;
        }

        return response()->json($payload);
    }

    /**
     * Export analytical dataset as CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $reportType = $request->input('report', 'gross-margin');
        $dates = $this->resolveDateRange($request);
        $filename = "b2b-analytics-{$reportType}-".date('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($reportType, $dates) {
            $handle = fopen('php://output', 'w');

            if ($reportType === 'ar-aging') {
                fputcsv($handle, ['Company ID', 'Company Name', 'Tier', 'Terms', 'Credit Limit', 'Credit Balance', 'Total AR', 'Current', '1-30 Days', '31-60 Days', '61-90 Days', '91+ Days', 'Risk Status']);
                $data = $this->reportingService->getAgingAccountsReceivableReport();
                foreach ($data['companies'] as $r) {
                    fputcsv($handle, [
                        $r['company_id'], $r['company_name'], $r['tier'], $r['payment_terms'],
                        $r['credit_limit'], $r['credit_balance'], $r['total_ar_balance'],
                        $r['bucket_current'], $r['bucket_1_30'], $r['bucket_31_60'],
                        $r['bucket_61_90'], $r['bucket_91_plus'], $r['account_risk_status'],
                    ]);
                }
            } elseif ($reportType === 'stock-aging') {
                fputcsv($handle, ['Category', 'SKU', 'Product Name', 'On Hand', 'Tied Capital', '0-30 Days', '31-60 Days', '61-90 Days', '91+ Days', 'Dead Capital Risk', 'Dead Stock %', 'Status']);
                $data = $this->reportingService->getStockAgingReport();
                foreach ($data['products'] as $r) {
                    fputcsv($handle, [
                        $r['category'], $r['sku'], $r['product_name'], $r['total_on_hand_units'],
                        $r['total_tied_capital'], $r['units_0_30'], $r['units_31_60'],
                        $r['units_61_90'], $r['units_91_plus'], $r['dead_capital_risk'],
                        $r['dead_stock_pct'], $r['action_status'],
                    ]);
                }
            } elseif ($reportType === 'rfq-winrate') {
                fputcsv($handle, ['Customer Tier', 'Submitted', 'Quoted', 'Accepted', 'Rejected', 'Converted', 'Win Rate %', 'Avg Turnaround (Hrs)', 'Pipeline Value', 'Realized Revenue']);
                $data = $this->reportingService->getRfqWinRateReport($dates['start_date'], $dates['end_date']);
                foreach ($data['by_tier'] as $r) {
                    fputcsv($handle, [
                        $r['company_tier'], $r['total_rfqs_submitted'], $r['rfqs_quoted'],
                        $r['rfqs_accepted'], $r['rfqs_rejected'], $r['rfqs_converted'],
                        $r['win_rate_pct'], $r['avg_turnaround_hours'],
                        $r['total_pipeline_value'], $r['total_realized_revenue'],
                    ]);
                }
            } elseif ($reportType === 'rma-defects') {
                fputcsv($handle, ['Brand', 'Units Sold', 'RMA Pending', 'Defective', 'Total Returns', 'RMA Defect Rate %', 'Quality Flag']);
                $data = $this->reportingService->getRmaDefectRateReport();
                foreach ($data['by_brand'] as $r) {
                    fputcsv($handle, [
                        $r['brand'], $r['units_sold'], $r['units_rma_pending'],
                        $r['units_defective'], $r['total_returns'], $r['rma_defect_rate_pct'],
                        $r['quality_flag'],
                    ]);
                }
            } else {
                // Default: Gross Margin by Serial
                fputcsv($handle, ['Serial Number', 'Product SKU', 'Product Name', 'Category', 'Brand', 'Order #', 'Company', 'Tier', 'Cost Price', 'Sold Price', 'Gross Profit', 'Margin %', 'Sold Date']);
                $data = $this->reportingService->getGrossMarginReport($dates['start_date'], $dates['end_date']);
                foreach ($data['serials'] as $r) {
                    fputcsv($handle, [
                        $r['serial_number'], $r['sku'], $r['product_name'], $r['category'],
                        $r['brand'], $r['order_number'], $r['company_name'], $r['customer_tier'],
                        $r['cost_price'], $r['sold_price'], $r['gross_profit'],
                        $r['margin_pct'], $r['sold_at'],
                    ]);
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Resolve start and end dates from presets or inputs.
     *
     * @return array{start_date: string, end_date: string, preset: string}
     */
    protected function resolveDateRange(Request $request): array
    {
        $preset = $request->input('preset', '30days');
        $now = Carbon::now();

        if ($request->filled('start_date') && $request->filled('end_date')) {
            return [
                'start_date' => Carbon::parse($request->input('start_date'))->startOfDay()->toDateString(),
                'end_date' => Carbon::parse($request->input('end_date'))->endOfDay()->toDateString(),
                'preset' => 'custom',
            ];
        }

        switch ($preset) {
            case '7days':
                $start = $now->copy()->subDays(7)->toDateString();
                $end = $now->toDateString();
                break;
            case 'qtd':
                $start = $now->copy()->firstOfQuarter()->toDateString();
                $end = $now->toDateString();
                break;
            case 'ytd':
                $start = $now->copy()->firstOfYear()->toDateString();
                $end = $now->toDateString();
                break;
            case '30days':
            default:
                $start = $now->copy()->subDays(30)->toDateString();
                $end = $now->toDateString();
                $preset = '30days';
                break;
        }

        return [
            'start_date' => $start,
            'end_date' => $end,
            'preset' => $preset,
        ];
    }
}

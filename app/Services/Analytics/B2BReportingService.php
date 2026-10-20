<?php

namespace App\Services\Analytics;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quote;
use App\Models\SerialNumber;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class B2BReportingService
{
    /**
     * 1. Real Gross Margin by Serial Number (Hardware Profitability)
     * Aggregates profit and margin by individual serial, product SKU, category, and customer tier.
     *
     * @return array{
     *     serials: array<int, array<string, mixed>>,
     *     by_sku: array<int, array<string, mixed>>,
     *     by_category: array<int, array<string, mixed>>,
     *     by_tier: array<int, array<string, mixed>>,
     *     summary: array<string, mixed>
     * }
     */
    public function getGrossMarginReport(?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();

        $query = SerialNumber::query()
            ->with([
                'product.category',
                'product.brand',
                'orderItem.order.customer.customerGroup',
            ])
            ->whereIn('status', [SerialNumber::STATUS_SOLD, 'sold', 'SOLD', SerialNumber::STATUS_SHIPPED])
            ->whereNotNull('order_item_id');

        if ($startDate || $endDate) {
            $query->where(function ($q) use ($start, $end) {
                $q->whereBetween('outbound_date', [$start->toDateString(), $end->toDateString()])
                    ->orWhereBetween('updated_at', [$start, $end]);
            });
        }

        $serials = $query->get();

        $serialRecords = [];
        $totalCogs = 0.0;
        $totalRevenue = 0.0;
        $totalGrossProfit = 0.0;

        foreach ($serials as $serial) {
            $product = $serial->product;
            $orderItem = $serial->orderItem;
            $order = $orderItem?->order;
            $customer = $order?->customer;
            $group = $customer?->customerGroup;

            $costPrice = (float) ($serial->cost_price ?? $product?->cost_price ?? 0);
            $soldPrice = (float) ($orderItem?->unit_price ?? $product?->base_price ?? 0);
            $profit = $soldPrice - $costPrice;
            $marginPct = $soldPrice > 0 ? round(($profit / $soldPrice) * 100, 2) : 0.0;

            $tier = $customer?->tier ?: ($group?->name ?: 'Standard');

            $serialRecords[] = [
                'serial_id' => $serial->id,
                'serial_number' => $serial->serial_number,
                'product_id' => $product?->id,
                'product_name' => $product?->name ?? 'Unknown Component',
                'sku' => $product?->sku ?? 'N/A',
                'category' => $product?->category?->name ?? 'Uncategorized',
                'brand' => $product?->brand?->name ?? 'Generic',
                'order_number' => $order?->order_number ?? 'N/A',
                'company_name' => $customer?->company_name ?: ($customer?->name ?: 'Retail / Direct'),
                'customer_tier' => $tier,
                'cost_price' => $costPrice,
                'sold_price' => $soldPrice,
                'gross_profit' => $profit,
                'margin_pct' => $marginPct,
                'sold_at' => $serial->outbound_date?->format('Y-m-d') ?: $serial->updated_at->format('Y-m-d'),
            ];

            $totalCogs += $costPrice;
            $totalRevenue += $soldPrice;
            $totalGrossProfit += $profit;
        }

        $collection = collect($serialRecords);

        // Group by SKU
        $bySku = $collection->groupBy('sku')->map(function (Collection $items, string $sku) {
            $cogs = (float) $items->sum('cost_price');
            $revenue = (float) $items->sum('sold_price');
            $profit = (float) $items->sum('gross_profit');
            $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0.0;
            $first = $items->first();

            return [
                'sku' => $sku,
                'product_name' => $first['product_name'] ?? $sku,
                'category' => $first['category'] ?? 'General',
                'units_sold' => $items->count(),
                'total_cogs' => round($cogs, 2),
                'total_revenue' => round($revenue, 2),
                'total_profit' => round($profit, 2),
                'margin_pct' => $margin,
            ];
        })->values()->sortByDesc('total_revenue')->values()->all();

        // Group by Category
        $byCategory = $collection->groupBy('category')->map(function (Collection $items, string $category) {
            $cogs = (float) $items->sum('cost_price');
            $revenue = (float) $items->sum('sold_price');
            $profit = (float) $items->sum('gross_profit');
            $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0.0;

            return [
                'category' => $category,
                'units_sold' => $items->count(),
                'total_cogs' => round($cogs, 2),
                'total_revenue' => round($revenue, 2),
                'total_profit' => round($profit, 2),
                'margin_pct' => $margin,
            ];
        })->values()->sortByDesc('total_revenue')->values()->all();

        // Group by Customer Tier
        $byTier = $collection->groupBy('customer_tier')->map(function (Collection $items, string $tier) {
            $cogs = (float) $items->sum('cost_price');
            $revenue = (float) $items->sum('sold_price');
            $profit = (float) $items->sum('gross_profit');
            $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0.0;

            return [
                'customer_tier' => $tier,
                'units_sold' => $items->count(),
                'total_cogs' => round($cogs, 2),
                'total_revenue' => round($revenue, 2),
                'total_profit' => round($profit, 2),
                'margin_pct' => $margin,
            ];
        })->values()->sortByDesc('total_revenue')->values()->all();

        $overallMarginPct = $totalRevenue > 0 ? round(($totalGrossProfit / $totalRevenue) * 100, 2) : 0.0;

        return [
            'serials' => $serialRecords,
            'by_sku' => $bySku,
            'by_category' => $byCategory,
            'by_tier' => $byTier,
            'summary' => [
                'total_units_sold' => count($serialRecords),
                'total_cogs' => round($totalCogs, 2),
                'total_revenue' => round($totalRevenue, 2),
                'total_gross_profit' => round($totalGrossProfit, 2),
                'overall_margin_pct' => $overallMarginPct,
            ],
        ];
    }

    /**
     * 2. Aging Accounts Receivable (A/R) for Net Terms (Net 30/60)
     * Groups unpaid orders into aging buckets and flags credit hold / high risk accounts.
     *
     * @return array{
     *     companies: array<int, array<string, mixed>>,
     *     summary: array<string, mixed>
     * }
     */
    public function getAgingAccountsReceivableReport(): array
    {
        $today = Carbon::today();

        // Unpaid or open orders
        $orders = Order::query()
            ->with(['customer.customerGroup'])
            ->where(function ($q) {
                $q->whereIn('payment_status', ['pending', 'unpaid', 'partially_paid', 'invoiced'])
                    ->orWhere(function ($sub) {
                        $sub->whereNull('payment_status')
                            ->where('status', '!=', 'cancelled');
                    });
            })
            ->whereNotIn('status', ['cancelled'])
            ->get();

        $groupedByCompany = $orders->groupBy(function (Order $order) {
            return $order->customer_id ? (string) $order->customer_id : ('anon_'.$order->customer_name);
        });

        $companyRows = [];
        $totalArOutstanding = 0.0;
        $totalCurrent = 0.0;
        $totalOverdue1to30 = 0.0;
        $totalOverdue31to60 = 0.0;
        $totalOverdue61to90 = 0.0;
        $totalOverdue91Plus = 0.0;

        foreach ($groupedByCompany as $companyKey => $compOrders) {
            $firstOrder = $compOrders->first();
            $customer = $firstOrder?->customer;
            $group = $customer?->customerGroup;

            $companyName = $customer?->company_name ?: ($customer?->name ?: $firstOrder->customer_name);
            $tier = $customer?->tier ?: ($group?->name ?: 'Standard');
            $paymentTerms = $firstOrder?->paymentTerm?->name
                ?? $group?->paymentTerms()?->where('is_default', true)->first()?->name
                ?? ($group?->payment_terms_days ? "Net {$group->payment_terms_days}" : 'Net 30');
            $creditLimit = (float) ($customer?->credit_limit ?? $group?->credit_limit ?? 50000);
            $creditBalance = (float) ($customer?->credit_balance ?? 0);

            $currentNotDue = 0.0;
            $overdue1to30 = 0.0;
            $overdue31to60 = 0.0;
            $overdue61to90 = 0.0;
            $overdue91Plus = 0.0;
            $companyTotalAr = 0.0;

            foreach ($compOrders as $order) {
                $amount = (float) $order->total_amount;
                $companyTotalAr += $amount;

                // Determine due date
                $dueDate = $order->payment_due_date
                    ? Carbon::parse($order->payment_due_date)
                    : ($order->created_at ? $order->created_at->copy()->addDays(30) : $today->copy()->addDays(30));

                $daysOverdue = $today->diffInDays($dueDate, false); // Negative if overdue

                if ($daysOverdue >= 0) {
                    // Not overdue yet
                    $currentNotDue += $amount;
                } else {
                    $overdueDays = abs($daysOverdue);
                    if ($overdueDays <= 30) {
                        $overdue1to30 += $amount;
                    } elseif ($overdueDays <= 60) {
                        $overdue31to60 += $amount;
                    } elseif ($overdueDays <= 90) {
                        $overdue61to90 += $amount;
                    } else {
                        $overdue91Plus += $amount;
                    }
                }
            }

            // Automated Risk Classification
            $riskStatus = 'GOOD_STANDING';
            if ($overdue91Plus > 0) {
                $riskStatus = 'CREDIT_HOLD';
            } elseif ($overdue61to90 > 0 || ($creditBalance > 0 && $creditBalance > $creditLimit)) {
                $riskStatus = 'HIGH_RISK';
            } elseif ($overdue1to30 > 0 || $overdue31to60 > 0) {
                $riskStatus = 'MODERATE_RISK';
            }

            $companyRows[] = [
                'company_id' => $customer?->id,
                'company_name' => $companyName,
                'customer_email' => $customer?->email ?: $firstOrder->customer_email,
                'customer_phone' => $customer?->phone ?: $firstOrder->customer_phone,
                'tier' => $tier,
                'payment_terms' => $paymentTerms,
                'credit_limit' => $creditLimit,
                'credit_balance' => $creditBalance,
                'open_order_count' => $compOrders->count(),
                'total_ar_balance' => round($companyTotalAr, 2),
                'bucket_current' => round($currentNotDue, 2),
                'bucket_1_30' => round($overdue1to30, 2),
                'bucket_31_60' => round($overdue31to60, 2),
                'bucket_61_90' => round($overdue61to90, 2),
                'bucket_91_plus' => round($overdue91Plus, 2),
                'account_risk_status' => $riskStatus,
            ];

            $totalArOutstanding += $companyTotalAr;
            $totalCurrent += $currentNotDue;
            $totalOverdue1to30 += $overdue1to30;
            $totalOverdue31to60 += $overdue31to60;
            $totalOverdue61to90 += $overdue61to90;
            $totalOverdue91Plus += $overdue91Plus;
        }

        // Sort by highest overdue 91+ and total balance
        usort($companyRows, function ($a, $b) {
            if ($a['bucket_91_plus'] != $b['bucket_91_plus']) {
                return $b['bucket_91_plus'] <=> $a['bucket_91_plus'];
            }

            return $b['total_ar_balance'] <=> $a['total_ar_balance'];
        });

        $creditHoldCount = count(array_filter($companyRows, fn ($r) => $r['account_risk_status'] === 'CREDIT_HOLD'));
        $highRiskCount = count(array_filter($companyRows, fn ($r) => $r['account_risk_status'] === 'HIGH_RISK'));

        return [
            'companies' => $companyRows,
            'summary' => [
                'total_ar_outstanding' => round($totalArOutstanding, 2),
                'total_current' => round($totalCurrent, 2),
                'total_overdue_1_30' => round($totalOverdue1to30, 2),
                'total_overdue_31_60' => round($totalOverdue31to60, 2),
                'total_overdue_61_90' => round($totalOverdue61to90, 2),
                'total_overdue_91_plus' => round($totalOverdue91Plus, 2),
                'credit_hold_accounts' => $creditHoldCount,
                'high_risk_accounts' => $highRiskCount,
            ],
        ];
    }

    /**
     * 3. Stock Aging & Dead Inventory (Depreciation Risk)
     * Identifies unsold hardware sitting in warehouse by intake age brackets.
     *
     * @return array{
     *     products: array<int, array<string, mixed>>,
     *     summary: array<string, mixed>
     * }
     */
    public function getStockAgingReport(): array
    {
        $today = Carbon::today();

        $availableSerials = SerialNumber::query()
            ->with(['product.category', 'product.brand'])
            ->whereIn('status', [SerialNumber::STATUS_IN_STOCK, 'available', 'IN_STOCK'])
            ->get();

        $groupedByProduct = $availableSerials->groupBy('product_id');

        $productRows = [];
        $totalUnits = 0;
        $totalCapital = 0.0;
        $totalUnits0to30 = 0;
        $totalUnits31to60 = 0;
        $totalUnits61to90 = 0;
        $totalUnits91Plus = 0;
        $totalDeadCapitalRisk = 0.0;

        foreach ($groupedByProduct as $productId => $serials) {
            $product = $serials->first()?->product;
            if (! $product) {
                continue;
            }

            $units0to30 = 0;
            $units31to60 = 0;
            $units61to90 = 0;
            $units91Plus = 0;
            $tiedCapital = 0.0;
            $deadCapital = 0.0;

            foreach ($serials as $sn) {
                $cost = (float) ($sn->cost_price ?: $product->cost_price ?: 0);
                $tiedCapital += $cost;

                $intakeDate = $sn->inbound_date
                    ? Carbon::parse($sn->inbound_date)
                    : ($sn->created_at ? $sn->created_at->copy() : $today);

                $shelfDays = max(0, $intakeDate->diffInDays($today));

                if ($shelfDays <= 30) {
                    $units0to30++;
                } elseif ($shelfDays <= 60) {
                    $units31to60++;
                } elseif ($shelfDays <= 90) {
                    $units61to90++;
                } else {
                    $units91Plus++;
                    $deadCapital += $cost;
                }
            }

            $totalOnHand = $serials->count();
            $deadStockPct = $totalOnHand > 0 ? round(($units91Plus / $totalOnHand) * 100, 1) : 0.0;

            $productRows[] = [
                'product_id' => $product->id,
                'sku' => $product->sku,
                'product_name' => $product->name,
                'category' => $product->category?->name ?? 'General',
                'brand' => $product->brand?->name ?? 'Generic',
                'total_on_hand_units' => $totalOnHand,
                'total_tied_capital' => round($tiedCapital, 2),
                'units_0_30' => $units0to30,
                'units_31_60' => $units31to60,
                'units_61_90' => $units61to90,
                'units_91_plus' => $units91Plus,
                'dead_capital_risk' => round($deadCapital, 2),
                'dead_stock_pct' => $deadStockPct,
                'action_status' => $deadStockPct >= 50 ? 'DEAD_STOCK_CRITICAL' : ($units91Plus > 0 ? 'SLOW_MOVING' : 'HEALTHY'),
            ];

            $totalUnits += $totalOnHand;
            $totalCapital += $tiedCapital;
            $totalUnits0to30 += $units0to30;
            $totalUnits31to60 += $units31to60;
            $totalUnits61to90 += $units61to90;
            $totalUnits91Plus += $units91Plus;
            $totalDeadCapitalRisk += $deadCapital;
        }

        // Sort by highest dead capital risk first
        usort($productRows, fn ($a, $b) => $b['dead_capital_risk'] <=> $a['dead_capital_risk']);

        $overallDeadStockRatio = $totalUnits > 0 ? round(($totalUnits91Plus / $totalUnits) * 100, 1) : 0.0;

        return [
            'products' => $productRows,
            'summary' => [
                'total_on_hand_units' => $totalUnits,
                'total_tied_capital' => round($totalCapital, 2),
                'units_0_30_healthy' => $totalUnits0to30,
                'units_31_60_warning' => $totalUnits31to60,
                'units_61_90_slow' => $totalUnits61to90,
                'units_91_plus_dead' => $totalUnits91Plus,
                'dead_capital_risk' => round($totalDeadCapitalRisk, 2),
                'dead_stock_ratio_pct' => $overallDeadStockRatio,
            ],
        ];
    }

    /**
     * 4. RFQ Conversion & Win-Rate Report
     * Tracks wholesale quotation pipeline performance, win rates, and quotation SLA turnaround.
     *
     * @return array{
     *     by_tier: array<int, array<string, mixed>>,
     *     summary: array<string, mixed>
     * }
     */
    public function getRfqWinRateReport(?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();

        $quotes = Quote::query()
            ->with(['convertedOrder'])
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $groupedByTier = $quotes->groupBy(fn (Quote $q) => ucfirst($q->company_tier ?: 'Standard'));

        $tierRows = [];
        $totalSubmitted = $quotes->count();
        $totalQuoted = 0;
        $totalAccepted = 0;
        $totalRejected = 0;
        $totalConverted = 0;
        $totalPipelineValue = 0.0;
        $totalRealizedRevenue = 0.0;
        $turnaroundHoursList = [];

        foreach ($groupedByTier as $tierName => $tierQuotes) {
            $submitted = $tierQuotes->count();
            $quoted = $tierQuotes->whereIn('status', ['sent', 'quoted', 'accepted', 'converted'])->count();
            $accepted = $tierQuotes->whereIn('status', ['accepted', 'converted'])->count();
            $rejected = $tierQuotes->where('status', 'rejected')->count();
            $converted = $tierQuotes->where('status', 'converted')->count();

            $closedCount = $accepted + $rejected;
            $winRate = $closedCount > 0 ? round(($accepted / $closedCount) * 100, 2) : 0.0;

            $pipelineVal = (float) $tierQuotes->sum('total_quoted_price');
            $realizedRev = (float) $tierQuotes->where('status', 'converted')->sum(function (Quote $q) {
                return $q->convertedOrder?->total_amount ?: $q->total_quoted_price;
            });

            // Calculate Turnaround hours
            $tierHours = [];
            foreach ($tierQuotes as $q) {
                if ($q->quoted_at && $q->created_at) {
                    $tierHours[] = abs($q->created_at->diffInMinutes($q->quoted_at)) / 60.0;
                }
            }

            $avgTurnaround = count($tierHours) > 0 ? round(array_sum($tierHours) / count($tierHours), 1) : 0.0;

            $tierRows[] = [
                'company_tier' => $tierName,
                'total_rfqs_submitted' => $submitted,
                'rfqs_quoted' => $quoted,
                'rfqs_accepted' => $accepted,
                'rfqs_rejected' => $rejected,
                'rfqs_converted' => $converted,
                'win_rate_pct' => $winRate,
                'avg_turnaround_hours' => $avgTurnaround,
                'total_pipeline_value' => round($pipelineVal, 2),
                'total_realized_revenue' => round($realizedRev, 2),
            ];

            $totalQuoted += $quoted;
            $totalAccepted += $accepted;
            $totalRejected += $rejected;
            $totalConverted += $converted;
            $totalPipelineValue += $pipelineVal;
            $totalRealizedRevenue += $realizedRev;
            $turnaroundHoursList = array_merge($turnaroundHoursList, $tierHours);
        }

        $overallClosed = $totalAccepted + $totalRejected;
        $overallWinRate = $overallClosed > 0 ? round(($totalAccepted / $overallClosed) * 100, 2) : 0.0;
        $overallAvgTurnaround = count($turnaroundHoursList) > 0 ? round(array_sum($turnaroundHoursList) / count($turnaroundHoursList), 1) : 0.0;

        return [
            'by_tier' => $tierRows,
            'summary' => [
                'total_rfqs_submitted' => $totalSubmitted,
                'rfqs_quoted' => $totalQuoted,
                'rfqs_accepted' => $totalAccepted,
                'rfqs_rejected' => $totalRejected,
                'rfqs_converted' => $totalConverted,
                'win_rate_pct' => $overallWinRate,
                'avg_turnaround_hours' => $overallAvgTurnaround,
                'total_pipeline_value' => round($totalPipelineValue, 2),
                'total_realized_revenue' => round($totalRealizedRevenue, 2),
            ],
        ];
    }

    /**
     * 5. Hardware RMA & Defect Rate by SKU/Brand
     * Identifies bad hardware batches and supplier defect spikes by Brand and SKU.
     *
     * @return array{
     *     by_brand: array<int, array<string, mixed>>,
     *     by_sku: array<int, array<string, mixed>>,
     *     summary: array<string, mixed>
     * }
     */
    public function getRmaDefectRateReport(): array
    {
        $serials = SerialNumber::query()
            ->with(['product.category', 'product.brand'])
            ->whereIn('status', [
                SerialNumber::STATUS_SOLD, 'sold', 'SOLD', SerialNumber::STATUS_SHIPPED,
                SerialNumber::STATUS_RETURNED_RMA, 'rma_pending', 'RETURNED_RMA',
                SerialNumber::STATUS_DEFECTIVE_SCRAP, 'defective', 'DEFECTIVE_SCRAP',
            ])
            ->get();

        $groupedByBrand = $serials->groupBy(fn (SerialNumber $sn) => $sn->product?->brand?->name ?? 'Generic / OEM');

        $brandRows = [];
        $totalSoldAll = 0;
        $totalRmaAll = 0;
        $totalDefectiveAll = 0;

        foreach ($groupedByBrand as $brandName => $brandSerials) {
            $sold = $brandSerials->filter(fn ($s) => in_array($s->status, [SerialNumber::STATUS_SOLD, 'sold', 'SOLD', SerialNumber::STATUS_SHIPPED]))->count();
            $rma = $brandSerials->filter(fn ($s) => in_array($s->status, [SerialNumber::STATUS_RETURNED_RMA, 'rma_pending', 'RETURNED_RMA']))->count();
            $defective = $brandSerials->filter(fn ($s) => in_array($s->status, [SerialNumber::STATUS_DEFECTIVE_SCRAP, 'defective', 'DEFECTIVE_SCRAP']))->count();

            $totalReturns = $rma + $defective;
            $denominator = $sold > 0 ? $sold : ($totalReturns > 0 ? $totalReturns : 0);
            $defectRate = $denominator > 0 ? round(($totalReturns / $denominator) * 100, 2) : 0.0;

            $statusFlag = 'NORMAL';
            if ($defectRate >= 4.0 && $totalReturns >= 2) {
                $statusFlag = 'CRITICAL_ACTION_REQUIRED';
            } elseif ($defectRate >= 2.0 && $totalReturns >= 1) {
                $statusFlag = 'ELEVATED_FAILURE_RATE';
            }

            $brandRows[] = [
                'brand' => $brandName,
                'units_sold' => $sold,
                'units_rma_pending' => $rma,
                'units_defective' => $defective,
                'total_returns' => $totalReturns,
                'rma_defect_rate_pct' => $defectRate,
                'quality_flag' => $statusFlag,
            ];

            $totalSoldAll += $sold;
            $totalRmaAll += $rma;
            $totalDefectiveAll += $defective;
        }

        // Group by SKU to spot individual troublesome models
        $groupedBySku = $serials->groupBy(fn (SerialNumber $sn) => $sn->product?->sku ?? 'UNKNOWN');
        $skuRows = [];

        foreach ($groupedBySku as $sku => $skuSerials) {
            $product = $skuSerials->first()?->product;
            $sold = $skuSerials->filter(fn ($s) => in_array($s->status, [SerialNumber::STATUS_SOLD, 'sold', 'SOLD', SerialNumber::STATUS_SHIPPED]))->count();
            $rma = $skuSerials->filter(fn ($s) => in_array($s->status, [SerialNumber::STATUS_RETURNED_RMA, 'rma_pending', 'RETURNED_RMA']))->count();
            $defective = $skuSerials->filter(fn ($s) => in_array($s->status, [SerialNumber::STATUS_DEFECTIVE_SCRAP, 'defective', 'DEFECTIVE_SCRAP']))->count();

            $totalReturns = $rma + $defective;
            $denominator = $sold > 0 ? $sold : ($totalReturns > 0 ? $totalReturns : 0);
            $defectRate = $denominator > 0 ? round(($totalReturns / $denominator) * 100, 2) : 0.0;

            $statusFlag = 'NORMAL';
            if ($defectRate >= 4.0 && $totalReturns >= 2) {
                $statusFlag = 'CRITICAL_ACTION_REQUIRED';
            } elseif ($defectRate >= 2.0 && $totalReturns >= 1) {
                $statusFlag = 'ELEVATED_FAILURE_RATE';
            }

            $skuRows[] = [
                'sku' => $sku,
                'product_name' => $product?->name ?? $sku,
                'category' => $product?->category?->name ?? 'General',
                'brand' => $product?->brand?->name ?? 'Generic',
                'units_sold' => $sold,
                'units_rma_pending' => $rma,
                'units_defective' => $defective,
                'total_returns' => $totalReturns,
                'rma_defect_rate_pct' => $defectRate,
                'quality_flag' => $statusFlag,
            ];
        }

        usort($brandRows, fn ($a, $b) => $b['rma_defect_rate_pct'] <=> $a['rma_defect_rate_pct']);
        usort($skuRows, fn ($a, $b) => $b['rma_defect_rate_pct'] <=> $a['rma_defect_rate_pct']);

        $totalReturnsAll = $totalRmaAll + $totalDefectiveAll;
        $overallDefectRate = $totalSoldAll > 0 ? round(($totalReturnsAll / $totalSoldAll) * 100, 2) : 0.0;

        return [
            'by_brand' => $brandRows,
            'by_sku' => $skuRows,
            'summary' => [
                'total_units_sold' => $totalSoldAll,
                'total_units_rma' => $totalRmaAll,
                'total_units_defective' => $totalDefectiveAll,
                'total_returns' => $totalReturnsAll,
                'overall_defect_rate_pct' => $overallDefectRate,
            ],
        ];
    }

    /**
     * Executive Overview KPIs for dashboard summary cards.
     *
     * @return array<string, mixed>
     */
    public function getExecutiveKpiSummary(?string $startDate = null, ?string $endDate = null): array
    {
        $marginData = $this->getGrossMarginReport($startDate, $endDate);
        $arData = $this->getAgingAccountsReceivableReport();
        $stockData = $this->getStockAgingReport();
        $rfqData = $this->getRfqWinRateReport($startDate, $endDate);
        $rmaData = $this->getRmaDefectRateReport();

        return [
            'total_revenue' => $marginData['summary']['total_revenue'],
            'total_gross_profit' => $marginData['summary']['total_gross_profit'],
            'overall_margin_pct' => $marginData['summary']['overall_margin_pct'],
            'total_ar_outstanding' => $arData['summary']['total_ar_outstanding'],
            'total_ar_overdue_91_plus' => $arData['summary']['total_overdue_91_plus'],
            'credit_hold_accounts' => $arData['summary']['credit_hold_accounts'],
            'dead_capital_risk' => $stockData['summary']['dead_capital_risk'],
            'dead_stock_ratio_pct' => $stockData['summary']['dead_stock_ratio_pct'],
            'rfq_win_rate_pct' => $rfqData['summary']['win_rate_pct'],
            'rfq_avg_turnaround_hours' => $rfqData['summary']['avg_turnaround_hours'],
            'overall_defect_rate_pct' => $rmaData['summary']['overall_defect_rate_pct'],
        ];
    }
}

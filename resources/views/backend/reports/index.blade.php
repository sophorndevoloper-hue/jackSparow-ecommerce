<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i></span>
                <div>
                    <h1 class="h3 mb-1">Financial, Inventory &amp; Sales Analytics</h1>
                    <p class="text-muted mb-0">Hardware gross margins, A/R Net 30/60 aging, warehouse dead stock, RFQ win rates, and RMA defects</p>
                </div>
            </div>
            <div class="heading-actions d-flex align-items-center gap-2">
                <a href="{{ route('admin.reports.export', ['report' => $activeTab, 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-file-earmark-arrow-down me-1"></i> Export Active CSV
                </a>
                <a href="{{ route('admin.reports.data', ['report' => 'all', 'start_date' => $startDate, 'end_date' => $endDate]) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-code-slash me-1"></i> JSON API
                </a>
            </div>
        </div>

        <!-- Date Range Filter Bar -->
        <div class="panel p-3 mb-4 bg-light border">
            <form action="{{ route('admin.reports.index') }}" method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                
                <div class="col-12 col-md-auto d-flex align-items-center gap-1">
                    <span class="small fw-semibold text-muted text-uppercase me-1">Quick Presets:</span>
                    <a href="{{ route('admin.reports.index', ['tab' => $activeTab, 'preset' => '30days']) }}" 
                       class="btn btn-sm {{ $preset === '30days' ? 'btn-primary' : 'btn-outline-secondary' }}">
                       Last 30 Days
                    </a>
                    <a href="{{ route('admin.reports.index', ['tab' => $activeTab, 'preset' => 'qtd']) }}" 
                       class="btn btn-sm {{ $preset === 'qtd' ? 'btn-primary' : 'btn-outline-secondary' }}">
                       QTD
                    </a>
                    <a href="{{ route('admin.reports.index', ['tab' => $activeTab, 'preset' => 'ytd']) }}" 
                       class="btn btn-sm {{ $preset === 'ytd' ? 'btn-primary' : 'btn-outline-secondary' }}">
                       YTD
                    </a>
                </div>

                <div class="col-12 col-md-auto ms-md-auto d-flex align-items-center gap-2">
                    <span class="small text-muted">Custom:</span>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}" style="max-width: 150px;">
                    <span class="text-muted">&ndash;</span>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}" style="max-width: 150px;">
                    <button type="submit" class="btn btn-dark btn-sm">
                        <i class="bi bi-funnel-fill me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Executive KPI Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-xl-2">
                <div class="panel p-3 h-100 border-start border-primary border-4">
                    <div class="text-muted small fw-semibold text-uppercase">Realized Revenue</div>
                    <div class="fs-4 fw-bold text-dark font-monospace mt-1">${{ number_format($kpis['total_revenue'], 2) }}</div>
                    <small class="text-muted">Total sales turnover</small>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="panel p-3 h-100 border-start border-success border-4">
                    <div class="text-muted small fw-semibold text-uppercase">Gross Profit</div>
                    <div class="fs-4 fw-bold text-success font-monospace mt-1">${{ number_format($kpis['total_gross_profit'], 2) }}</div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $kpis['overall_margin_pct'] }}% Margin</span>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="panel p-3 h-100 border-start border-info border-4">
                    <div class="text-muted small fw-semibold text-uppercase">A/R Outstanding</div>
                    <div class="fs-4 fw-bold text-dark font-monospace mt-1">${{ number_format($kpis['total_ar_outstanding'], 2) }}</div>
                    <small class="text-muted">Open net term balances</small>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="panel p-3 h-100 border-start {{ $kpis['credit_hold_accounts'] > 0 ? 'border-danger bg-danger-subtle bg-opacity-10' : 'border-secondary' }} border-4">
                    <div class="text-muted small fw-semibold text-uppercase">91+ Days Overdue</div>
                    <div class="fs-4 fw-bold text-danger font-monospace mt-1">${{ number_format($kpis['total_ar_overdue_91_plus'], 2) }}</div>
                    <span class="badge {{ $kpis['credit_hold_accounts'] > 0 ? 'bg-danger' : 'bg-secondary' }}">
                        {{ $kpis['credit_hold_accounts'] }} Accounts Hold
                    </span>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="panel p-3 h-100 border-start border-warning border-4">
                    <div class="text-muted small fw-semibold text-uppercase">Dead Capital Risk</div>
                    <div class="fs-4 fw-bold text-warning-emphasis font-monospace mt-1">${{ number_format($kpis['dead_capital_risk'], 2) }}</div>
                    <small class="text-muted">{{ $kpis['dead_stock_ratio_pct'] }}% of inventory > 90d</small>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="panel p-3 h-100 border-start border-purple border-4" style="border-left-color: #6f42c1 !important;">
                    <div class="text-muted small fw-semibold text-uppercase">RFQ Win Rate</div>
                    <div class="fs-4 fw-bold text-purple font-monospace mt-1" style="color: #6f42c1;">{{ $kpis['rfq_win_rate_pct'] }}%</div>
                    <small class="text-muted">Avg SLA: {{ $kpis['rfq_avg_turnaround_hours'] }}h</small>
                </div>
            </div>
        </div>

        <!-- Tabbed Reports Navigation -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'gross-margin' ? 'active fw-bold' : '' }}" 
                   href="{{ route('admin.reports.index', ['tab' => 'gross-margin', 'preset' => $preset, 'start_date' => $startDate, 'end_date' => $endDate]) }}">
                    <i class="bi bi-currency-dollar me-1"></i> 1. Gross Margin by Serial
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'ar-aging' ? 'active fw-bold' : '' }}" 
                   href="{{ route('admin.reports.index', ['tab' => 'ar-aging', 'preset' => $preset, 'start_date' => $startDate, 'end_date' => $endDate]) }}">
                    <i class="bi bi-clock-history me-1"></i> 2. Aging Accounts Receivable (A/R)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'stock-aging' ? 'active fw-bold' : '' }}" 
                   href="{{ route('admin.reports.index', ['tab' => 'stock-aging', 'preset' => $preset, 'start_date' => $startDate, 'end_date' => $endDate]) }}">
                    <i class="bi bi-boxes me-1"></i> 3. Stock Aging &amp; Dead Inventory
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'rfq-winrate' ? 'active fw-bold' : '' }}" 
                   href="{{ route('admin.reports.index', ['tab' => 'rfq-winrate', 'preset' => $preset, 'start_date' => $startDate, 'end_date' => $endDate]) }}">
                    <i class="bi bi-file-earmark-text me-1"></i> 4. RFQ Conversion &amp; Win Rate
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'rma-defects' ? 'active fw-bold' : '' }}" 
                   href="{{ route('admin.reports.index', ['tab' => 'rma-defects', 'preset' => $preset, 'start_date' => $startDate, 'end_date' => $endDate]) }}">
                    <i class="bi bi-shield-x me-1"></i> 5. Hardware RMA &amp; Defect Rate
                </a>
            </li>
        </ul>

        <!-- Tab 1: Gross Margin by Serial Number -->
        @if($activeTab === 'gross-margin')
            <div class="panel p-4 mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Hardware Profitability: Real Gross Margin by Serial</h5>
                        <small class="text-muted">Calculates exact sold price minus physical unit cost price down to individual serial numbers</small>
                    </div>
                    <div class="text-end">
                        <span class="fs-6 fw-bold font-monospace text-success">${{ number_format($grossMarginData['summary']['total_gross_profit'], 2) }} Profit</span>
                        <span class="badge bg-success ms-2">{{ $grossMarginData['summary']['overall_margin_pct'] }}% Margin</span>
                    </div>
                </div>

                <!-- Margin Summaries by Customer Tier & Category -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-lg-6">
                        <div class="border rounded p-3 bg-light">
                            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-tags-fill text-primary me-2"></i>Profitability by Customer Tier</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr class="text-muted small">
                                            <th>Tier</th>
                                            <th class="text-center">Units</th>
                                            <th class="text-end">COGS</th>
                                            <th class="text-end">Revenue</th>
                                            <th class="text-end">Gross Profit</th>
                                            <th class="text-end">Margin %</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($grossMarginData['by_tier'] as $tier)
                                            <tr>
                                                <td class="fw-semibold">{{ $tier['customer_tier'] }}</td>
                                                <td class="text-center">{{ $tier['units_sold'] }}</td>
                                                <td class="text-end font-monospace small">${{ number_format($tier['total_cogs'], 2) }}</td>
                                                <td class="text-end font-monospace small">${{ number_format($tier['total_revenue'], 2) }}</td>
                                                <td class="text-end font-monospace small text-success fw-bold">${{ number_format($tier['total_profit'], 2) }}</td>
                                                <td class="text-end fw-bold">{{ $tier['margin_pct'] }}%</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="text-center text-muted py-2">No sales recorded for this period.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="border rounded p-3 bg-light">
                            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-grid-3x3-gap-fill text-primary me-2"></i>Profitability by Product Category</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr class="text-muted small">
                                            <th>Category</th>
                                            <th class="text-center">Units</th>
                                            <th class="text-end">Revenue</th>
                                            <th class="text-end">Gross Profit</th>
                                            <th class="text-end">Margin %</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($grossMarginData['by_category'] as $cat)
                                            <tr>
                                                <td class="fw-semibold">{{ $cat['category'] }}</td>
                                                <td class="text-center">{{ $cat['units_sold'] }}</td>
                                                <td class="text-end font-monospace small">${{ number_format($cat['total_revenue'], 2) }}</td>
                                                <td class="text-end font-monospace small text-success fw-bold">${{ number_format($cat['total_profit'], 2) }}</td>
                                                <td class="text-end fw-bold">{{ $cat['margin_pct'] }}%</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center text-muted py-2">No category sales found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Serial Ledger Table -->
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Individual Serial Sales Ledger ({{ count($grossMarginData['serials']) }} Units)</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th>Serial Number</th>
                                <th>Product SKU &amp; Name</th>
                                <th>Order #</th>
                                <th>Client &amp; Tier</th>
                                <th class="text-end">Unit Cost</th>
                                <th class="text-end">Sold Price</th>
                                <th class="text-end">Gross Profit</th>
                                <th class="text-end">Margin %</th>
                                <th class="text-center">Sold Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($grossMarginData['serials'] as $s)
                                <tr>
                                    <td>
                                        <span class="font-monospace fw-bold text-primary">{{ $s['serial_number'] }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $s['product_name'] }}</div>
                                        <div class="text-muted small font-monospace">{{ $s['sku'] }} &bull; {{ $s['brand'] }}</div>
                                    </td>
                                    <td>
                                        <span class="font-monospace text-secondary">{{ $s['order_number'] }}</span>
                                    </td>
                                    <td>
                                        <div>{{ $s['company_name'] }}</div>
                                        <span class="badge bg-light text-dark border" style="font-size: 10px;">{{ $s['customer_tier'] }}</span>
                                    </td>
                                    <td class="text-end font-monospace small">${{ number_format($s['cost_price'], 2) }}</td>
                                    <td class="text-end font-monospace small">${{ number_format($s['sold_price'], 2) }}</td>
                                    <td class="text-end font-monospace small fw-bold text-success">${{ number_format($s['gross_profit'], 2) }}</td>
                                    <td class="text-end font-monospace fw-bold {{ $s['margin_pct'] >= 15 ? 'text-success' : ($s['margin_pct'] >= 5 ? 'text-primary' : 'text-danger') }}">
                                        {{ $s['margin_pct'] }}%
                                    </td>
                                    <td class="text-center small text-muted">{{ $s['sold_at'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">No serialized sales found for selected date range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Tab 2: Aging Accounts Receivable (Net 30/60) -->
        @if($activeTab === 'ar-aging')
            <div class="panel p-4 mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Aging Accounts Receivable (A/R) for Net Terms</h5>
                        <small class="text-muted">Unpaid orders grouped into standard aging buckets with automated Credit Hold flags</small>
                    </div>
                    <div class="text-end">
                        <span class="fs-6 fw-bold font-monospace text-primary">${{ number_format($arAgingData['summary']['total_ar_outstanding'], 2) }} Total A/R</span>
                        <span class="badge bg-danger ms-2">{{ $arAgingData['summary']['credit_hold_accounts'] }} Credit Holds</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th>Company &amp; Terms</th>
                                <th class="text-center">Tier</th>
                                <th class="text-end">Credit Limit</th>
                                <th class="text-end">Total A/R</th>
                                <th class="text-end text-success">Current (Not Due)</th>
                                <th class="text-end text-primary">1-30 Days</th>
                                <th class="text-end text-warning-emphasis">31-60 Days</th>
                                <th class="text-end text-danger">61-90 Days</th>
                                <th class="text-end text-danger fw-bold">91+ Days</th>
                                <th class="text-center">Account Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($arAgingData['companies'] as $comp)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $comp['company_name'] }}</div>
                                        <div class="text-muted small">
                                            <span class="badge bg-light text-dark border me-1">{{ $comp['payment_terms'] }}</span>
                                            {{ $comp['customer_phone'] ?: $comp['customer_email'] }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary-subtle text-secondary">{{ $comp['tier'] }}</span>
                                    </td>
                                    <td class="text-end font-monospace small">${{ number_format($comp['credit_limit'], 0) }}</td>
                                    <td class="text-end font-monospace fw-bold">${{ number_format($comp['total_ar_balance'], 2) }}</td>
                                    <td class="text-end font-monospace small text-success">${{ number_format($comp['bucket_current'], 2) }}</td>
                                    <td class="text-end font-monospace small text-primary">${{ number_format($comp['bucket_1_30'], 2) }}</td>
                                    <td class="text-end font-monospace small text-warning-emphasis">${{ number_format($comp['bucket_31_60'], 2) }}</td>
                                    <td class="text-end font-monospace small text-danger">${{ number_format($comp['bucket_61_90'], 2) }}</td>
                                    <td class="text-end font-monospace small text-danger fw-bold">${{ number_format($comp['bucket_91_plus'], 2) }}</td>
                                    <td class="text-center">
                                        @if($comp['account_risk_status'] === 'CREDIT_HOLD')
                                            <span class="badge bg-danger text-white py-1 px-2">
                                                <i class="bi bi-slash-circle me-1"></i> CREDIT HOLD
                                            </span>
                                        @elseif($comp['account_risk_status'] === 'HIGH_RISK')
                                            <span class="badge bg-warning text-dark py-1 px-2">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i> HIGH RISK
                                            </span>
                                        @elseif($comp['account_risk_status'] === 'MODERATE_RISK')
                                            <span class="badge bg-info-subtle text-info border border-info-subtle py-1 px-2">
                                                MODERATE
                                            </span>
                                        @else
                                            <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2">
                                                <i class="bi bi-check2-circle me-1"></i> GOOD
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">No outstanding Accounts Receivable records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Tab 3: Stock Aging & Dead Inventory -->
        @if($activeTab === 'stock-aging')
            <div class="panel p-4 mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Stock Aging &amp; Dead Inventory (Depreciation Risk)</h5>
                        <small class="text-muted">Calculates shelf days for unsold warehouse serials to detect rapid component depreciation risk</small>
                    </div>
                    <div class="text-end">
                        <span class="fs-6 fw-bold font-monospace text-warning-emphasis">${{ number_format($stockAgingData['summary']['dead_capital_risk'], 2) }} Trapped Capital</span>
                        <span class="badge bg-warning text-dark ms-2">{{ $stockAgingData['summary']['dead_stock_ratio_pct'] }}% Dead Ratio</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th>Category &amp; SKU</th>
                                <th>Product Component</th>
                                <th class="text-center">On Hand</th>
                                <th class="text-end">Tied Capital</th>
                                <th class="text-center text-success">0-30d (Healthy)</th>
                                <th class="text-center text-primary">31-60d (Warning)</th>
                                <th class="text-center text-warning-emphasis">61-90d (Slow)</th>
                                <th class="text-center text-danger fw-bold">90d+ (Dead)</th>
                                <th class="text-end text-danger">Dead Capital Risk</th>
                                <th class="text-center">Action Required</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stockAgingData['products'] as $p)
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace">{{ $p['sku'] }}</span>
                                        <div class="text-muted small">{{ $p['category'] }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $p['product_name'] }}</div>
                                        <small class="text-muted">Brand: {{ $p['brand'] }}</small>
                                    </td>
                                    <td class="text-center font-monospace fw-bold">{{ $p['total_on_hand_units'] }}</td>
                                    <td class="text-end font-monospace small">${{ number_format($p['total_tied_capital'], 2) }}</td>
                                    <td class="text-center font-monospace text-success">{{ $p['units_0_30'] }}</td>
                                    <td class="text-center font-monospace text-primary">{{ $p['units_31_60'] }}</td>
                                    <td class="text-center font-monospace text-warning-emphasis">{{ $p['units_61_90'] }}</td>
                                    <td class="text-center font-monospace text-danger fw-bold">{{ $p['units_91_plus'] }}</td>
                                    <td class="text-end font-monospace text-danger fw-bold">${{ number_format($p['dead_capital_risk'], 2) }}</td>
                                    <td class="text-center">
                                        @if($p['action_status'] === 'DEAD_STOCK_CRITICAL')
                                            <span class="badge bg-danger text-white py-1 px-2">LIQUIDATE / PROMO</span>
                                        @elseif($p['action_status'] === 'SLOW_MOVING')
                                            <span class="badge bg-warning text-dark py-1 px-2">MARKDOWN REVIEW</span>
                                        @else
                                            <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2">HEALTHY</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">No inventory stock aging records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Tab 4: RFQ Conversion & Win-Rate Report -->
        @if($activeTab === 'rfq-winrate')
            <div class="panel p-4 mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Wholesale RFQ Pipeline &amp; Win-Rate Analysis</h5>
                        <small class="text-muted">Track quotation turnaround SLAs, conversion rates, and realized revenue across wholesale tiers</small>
                    </div>
                    <div class="text-end">
                        <span class="fs-6 fw-bold font-monospace text-success">${{ number_format($rfqData['summary']['total_realized_revenue'], 2) }} Realized</span>
                        <span class="badge bg-primary ms-2">{{ $rfqData['summary']['win_rate_pct'] }}% Overall Win Rate</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th>Customer Tier</th>
                                <th class="text-center">Submitted</th>
                                <th class="text-center">Quoted</th>
                                <th class="text-center text-success">Accepted</th>
                                <th class="text-center text-danger">Rejected</th>
                                <th class="text-center text-primary">Converted Orders</th>
                                <th class="text-center fw-bold">Win Rate %</th>
                                <th class="text-center">Avg SLA Turnaround</th>
                                <th class="text-end">Quoted Pipeline</th>
                                <th class="text-end text-success">Realized Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rfqData['by_tier'] as $t)
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $t['company_tier'] }}</span>
                                    </td>
                                    <td class="text-center font-monospace">{{ $t['total_rfqs_submitted'] }}</td>
                                    <td class="text-center font-monospace">{{ $t['rfqs_quoted'] }}</td>
                                    <td class="text-center font-monospace text-success fw-bold">{{ $t['rfqs_accepted'] }}</td>
                                    <td class="text-center font-monospace text-danger">{{ $t['rfqs_rejected'] }}</td>
                                    <td class="text-center font-monospace text-primary fw-bold">{{ $t['rfqs_converted'] }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $t['win_rate_pct'] >= 50 ? 'bg-success' : 'bg-warning text-dark' }} fs-6">
                                            {{ $t['win_rate_pct'] }}%
                                        </span>
                                    </td>
                                    <td class="text-center small font-monospace">{{ $t['avg_turnaround_hours'] }} Hours</td>
                                    <td class="text-end font-monospace small">${{ number_format($t['total_pipeline_value'], 2) }}</td>
                                    <td class="text-end font-monospace fw-bold text-success">${{ number_format($t['total_realized_revenue'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">No RFQ quote pipeline activity found for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Tab 5: Hardware RMA & Defect Rates by SKU/Brand -->
        @if($activeTab === 'rma-defects')
            <div class="panel p-4 mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Hardware RMA &amp; Defect Rate by SKU &amp; Brand</h5>
                        <small class="text-muted">Return rates grouped by brand and model to identify bad component batches and quality spikes</small>
                    </div>
                    <div class="text-end">
                        <span class="fs-6 fw-bold font-monospace text-danger">{{ $rmaData['summary']['total_returns'] }} Total Returns</span>
                        <span class="badge {{ $rmaData['summary']['overall_defect_rate_pct'] >= 3.0 ? 'bg-danger' : 'bg-success' }} ms-2">
                            {{ $rmaData['summary']['overall_defect_rate_pct'] }}% Overall Defect Rate
                        </span>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- By Brand Summary -->
                    <div class="col-12 col-xl-5">
                        <div class="border rounded p-3 bg-light h-100">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-shield-check text-primary me-2"></i>RMA Failure Rates by Manufacturer Brand</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="text-muted small">
                                        <tr>
                                            <th>Brand</th>
                                            <th class="text-center">Sold</th>
                                            <th class="text-center text-danger">Returns</th>
                                            <th class="text-end">RMA Rate %</th>
                                            <th class="text-center">Quality Flag</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rmaData['by_brand'] as $b)
                                            <tr>
                                                <td class="fw-semibold text-dark">{{ $b['brand'] }}</td>
                                                <td class="text-center font-monospace small">{{ $b['units_sold'] }}</td>
                                                <td class="text-center font-monospace small text-danger fw-bold">{{ $b['total_returns'] }}</td>
                                                <td class="text-end font-monospace fw-bold {{ $b['rma_defect_rate_pct'] >= 3 ? 'text-danger' : 'text-success' }}">
                                                    {{ $b['rma_defect_rate_pct'] }}%
                                                </td>
                                                <td class="text-center">
                                                    @if($b['quality_flag'] === 'CRITICAL_ACTION_REQUIRED')
                                                        <span class="badge bg-danger" style="font-size: 10px;">CRITICAL</span>
                                                    @elseif($b['quality_flag'] === 'ELEVATED_FAILURE_RATE')
                                                        <span class="badge bg-warning text-dark" style="font-size: 10px;">ELEVATED</span>
                                                    @else
                                                        <span class="badge bg-success-subtle text-success border" style="font-size: 10px;">NORMAL</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center text-muted py-3">No return data recorded.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- By SKU Component Breakdown -->
                    <div class="col-12 col-xl-7">
                        <div class="border rounded p-3 bg-light h-100">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-cpu-fill text-primary me-2"></i>Component Model RMA Breakdown</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="text-muted small">
                                        <tr>
                                            <th>SKU &amp; Product</th>
                                            <th class="text-center">Sold</th>
                                            <th class="text-center">RMA Pending</th>
                                            <th class="text-center">Scrap Defective</th>
                                            <th class="text-end">RMA Rate %</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rmaData['by_sku'] as $sku)
                                            <tr>
                                                <td>
                                                    <div class="fw-bold text-dark">{{ $sku['product_name'] }}</div>
                                                    <div class="text-muted small font-monospace">{{ $sku['sku'] }} &bull; {{ $sku['brand'] }}</div>
                                                </td>
                                                <td class="text-center font-monospace small">{{ $sku['units_sold'] }}</td>
                                                <td class="text-center font-monospace small text-warning-emphasis">{{ $sku['units_rma_pending'] }}</td>
                                                <td class="text-center font-monospace small text-danger">{{ $sku['units_defective'] }}</td>
                                                <td class="text-end font-monospace fw-bold {{ $sku['rma_defect_rate_pct'] >= 4 ? 'text-danger' : 'text-success' }}">
                                                    {{ $sku['rma_defect_rate_pct'] }}%
                                                </td>
                                                <td class="text-center">
                                                    @if($sku['quality_flag'] === 'CRITICAL_ACTION_REQUIRED')
                                                        <span class="badge bg-danger" style="font-size: 10px;">ACTION REQ</span>
                                                    @elseif($sku['quality_flag'] === 'ELEVATED_FAILURE_RATE')
                                                        <span class="badge bg-warning text-dark" style="font-size: 10px;">ELEVATED</span>
                                                    @else
                                                        <span class="badge bg-success-subtle text-success border" style="font-size: 10px;">PASS</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="text-center text-muted py-3">No SKU return records found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>

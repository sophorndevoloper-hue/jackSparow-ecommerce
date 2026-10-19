<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-file-earmark-text" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Manage Sale &bull; Wholesale RFQ Pipeline</p>
                    <h1 class="h3 mb-1">Quotes &amp; RFQs</h1>
                    <p class="text-muted mb-0">Review custom Bill of Materials (BOM) requests, configure custom margin pricing, and issue binding quotes.</p>
                </div>
            </div>
            <div class="heading-actions d-flex align-items-center gap-2">
                <a href="{{ route('admin.quotes.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> New RFQ / Custom BOM
                </a>
                <a href="{{ route('admin.pricing.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-calculator me-1"></i> Pricing Engine
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i> {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Summary KPI Cards -->
        <div class="row g-3 mt-1 mb-3">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Total RFQs</small>
                            <h3 class="mb-0 mt-1 fw-bold text-dark">{{ $totalQuotes }}</h3>
                            <small class="text-muted">Total requested quotes</small>
                        </div>
                        <div class="rounded-circle bg-primary-subtle text-primary p-3">
                            <i class="bi bi-files fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Pending Review</small>
                            <h3 class="mb-0 mt-1 fw-bold text-warning-emphasis">{{ $pendingReview }}</h3>
                            <small class="text-muted">Awaiting margin review</small>
                        </div>
                        <div class="rounded-circle bg-warning-subtle text-warning-emphasis p-3">
                            <i class="bi bi-hourglass-split fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Active Quotes Sent</small>
                            <h3 class="mb-0 mt-1 fw-bold text-info">{{ $quotedCount }}</h3>
                            <small class="text-muted">With clients for approval</small>
                        </div>
                        <div class="rounded-circle bg-info-subtle text-info p-3">
                            <i class="bi bi-send-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Converted to Orders</small>
                            <h3 class="mb-0 mt-1 fw-bold text-success">{{ $convertedCount }}</h3>
                            <small class="text-muted">Won &amp; converted</small>
                        </div>
                        <div class="rounded-circle bg-success-subtle text-success p-3">
                            <i class="bi bi-cart-check-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="panel p-3 mb-3">
            <form action="{{ route('admin.quotes.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by RFQ number, company name, client name, email..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="pending_review" {{ request('status') === 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                        <option value="quoted" {{ request('status') === 'quoted' ? 'selected' : '' }}>Quoted / Sent</option>
                        <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Accepted</option>
                        <option value="converted_to_order" {{ request('status') === 'converted_to_order' ? 'selected' : '' }}>Converted to Order</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Declined</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="tier" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Tiers</option>
                        <option value="Standard" {{ request('tier') === 'Standard' ? 'selected' : '' }}>Standard</option>
                        <option value="Gold" {{ request('tier') === 'Gold' ? 'selected' : '' }}>Gold Tier</option>
                        <option value="OEM" {{ request('tier') === 'OEM' ? 'selected' : '' }}>OEM Tier</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary btn-sm flex-fill">Filter</button>
                    @if(request()->hasAny(['search', 'status', 'tier']))
                        <a href="{{ route('admin.quotes.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- RFQs Table -->
        <div class="panel p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">RFQ / Quote #</th>
                            <th>Company &amp; Contact</th>
                            <th>Tier</th>
                            <th class="text-center">BOM Items</th>
                            <th>Quoted Total</th>
                            <th>Target Margin</th>
                            <th class="text-center">Status</th>
                            <th>Validity</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quotes as $quote)
                            <tr>
                                <td class="ps-3">
                                    <a href="{{ route('admin.quotes.show', $quote->id) }}" class="fw-bold text-decoration-none font-monospace">
                                        {{ $quote->quote_number }}
                                    </a>
                                    <small class="text-muted d-block">{{ $quote->created_at->format('M d, Y') }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $quote->company_name }}</div>
                                    <small class="text-muted">{{ $quote->client_name }} &bull; {{ $quote->client_phone ?: $quote->client_email }}</small>
                                </td>
                                <td>
                                    <span class="badge {{ $quote->tierBadgeClass() }}">{{ $quote->company_tier }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border font-monospace">
                                        {{ $quote->items->count() }} line(s)
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-dark fs-6">${{ number_format($quote->total_quoted_price, 2) }}</strong>
                                    <small class="text-muted d-block">Cost: ${{ number_format($quote->total_cost, 2) }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace">
                                        {{ $quote->target_margin_percent }}%
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $quote->statusBadgeClass() }} px-2 py-1">
                                        {{ $quote->statusLabel() }}
                                    </span>
                                </td>
                                <td>
                                    @if($quote->valid_until)
                                        <small class="font-monospace text-muted">{{ $quote->valid_until->format('M d, Y') }}</small>
                                    @else
                                        <small class="text-muted fst-italic">Draft / Unsent</small>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.quotes.show', $quote->id) }}" class="btn btn-primary" title="Review Quote & Margins">
                                            <i class="bi bi-eye me-1"></i> Review
                                        </a>
                                        @if($quote->converted_order_id)
                                            <a href="{{ route('admin.orders.show', $quote->converted_order_id) }}" class="btn btn-outline-success" title="View Converted Order">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-file-earmark-x display-4 d-block mb-2 text-secondary"></i>
                                    <h5>No quotes found</h5>
                                    <p class="mb-3">No wholesale requests for quotes match your search criteria.</p>
                                    <a href="{{ route('admin.quotes.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-circle me-1"></i> Create First RFQ
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($quotes->hasPages())
                <div class="p-3 border-top">
                    {{ $quotes->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>


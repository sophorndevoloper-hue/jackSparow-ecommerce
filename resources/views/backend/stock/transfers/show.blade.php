<x-app-layout>
    @php
        $totalUnits = $transfer->items->sum('quantity');
        $totalSerials = $transfer->items->reduce(function ($carry, $item) {
            return $carry + (is_array($item->serial_numbers) ? count($item->serial_numbers) : 0);
        }, 0);
    @endphp

    <style>
        /* Modern Logistics Manifest Styles - Screen View */
        :root {
            --manifest-border: #e2e8f0;
            --manifest-card-bg: #ffffff;
            --manifest-surface-subtle: #f8fafc;
            --manifest-node-bg: #ffffff;
            --manifest-text-main: #0f172a;
            --manifest-text-muted: #64748b;
            --manifest-accent: #2563eb;
            --manifest-serial-tag: #f1f5f9;
            --manifest-serial-border: #cbd5e1;
            --manifest-serial-text: #1e293b;
        }

        html[data-theme="dark"],
        html[data-bs-theme="dark"] {
            --manifest-border: #1e293b;
            --manifest-card-bg: #0f172a;
            --manifest-surface-subtle: #141f36;
            --manifest-node-bg: #101a30;
            --manifest-text-main: #f8fafc;
            --manifest-text-muted: #94a3b8;
            --manifest-accent: #38bdf8;
            --manifest-serial-tag: #1e293b;
            --manifest-serial-border: #334155;
            --manifest-serial-text: #e2e8f0;
        }

        .manifest-card {
            background-color: var(--manifest-card-bg);
            border: 1px solid var(--manifest-border);
            border-radius: 12px;
            box-shadow: 0 4px 16px -2px rgba(0, 0, 0, 0.05);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        /* Stat Metrics Cards */
        .stat-metric-card {
            background-color: var(--manifest-card-bg);
            border: 1px solid var(--manifest-border);
            border-radius: 12px;
            padding: 1.15rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
            overflow: hidden;
        }
        .stat-metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: currentColor;
            opacity: 0.75;
        }
        .stat-metric-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        /* Route Journey Visualizer */
        .route-journey-box {
            background: linear-gradient(135deg, var(--manifest-surface-subtle) 0%, var(--manifest-card-bg) 100%);
            border: 1px solid var(--manifest-border);
            border-radius: 12px;
            padding: 1.5rem;
            position: relative;
        }
        .route-node {
            background: var(--manifest-node-bg);
            border: 1px solid var(--manifest-border);
            border-radius: 10px;
            padding: 1rem 1.25rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }
        .route-node:hover {
            border-color: var(--manifest-accent);
        }
        .route-node-icon {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
        }

        /* Transit Pathway Line */
        .transit-path {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 0 1rem;
            min-height: 100px;
        }
        .transit-line {
            position: absolute;
            top: 50%;
            left: 10%;
            right: 10%;
            height: 3px;
            background: repeating-linear-gradient(90deg, #3b82f6 0, #3b82f6 8px, transparent 8px, transparent 14px);
            transform: translateY(-50%);
            z-index: 1;
            opacity: 0.6;
        }
        .transit-vehicle-badge {
            position: relative;
            z-index: 2;
            background: var(--manifest-card-bg);
            border: 2px solid #3b82f6;
            color: #3b82f6;
            border-radius: 50px;
            padding: 0.35rem 1rem;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
        }

        /* Pulse Animation */
        .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
            animation: pulse-glow 1.8s infinite;
        }
        @keyframes pulse-glow {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1.15); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        .pulse-dot-amber {
            animation-name: pulse-glow-amber;
        }
        @keyframes pulse-glow-amber {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7); }
            70% { transform: scale(1.15); box-shadow: 0 0 0 6px rgba(245, 158, 11, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
        }

        /* Hardware Component Table */
        .manifest-table-thead th {
            background-color: var(--manifest-surface-subtle) !important;
            color: var(--manifest-text-muted) !important;
            border-bottom: 1px solid var(--manifest-border) !important;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.85rem 1rem;
        }
        .manifest-row td {
            padding: 1.1rem 1rem;
            border-bottom: 1px solid var(--manifest-border);
            vertical-align: middle;
        }
        .manifest-product-avatar {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: var(--manifest-surface-subtle);
            border: 1px solid var(--manifest-border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            color: #3b82f6;
            flex-shrink: 0;
        }

        /* Serial Numbers Box */
        .serial-drawer {
            background-color: var(--manifest-surface-subtle);
            border: 1px solid var(--manifest-border);
            border-radius: 8px;
            padding: 0.75rem 0.9rem;
            margin-top: 0.75rem;
        }
        .serial-badge {
            background-color: var(--manifest-serial-tag);
            border: 1px solid var(--manifest-serial-border);
            color: var(--manifest-serial-text);
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.75rem;
            padding: 0.3rem 0.55rem;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: all 0.15s ease;
            cursor: pointer;
            user-select: all;
        }
        .serial-badge:hover {
            border-color: #3b82f6;
            color: #3b82f6;
            background-color: rgba(59, 130, 246, 0.1);
            transform: translateY(-1px);
        }

        /* Barcode Preview Box */
        .barcode-card {
            background: var(--manifest-surface-subtle);
            border: 1px dashed var(--manifest-border);
            border-radius: 10px;
            padding: 1.25rem;
            text-align: center;
        }
        .simulated-barcode {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            height: 40px;
            gap: 2px;
            margin-bottom: 0.35rem;
            opacity: 0.9;
        }
        .barcode-bar {
            background-color: currentColor;
            width: 2px;
            height: 100%;
        }
        .barcode-bar.w-1 { width: 1.5px; }
        .barcode-bar.w-2 { width: 3px; }
        .barcode-bar.w-3 { width: 4.5px; }
        .barcode-bar.h-sm { height: 75%; }

        /* Dark Mode Specific Fine-tuning */
        html[data-theme="dark"] .table,
        html[data-bs-theme="dark"] .table {
            --bs-table-bg: transparent;
            --bs-table-color: #e5edf7;
            --bs-table-border-color: #1e293b;
        }
        html[data-theme="dark"] .text-dark,
        html[data-bs-theme="dark"] .text-dark {
            color: #f8fafc !important;
        }

        /* ==========================================================
           RIGOROUS SINGLE-PAGE PRINT STYLES
           ========================================================== */
        @media print {
            @page {
                size: A4 portrait;
                margin: 6mm 8mm;
            }

            /* Completely remove layout chrome: topbar navbar, search, user menu, sidebar, footer */
            nav,
            header,
            footer,
            .navbar,
            .admin-navbar,
            .admin-sidebar,
            .admin-footer,
            .sidebar-backdrop,
            .sidebar-toggle,
            [data-sidebar-toggle],
            [data-sidebar-close],
            .search-input,
            .navbar-actions,
            .theme-toggle,
            .page-heading,
            .heading-actions,
            .btn,
            .no-print,
            .screen-view,
            .d-print-none {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                max-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                overflow: hidden !important;
            }

            /* Complete reset of all theme backgrounds to pure white for paper printing */
            html,
            html[data-theme="dark"],
            html[data-bs-theme="dark"],
            body,
            html[data-theme="dark"] body,
            html[data-bs-theme="dark"] body,
            .admin-shell,
            .admin-main,
            .dashboard-content,
            .container-fluid,
            main {
                background-color: #ffffff !important;
                background: #ffffff !important;
                color: #111827 !important;
                color-scheme: light !important;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif !important;
                font-size: 11px !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                height: auto !important;
                min-height: 0 !important;
                border: none !important;
                box-shadow: none !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Dedicated Print Manifest Styles - Guarantee 1 single page */
            .print-manifest-wrapper {
                display: block !important;
                background-color: #ffffff !important;
                color: #111827 !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 4px 6px !important;
                page-break-before: avoid !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
                break-before: avoid !important;
                break-after: avoid !important;
                break-inside: avoid !important;
            }

            .print-manifest-wrapper * {
                color: #111827 !important;
                box-sizing: border-box !important;
            }

            .print-table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            .print-table th,
            .print-table td {
                border: 1.5px solid #1f2937 !important;
                padding: 8px 10px !important;
                font-size: 11px !important;
                vertical-align: middle !important;
            }
            .print-table th {
                background-color: #e5e7eb !important;
                color: #000000 !important;
                font-weight: 800 !important;
                text-transform: uppercase !important;
                font-size: 10px !important;
                letter-spacing: 0.04em !important;
            }

            .print-serial-chip {
                display: inline-block !important;
                border: 1px solid #374151 !important;
                background-color: #f3f4f6 !important;
                color: #000000 !important;
                padding: 2px 6px !important;
                margin: 2px !important;
                border-radius: 4px !important;
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
                font-size: 10px !important;
                font-weight: 600 !important;
            }

            .print-route-card {
                border: 1.5px solid #1f2937 !important;
                border-radius: 6px !important;
                padding: 10px 14px !important;
                background-color: #f9fafb !important;
            }

            .print-signatures-box {
                border: 1.5px solid #1f2937 !important;
                border-radius: 6px !important;
                padding: 12px 14px !important;
                background-color: #ffffff !important;
            }
        }
    </style>

    <!-- ============================================================== -->
    <!-- 1. INTERACTIVE SCREEN VIEW (Hidden completely when printing)    -->
    <!-- ============================================================== -->
    <div class="screen-view d-print-none container-fluid px-3 px-lg-4 py-4">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb mb-0 py-1" style="font-size: 0.85rem;">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.stock.transfers.index') }}" class="text-decoration-none text-muted">
                        <i class="bi bi-arrow-left-right me-1"></i> Stock Transfers
                    </a>
                </li>
                <li class="breadcrumb-item active fw-semibold text-primary" aria-current="page">
                    {{ $transfer->reference_number }}
                </li>
            </ol>
        </nav>

        <!-- Page Heading Banner -->
        <div class="page-heading mb-4">
            <div class="page-heading-copy d-flex align-items-center gap-3">
                <div class="page-icon" style="width: 52px; height: 52px; border-radius: 12px; background: rgba(59, 130, 246, 0.12); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 1.6rem;">
                    <i class="bi bi-truck-flatbed" aria-hidden="true"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 0.72rem; letter-spacing: 0.05em; font-weight: 700;">
                            LOGISTICS MANIFEST
                        </span>
                        @if($transfer->status === 'completed')
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                <span class="pulse-dot bg-success"></span> Completed &amp; Stock Received
                            </span>
                        @elseif($transfer->status === 'in_transit')
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">
                                <span class="pulse-dot pulse-dot-amber bg-warning"></span> In Transit (En Route)
                            </span>
                        @elseif($transfer->status === 'pending')
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                <i class="bi bi-hourglass-split me-1"></i> Pending Dispatch
                            </span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                <i class="bi bi-x-circle me-1"></i> Cancelled
                            </span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <h1 class="h3 mb-0 fw-bold font-monospace text-body-emphasis">{{ $transfer->reference_number }}</h1>
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 rounded" onclick="copyText('{{ $transfer->reference_number }}', this)" title="Copy Reference">
                            <i class="bi bi-clipboard me-1"></i><span class="small copy-label">Copy</span>
                        </button>
                    </div>
                    <p class="text-muted small mb-0 mt-1">
                        <i class="bi bi-person-circle me-1"></i> Initiated by <strong class="text-body">{{ $transfer->user?->name ?? 'System' }}</strong>
                        &bull; <i class="bi bi-clock-history ms-1 me-1"></i> {{ $transfer->created_at->format('M d, Y \a\t H:i') }}
                    </p>
                </div>
            </div>

            <div class="heading-actions d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm px-3" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print Manifest
                </button>
                @canany(['create products', 'create warehouses', 'edit products'])
                    <a href="{{ route('admin.stock.transfers.create') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                        <i class="bi bi-plus-circle"></i> New Transfer
                    </a>
                @endcanany
                <a href="{{ route('admin.stock.transfers.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i> All Transfers
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                    <div>{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                    <div>{{ session('error') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- 4 Stat Metric Cards -->
        <div class="row g-3 mb-4">
            <!-- Metric 1: Status -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-metric-card text-primary" style="color: #3b82f6;">
                    <div class="stat-metric-icon bg-primary-subtle text-primary">
                        @if($transfer->status === 'completed')
                            <i class="bi bi-check-circle-fill text-success"></i>
                        @elseif($transfer->status === 'in_transit')
                            <i class="bi bi-truck text-warning"></i>
                        @elseif($transfer->status === 'pending')
                            <i class="bi bi-hourglass-split text-info"></i>
                        @else
                            <i class="bi bi-x-circle-fill text-danger"></i>
                        @endif
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Manifest Status</div>
                        <div class="fs-5 fw-bold text-body-emphasis mt-0">
                            {{ ucfirst(str_replace('_', ' ', $transfer->status)) }}
                        </div>
                        <div class="text-muted small" style="font-size: 0.78rem;">
                            @if($transfer->status === 'completed')
                                Inventory securely checked-in
                            @elseif($transfer->status === 'in_transit')
                                En route to destination
                            @elseif($transfer->status === 'pending')
                                Prepared &amp; awaiting dispatch
                            @else
                                Transfer cancelled
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metric 2: Total Units -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-metric-card text-success" style="color: #10b981;">
                    <div class="stat-metric-icon bg-success-subtle text-success">
                        <i class="bi bi-boxes"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Total Transfer Quantity</div>
                        <div class="fs-5 fw-bold text-body-emphasis mt-0 font-monospace">
                            {{ $totalUnits }} <span class="fs-6 fw-normal text-muted">units</span>
                        </div>
                        <div class="text-muted small" style="font-size: 0.78rem;">
                            Across {{ $transfer->items->count() }} unique {{ Str::plural('line item', $transfer->items->count()) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metric 3: Serial Numbers -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-metric-card text-info" style="color: #06b6d4;">
                    <div class="stat-metric-icon bg-info-subtle text-info">
                        <i class="bi bi-upc-scan"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Tracked Serial Numbers</div>
                        <div class="fs-5 fw-bold text-body-emphasis mt-0 font-monospace">
                            {{ $totalSerials }} <span class="fs-6 fw-normal text-muted">serials</span>
                        </div>
                        <div class="text-muted small" style="font-size: 0.78rem;">
                            @if($totalSerials > 0)
                                Individual barcodes matched
                            @else
                                Non-serialized hardware units
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metric 4: Scheduled Transfer Date -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-metric-card text-purple" style="color: #8b5cf6;">
                    <div class="stat-metric-icon bg-body-secondary text-primary">
                        <i class="bi bi-calendar2-check"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Dispatched Date</div>
                        <div class="fs-5 fw-bold text-body-emphasis mt-0">
                            {{ $transfer->transfer_date ? $transfer->transfer_date->format('M d, Y') : $transfer->created_at->format('M d, Y') }}
                        </div>
                        <div class="text-muted small" style="font-size: 0.78rem;">
                            {{ $transfer->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logistics Route Journey Visualizer (Hero Card) -->
        <div class="route-journey-box mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-signpost-split text-primary fs-5"></i>
                    <h6 class="mb-0 fw-bold text-body-emphasis">Logistics Route &amp; Transit Journey</h6>
                </div>
                <div class="text-muted small font-monospace">
                    Ref ID: <strong class="text-body">{{ $transfer->reference_number }}</strong>
                </div>
            </div>

            <div class="row align-items-center g-3">
                <!-- Origin Hub Node -->
                <div class="col-12 col-md-5">
                    <div class="route-node">
                        <div class="d-flex align-items-start gap-3">
                            <div class="route-node-icon bg-primary-subtle text-primary">
                                <i class="bi bi-building-up"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="badge bg-secondary-subtle text-muted text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                                        Origin / Source Hub
                                    </span>
                                    <span class="badge bg-primary text-white font-monospace" style="font-size: 0.75rem;">
                                        {{ $transfer->fromWarehouse?->code ?? 'N/A' }}
                                    </span>
                                </div>
                                <h5 class="fw-bold text-body-emphasis mt-1 mb-1">
                                    {{ $transfer->fromWarehouse?->name ?? 'Unknown Origin' }}
                                </h5>
                                <div class="text-muted small">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    {{ $transfer->fromWarehouse?->city ?? $transfer->fromWarehouse?->address ?? 'Central Logistics Facility' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transit Pathway Track -->
                <div class="col-12 col-md-2">
                    <div class="transit-path">
                        <div class="transit-line d-none d-md-block"></div>
                        <div class="transit-vehicle-badge">
                            @if($transfer->status === 'completed')
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span class="text-success">Delivered</span>
                            @elseif($transfer->status === 'in_transit')
                                <i class="bi bi-truck text-warning"></i>
                                <span class="text-warning">In Transit</span>
                            @elseif($transfer->status === 'pending')
                                <i class="bi bi-clock-history text-info"></i>
                                <span class="text-info">Ready</span>
                            @else
                                <i class="bi bi-x-circle text-danger"></i>
                                <span class="text-danger">Cancelled</span>
                            @endif
                        </div>
                        <div class="text-center mt-2 small text-muted d-none d-md-block" style="font-size: 11px;">
                            Direct Transit
                        </div>
                    </div>
                </div>

                <!-- Destination Hub Node -->
                <div class="col-12 col-md-5">
                    <div class="route-node">
                        <div class="d-flex align-items-start gap-3">
                            <div class="route-node-icon bg-success-subtle text-success">
                                <i class="bi bi-building-check"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="badge bg-secondary-subtle text-muted text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                                        Destination / Target Hub
                                    </span>
                                    <span class="badge bg-success text-white font-monospace" style="font-size: 0.75rem;">
                                        {{ $transfer->toWarehouse?->code ?? 'N/A' }}
                                    </span>
                                </div>
                                <h5 class="fw-bold text-body-emphasis mt-1 mb-1">
                                    {{ $transfer->toWarehouse?->name ?? 'Unknown Destination' }}
                                </h5>
                                <div class="text-muted small">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    {{ $transfer->toWarehouse?->city ?? $transfer->toWarehouse?->address ?? 'Receiving Logistics Depot' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transit Quick Action Bar -->
            @canany(['create products', 'create warehouses', 'edit products'])
                @if($transfer->status === 'pending')
                    <div class="mt-3 pt-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="small text-muted">
                            <i class="bi bi-info-circle me-1 text-primary"></i> This transfer has been created and is awaiting transit dispatch.
                        </div>
                        <div class="d-flex gap-2">
                            <form action="{{ route('admin.stock.transfers.status', $transfer->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="in_transit">
                                <button type="submit" class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1 shadow-sm fw-medium">
                                    <i class="bi bi-truck"></i> Mark In Transit
                                </button>
                            </form>
                            <form action="{{ route('admin.stock.transfers.status', $transfer->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1 shadow-sm fw-medium">
                                    <i class="bi bi-check2-circle"></i> Direct Receive (Complete)
                                </button>
                            </form>
                        </div>
                    </div>
                @elseif($transfer->status === 'in_transit')
                    <div class="mt-3 pt-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="small text-muted">
                            <i class="bi bi-truck me-1 text-warning"></i> Shipment is currently in transit to <strong>{{ $transfer->toWarehouse?->name }}</strong>.
                        </div>
                        <div class="d-flex gap-2">
                            <form action="{{ route('admin.stock.transfers.status', $transfer->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1 shadow-sm fw-medium">
                                    <i class="bi bi-check2-circle"></i> Confirm Delivery &amp; Receive Stock
                                </button>
                            </form>
                            <form action="{{ route('admin.stock.transfers.status', $transfer->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Cancel transfer and return items to source warehouse?')">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Cancel Transfer
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            @endcanany
        </div>

        <!-- Main Manifest Details Row -->
        <div class="row g-4">
            <!-- Left Side: Transferred Hardware Components (8 Cols) -->
            <div class="col-12 col-lg-8">
                <div class="manifest-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="p-2 rounded bg-primary-subtle text-primary">
                                <i class="bi bi-cpu fs-5"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold text-body-emphasis">Transferred Components</h5>
                                <div class="text-muted small">{{ $transfer->items->count() }} item {{ Str::plural('entry', $transfer->items->count()) }} in this shipment</div>
                            </div>
                        </div>

                        @if($totalSerials > 0)
                            <button type="button" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 rounded-pill" onclick="copyAllSerials()">
                                <i class="bi bi-clipboard-check"></i> <span id="copyAllLabel">Copy All Serials ({{ $totalSerials }})</span>
                            </button>
                        @endif
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="manifest-table-thead">
                                <tr>
                                    <th style="min-width: 260px;">Hardware Part &amp; Tracking</th>
                                    <th class="text-center" style="width: 140px;">Category</th>
                                    <th class="text-end" style="width: 130px;">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transfer->items as $item)
                                    <tr class="manifest-row">
                                        <td>
                                            <div class="d-flex align-items-start gap-3">
                                                <div class="manifest-product-avatar">
                                                    @php
                                                        $catName = strtolower($item->product?->category?->name ?? '');
                                                    @endphp
                                                    @if(str_contains($catName, 'cpu') || str_contains($catName, 'processor'))
                                                        <i class="bi bi-cpu"></i>
                                                    @elseif(str_contains($catName, 'gpu') || str_contains($catName, 'graphic'))
                                                        <i class="bi bi-gpu-card"></i>
                                                    @elseif(str_contains($catName, 'ram') || str_contains($catName, 'memory'))
                                                        <i class="bi bi-memory"></i>
                                                    @elseif(str_contains($catName, 'motherboard'))
                                                        <i class="bi bi-motherboard"></i>
                                                    @else
                                                        <i class="bi bi-box-seam"></i>
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1">
                                                    <a href="{{ route('admin.products.edit', $item->product_id) }}" class="fw-bold text-body-emphasis text-decoration-none hover-primary">
                                                        {{ $item->product?->name }}
                                                    </a>
                                                    <div class="d-flex align-items-center gap-2 mt-1">
                                                        <span class="badge bg-secondary-subtle text-muted font-monospace" style="font-size: 0.72rem;">
                                                            SKU: {{ $item->product?->sku ?? 'N/A' }}
                                                        </span>
                                                        <button type="button" class="btn btn-link text-muted p-0" onclick="copyText('{{ $item->product?->sku }}', this)" title="Copy SKU" style="font-size: 11px;">
                                                            <i class="bi bi-copy"></i>
                                                        </button>
                                                    </div>

                                                    <!-- Serial Numbers Drawer -->
                                                    @if(!empty($item->serial_numbers) && is_array($item->serial_numbers))
                                                        <div class="serial-drawer">
                                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                                <span class="small fw-semibold text-body-emphasis" style="font-size: 0.75rem;">
                                                                    <i class="bi bi-upc-scan me-1 text-primary"></i> Tracked Serials ({{ count($item->serial_numbers) }}):
                                                                </span>
                                                                <button type="button" class="btn btn-link text-primary p-0 text-decoration-none" style="font-size: 0.72rem;" onclick="copyItemSerials({{ json_encode($item->serial_numbers) }}, this)">
                                                                    <i class="bi bi-clipboard me-1"></i>Copy line serials
                                                                </button>
                                                            </div>
                                                            <div class="d-flex flex-wrap gap-1">
                                                                @foreach($item->serial_numbers as $sn)
                                                                    <span class="serial-badge individual-serial-tag" onclick="copyText('{{ $sn }}', this)" title="Click to copy serial">
                                                                        <i class="bi bi-upc text-muted" style="font-size: 10px;"></i> {{ $sn }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary-subtle text-body-secondary border px-2 py-1">
                                                {{ $item->product?->category?->name ?? 'Hardware' }}
                                            </span>
                                        </td>
                                        <td class="text-end font-monospace">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fs-6 fw-bold">
                                                {{ $item->quantity }} <span class="fw-normal small" style="font-size: 0.75rem;">units</span>
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="manifest-table-thead">
                                    <th colspan="2" class="text-end text-uppercase">Total Manifest Quantity:</th>
                                    <th class="text-end font-monospace fs-6 text-primary fw-bold">{{ $totalUnits }} units</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Side: Logistics Waybill & Courier Details (4 Cols) -->
            <div class="col-12 col-lg-4">
                <div class="d-flex flex-column gap-3">
                    <!-- Barcode Waybill Card -->
                    <div class="manifest-card p-4">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <i class="bi bi-upc text-primary fs-5"></i>
                            <h6 class="mb-0 fw-bold text-body-emphasis">Electronic Waybill &amp; Scan</h6>
                        </div>

                        <div class="barcode-card mb-3 text-body-emphasis">
                            <div class="simulated-barcode">
                                <span class="barcode-bar w-2"></span>
                                <span class="barcode-bar w-1"></span>
                                <span class="barcode-bar w-3"></span>
                                <span class="barcode-bar w-1 h-sm"></span>
                                <span class="barcode-bar w-2"></span>
                                <span class="barcode-bar w-3"></span>
                                <span class="barcode-bar w-1"></span>
                                <span class="barcode-bar w-2 h-sm"></span>
                                <span class="barcode-bar w-1"></span>
                                <span class="barcode-bar w-3"></span>
                                <span class="barcode-bar w-2"></span>
                                <span class="barcode-bar w-1"></span>
                                <span class="barcode-bar w-2 h-sm"></span>
                                <span class="barcode-bar w-3"></span>
                                <span class="barcode-bar w-1"></span>
                                <span class="barcode-bar w-2"></span>
                            </div>
                            <div class="font-monospace fw-bold small text-body-emphasis tracking-wider">
                                *{{ $transfer->reference_number }}*
                            </div>
                            <div class="text-muted" style="font-size: 10px;">
                                SCAN VIA BARCODE SCANNER OR ENTER CODE
                            </div>
                        </div>

                        <ul class="list-unstyled space-y-2 mb-0" style="font-size: 0.85rem;">
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Waybill Ref:</span>
                                <strong class="font-monospace text-body-emphasis">{{ $transfer->reference_number }}</strong>
                            </li>
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Initiated By:</span>
                                <span class="text-body-emphasis fw-medium">{{ $transfer->user?->name ?? 'System Admin' }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Scheduled Date:</span>
                                <span class="text-body-emphasis">{{ $transfer->transfer_date ? $transfer->transfer_date->format('M d, Y') : 'Immediate' }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Dispatched At:</span>
                                <span class="text-body-emphasis">{{ $transfer->created_at->format('M d, Y H:i') }}</span>
                            </li>
                            <li class="d-flex justify-content-between pt-2">
                                <span class="text-muted">Last Updated:</span>
                                <span class="text-body-emphasis">{{ $transfer->updated_at->format('M d, Y H:i') }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Dispatch & Courier Notes -->
                    <div class="manifest-card p-4">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <i class="bi bi-chat-left-quote text-primary fs-5"></i>
                            <h6 class="mb-0 fw-bold text-body-emphasis">Courier &amp; Handling Notes</h6>
                        </div>

                        @if($transfer->notes)
                            <div class="p-3 rounded bg-body-tertiary border text-body-emphasis small lh-base" style="font-family: inherit;">
                                <i class="bi bi-quote fs-5 text-muted me-1"></i>{{ $transfer->notes }}
                            </div>
                        @else
                            <div class="text-center py-3 text-muted small">
                                <i class="bi bi-journal-text fs-4 d-block mb-1 text-muted opacity-50"></i>
                                No specific courier or delivery notes recorded.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- ============================================================== -->
    <!-- 2. OFFICIAL PRINT MANIFEST (Visible ONLY when printing)        -->
    <!-- Strictly designed to fit on EXACTLY 1 SINGLE PAGE              -->
    <!-- ============================================================== -->
    <div class="print-manifest-wrapper d-none d-print-block">
        <!-- Official Company & Waybill Header -->
        <div style="border-bottom: 2.5px solid #000000; padding-bottom: 10px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <h1 style="font-size: 20px; font-weight: 900; margin: 0; text-transform: uppercase; letter-spacing: 0.04em; color: #000000;">
                    {{ config('app.name', 'JackSparow TECH') }}
                </h1>
                <div style="font-size: 11.5px; font-weight: 700; color: #1f2937; letter-spacing: 0.02em;">
                    OFFICIAL INTER-WAREHOUSE STOCK DISPATCH MANIFEST
                </div>
            </div>
            <div style="text-align: right;">
                <div style="font-family: ui-monospace, monospace; font-size: 15px; font-weight: 900; color: #000000;">
                    WAYBILL REF: {{ $transfer->reference_number }}
                </div>
                <div style="font-size: 10px; color: #111827; font-weight: 600;">
                    Dispatched: {{ $transfer->created_at->format('Y-m-d H:i') }} &bull; Status: <strong style="text-transform: uppercase; color: #000000;">{{ str_replace('_', ' ', $transfer->status) }}</strong>
                </div>
            </div>
        </div>

        <!-- Logistics Origin & Destination Route Box (Side by Side) -->
        <div style="display: flex; gap: 12px; margin-bottom: 12px; align-items: stretch;">
            <div style="flex: 1; border: 1.5px solid #1f2937; border-radius: 6px; padding: 8px 12px; background-color: #f9fafb;">
                <div style="font-size: 9px; text-transform: uppercase; font-weight: 800; color: #374151; letter-spacing: 0.05em;">
                    DISPATCH FACILITY (ORIGIN HUB)
                </div>
                <div style="font-size: 13px; font-weight: 900; color: #000000; margin-top: 2px;">
                    {{ $transfer->fromWarehouse?->name }} ({{ $transfer->fromWarehouse?->code }})
                </div>
                <div style="font-size: 10px; color: #1f2937; margin-top: 2px;">
                    Location: {{ $transfer->fromWarehouse?->city ?? $transfer->fromWarehouse?->address ?? 'Primary Logistics Facility' }}
                </div>
            </div>

            <div style="width: 80px; display: flex; flex-direction: column; align-items: center; justify-content: center; font-weight: 900; font-size: 16px; color: #000000;">
                <span style="font-size: 20px; line-height: 1;">&rarr;</span>
                <span style="font-size: 8.5px; text-transform: uppercase; letter-spacing: 0.06em; font-weight: 800;">IN TRANSIT</span>
            </div>

            <div style="flex: 1; border: 1.5px solid #1f2937; border-radius: 6px; padding: 8px 12px; background-color: #f9fafb;">
                <div style="font-size: 9px; text-transform: uppercase; font-weight: 800; color: #374151; letter-spacing: 0.05em;">
                    RECEIVING FACILITY (DESTINATION HUB)
                </div>
                <div style="font-size: 13px; font-weight: 900; color: #000000; margin-top: 2px;">
                    {{ $transfer->toWarehouse?->name }} ({{ $transfer->toWarehouse?->code }})
                </div>
                <div style="font-size: 10px; color: #1f2937; margin-top: 2px;">
                    Location: {{ $transfer->toWarehouse?->city ?? $transfer->toWarehouse?->address ?? 'Receiving Logistics Depot' }}
                </div>
            </div>
        </div>

        <!-- Transfer Metadata Summary Strip -->
        <div style="display: flex; justify-content: space-between; background-color: #f3f4f6; border: 1.5px solid #1f2937; padding: 6px 12px; font-size: 10.5px; margin-bottom: 12px; border-radius: 6px; font-weight: 600;">
            <div><strong>Initiator / Operator:</strong> {{ $transfer->user?->name ?? 'System Admin' }}</div>
            <div><strong>Transfer Date:</strong> {{ $transfer->transfer_date ? $transfer->transfer_date->format('M d, Y') : 'Immediate' }}</div>
            <div><strong>Line Items:</strong> {{ $transfer->items->count() }}</div>
            <div><strong>Total Units:</strong> {{ $totalUnits }} Units</div>
            <div><strong>Tracked Serials:</strong> {{ $totalSerials }} Serials</div>
        </div>

        <!-- Transferred Components & Hardware Items Table -->
        <table class="print-table" style="margin-bottom: 12px;">
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">#</th>
                    <th style="width: 40%;">Hardware Description &amp; SKU</th>
                    <th style="width: 15%;">Category</th>
                    <th style="width: 28%;">Tracked Serial Numbers</th>
                    <th style="width: 12%; text-align: right;">Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transfer->items as $idx => $item)
                    <tr>
                        <td style="text-align: center; font-weight: 800; color: #000000;">{{ $idx + 1 }}</td>
                        <td>
                            <strong style="font-size: 12px; color: #000000;">{{ $item->product?->name }}</strong>
                            <div style="font-family: ui-monospace, monospace; font-size: 10px; color: #1f2937; font-weight: 700; margin-top: 2px;">
                                SKU: {{ $item->product?->sku ?? 'N/A' }}
                            </div>
                        </td>
                        <td style="font-size: 10.5px; font-weight: 600; color: #111827;">
                            {{ $item->product?->category?->name ?? '-' }}
                        </td>
                        <td>
                            @if(!empty($item->serial_numbers) && is_array($item->serial_numbers))
                                <div style="display: flex; flex-wrap: wrap; gap: 3px;">
                                    @foreach($item->serial_numbers as $sn)
                                        <span class="print-serial-chip">{{ $sn }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span style="color: #4b5563; font-size: 10px; font-style: italic;">Non-serialized bulk stock</span>
                            @endif
                        </td>
                        <td style="text-align: right; font-family: ui-monospace, monospace; font-weight: 900; font-size: 12px; color: #000000;">
                            {{ $item->quantity }} units
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #e5e7eb;">
                    <th colspan="4" style="text-align: right; text-transform: uppercase; font-size: 11px;">Total Manifest Units:</th>
                    <th style="text-align: right; font-family: ui-monospace, monospace; font-size: 12.5px; font-weight: 900; color: #000000;">{{ $totalUnits }} units</th>
                </tr>
            </tfoot>
        </table>

        <!-- Notes if present -->
        @if($transfer->notes)
            <div style="border: 1.5px solid #1f2937; background-color: #f9fafb; padding: 8px 12px; border-radius: 6px; font-size: 10px; margin-bottom: 14px;">
                <strong style="color: #000000;">Courier &amp; Handling Notes:</strong>
                <span style="color: #111827;">{{ $transfer->notes }}</span>
            </div>
        @endif

        <!-- Signatures & Formal Confirmation Block (Bottom of 1 Page) -->
        <div style="display: flex; gap: 14px; margin-top: 14px;">
            <div style="flex: 1; border: 1.5px solid #000000; border-radius: 6px; padding: 10px 14px; background-color: #ffffff;">
                <div style="font-weight: 800; font-size: 11px; text-transform: uppercase; margin-bottom: 3px; color: #000000;">
                    1. Origin Dispatch Verification
                </div>
                <div style="font-size: 9.5px; color: #1f2937; margin-bottom: 30px;">
                    I certify that all listed hardware units and serial numbers were physically inspected and dispatched.
                </div>
                <div style="display: flex; justify-content: space-between; border-top: 1.5px solid #000000; padding-top: 6px; font-size: 9.5px; color: #000000; font-weight: 600;">
                    <span>Signature: ______________________</span>
                    <span>Date: ____________</span>
                </div>
            </div>

            <div style="flex: 1; border: 1.5px solid #000000; border-radius: 6px; padding: 10px 14px; background-color: #ffffff;">
                <div style="font-weight: 800; font-size: 11px; text-transform: uppercase; margin-bottom: 3px; color: #000000;">
                    2. Destination Receipt Verification
                </div>
                <div style="font-size: 9.5px; color: #1f2937; margin-bottom: 30px;">
                    I certify that all transferred items and barcodes were received in good order and stocked.
                </div>
                <div style="display: flex; justify-content: space-between; border-top: 1.5px solid #000000; padding-top: 6px; font-size: 9.5px; color: #000000; font-weight: 600;">
                    <span>Signature: ______________________</span>
                    <span>Date: ____________</span>
                </div>
            </div>
        </div>

        <!-- Footer Verification Code -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px; font-size: 8.5px; color: #374151; font-family: ui-monospace, monospace; border-top: 1px solid #9ca3af; padding-top: 4px;">
            <span>E-MANIFEST AUTH CODE: {{ md5($transfer->reference_number . $transfer->created_at) }}</span>
            <span>Page 1 of 1 &bull; Generated: {{ date('Y-m-d H:i') }}</span>
        </div>
    </div>

    <!-- Interactive Copy Tools Script (For Screen Mode) -->
    <script>
        function copyText(text, element) {
            if (!text) return;
            navigator.clipboard.writeText(text).then(function () {
                var originalHtml = element.innerHTML;
                element.innerHTML = '<i class="bi bi-check-lg text-success"></i> <span class="small text-success">Copied!</span>';
                setTimeout(function () {
                    element.innerHTML = originalHtml;
                }, 1500);
            }).catch(function (err) {
                console.error('Failed to copy: ', err);
            });
        }

        function copyItemSerials(serials, element) {
            if (!serials || !serials.length) return;
            var text = serials.join('\n');
            navigator.clipboard.writeText(text).then(function () {
                var originalHtml = element.innerHTML;
                element.innerHTML = '<i class="bi bi-check-lg text-success me-1"></i>Copied!';
                setTimeout(function () {
                    element.innerHTML = originalHtml;
                }, 1500);
            });
        }

        function copyAllSerials() {
            var allElements = document.querySelectorAll('.individual-serial-tag');
            var serials = [];
            allElements.forEach(function (el) {
                var text = el.innerText.trim();
                if (text) serials.push(text);
            });

            if (serials.length === 0) return;

            navigator.clipboard.writeText(serials.join('\n')).then(function () {
                var label = document.getElementById('copyAllLabel');
                if (label) {
                    var originalText = label.innerText;
                    label.innerText = 'Copied ' + serials.length + ' Serials!';
                    label.classList.add('text-success');
                    setTimeout(function () {
                        label.innerText = originalText;
                        label.classList.remove('text-success');
                    }, 2000);
                }
            });
        }
    </script>
</x-app-layout>

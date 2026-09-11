<x-app-layout>
    @php
        $totalItems = $adjustment->items->count();
        $totalNetDelta = $adjustment->items->sum('adjusted_quantity');
        $totalOldQty = $adjustment->items->sum('old_quantity');
        $totalNewQty = $adjustment->items->sum('new_quantity');
        $totalSerials = $adjustment->items->reduce(function ($carry, $item) {
            return $carry + (is_array($item->serial_numbers) ? count($item->serial_numbers) : 0);
        }, 0);
    @endphp

    <style>
        /* Modern Inventory Audit Record Styles - Screen View */
        :root {
            --audit-border: #e2e8f0;
            --audit-card-bg: #ffffff;
            --audit-surface-subtle: #f8fafc;
            --audit-node-bg: #ffffff;
            --audit-text-main: #0f172a;
            --audit-text-muted: #64748b;
            --audit-accent: #2563eb;
            --audit-serial-tag: #f1f5f9;
            --audit-serial-border: #cbd5e1;
            --audit-serial-text: #1e293b;
        }

        html[data-theme="dark"],
        html[data-bs-theme="dark"] {
            --audit-border: #1e293b;
            --audit-card-bg: #0f172a;
            --audit-surface-subtle: #141f36;
            --audit-node-bg: #101a30;
            --audit-text-main: #f8fafc;
            --audit-text-muted: #94a3b8;
            --audit-accent: #38bdf8;
            --audit-serial-tag: #1e293b;
            --audit-serial-border: #334155;
            --audit-serial-text: #e2e8f0;
        }

        .audit-card {
            background-color: var(--audit-card-bg);
            border: 1px solid var(--audit-border);
            border-radius: 12px;
            box-shadow: 0 4px 16px -2px rgba(0, 0, 0, 0.05);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        /* Stat Metrics Cards */
        .stat-metric-card {
            background-color: var(--audit-card-bg);
            border: 1px solid var(--audit-border);
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
        .pulse-dot-red {
            animation-name: pulse-glow-red;
        }
        @keyframes pulse-glow-red {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { transform: scale(1.15); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        /* Component Table & Rows */
        .audit-table-thead th {
            background-color: var(--audit-surface-subtle) !important;
            color: var(--audit-text-muted) !important;
            border-bottom: 1px solid var(--audit-border) !important;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.85rem 1rem;
        }
        .audit-row td {
            padding: 1.1rem 1rem;
            border-bottom: 1px solid var(--audit-border);
            vertical-align: middle;
        }
        .audit-product-avatar {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: var(--audit-surface-subtle);
            border: 1px solid var(--audit-border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            color: #3b82f6;
            flex-shrink: 0;
        }

        /* Serial Numbers Box */
        .serial-drawer {
            background-color: var(--audit-surface-subtle);
            border: 1px solid var(--audit-border);
            border-radius: 8px;
            padding: 0.75rem 0.9rem;
            margin-top: 0.75rem;
        }
        .serial-badge {
            background-color: var(--audit-serial-tag);
            border: 1px solid var(--audit-serial-border);
            color: var(--audit-serial-text);
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

        /* Barcode Stamp Box */
        .barcode-card {
            background: var(--audit-surface-subtle);
            border: 1px dashed var(--audit-border);
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

            .print-audit-wrapper {
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

            .print-audit-wrapper * {
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

            .print-box-card {
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
                    <a href="{{ route('admin.stock.adjustments.index') }}" class="text-decoration-none text-muted">
                        <i class="bi bi-sliders me-1"></i> Stock Adjustments
                    </a>
                </li>
                <li class="breadcrumb-item active fw-semibold text-primary" aria-current="page">
                    {{ $adjustment->reference_number }}
                </li>
            </ol>
        </nav>

        <!-- Page Heading Banner -->
        <div class="page-heading mb-4">
            <div class="page-heading-copy d-flex align-items-center gap-3">
                <div class="page-icon" style="width: 52px; height: 52px; border-radius: 12px; background: rgba(59, 130, 246, 0.12); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 1.6rem;">
                    <i class="bi bi-clipboard2-check" aria-hidden="true"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 0.72rem; letter-spacing: 0.05em; font-weight: 700;">
                            INVENTORY AUDIT RECORD
                        </span>
                        @if($adjustment->type === 'addition')
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                <span class="pulse-dot bg-success"></span> Stock Addition (+)
                            </span>
                        @elseif($adjustment->type === 'subtraction')
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                <span class="pulse-dot pulse-dot-red bg-danger"></span> Stock Subtraction (-)
                            </span>
                        @else
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">
                                <span class="pulse-dot pulse-dot-amber bg-warning"></span> Count Correction (=)
                            </span>
                        @endif
                        <span class="badge bg-secondary-subtle text-body-secondary border px-2 py-1 font-monospace" style="font-size: 0.72rem;">
                            <i class="bi bi-tag-fill me-1 text-muted"></i>{{ ucwords(str_replace('_', ' ', $adjustment->reason)) }}
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <h1 class="h3 mb-0 text-dark fw-bold font-monospace">{{ $adjustment->reference_number }}</h1>
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="copyReference('{{ $adjustment->reference_number }}', this)" title="Copy Reference Number">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                    <p class="text-muted small mb-0 mt-1">
                        <i class="bi bi-clock-history me-1"></i> Recorded on {{ $adjustment->created_at->format('M d, Y \a\t H:i:s') }}
                        <span class="text-secondary">&bull;</span> {{ $adjustment->created_at->diffForHumans() }}
                    </p>
                </div>
            </div>

            <!-- Header Action Buttons -->
            <div class="heading-actions d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-xs" onclick="window.print()">
                    <i class="bi bi-printer"></i>
                    <span>Print Audit Voucher</span>
                </button>
                <a href="{{ route('admin.stock.adjustments.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-xs">
                    <i class="bi bi-plus-circle"></i>
                    <span>New Adjustment</span>
                </a>
                <a href="{{ route('admin.stock.adjustments.index') }}" class="btn btn-outline-secondary btn-sm shadow-xs">
                    &larr; Back to History
                </a>
            </div>
        </div>

        <!-- 4 Key Metric Stat Cards -->
        <div class="row g-3 mb-4">
            <!-- 1. Adjustment Operation -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-metric-card text-primary">
                    <div class="stat-metric-icon bg-primary-subtle text-primary">
                        <i class="bi bi-sliders2-vertical"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.04em;">Operation Type</div>
                        <div class="h5 mb-0 fw-bold text-dark text-truncate">
                            @if($adjustment->type === 'addition')
                                Addition (+)
                            @elseif($adjustment->type === 'subtraction')
                                Subtraction (-)
                            @else
                                Correction (=)
                            @endif
                        </div>
                        <div class="text-muted small font-monospace" style="font-size: 0.75rem;">
                            {{ ucwords(str_replace('_', ' ', $adjustment->reason)) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Target Facility -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-metric-card text-info">
                    <div class="stat-metric-icon bg-info-subtle text-info">
                        <i class="bi bi-building-gear"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.04em;">Target Depot</div>
                        <div class="h5 mb-0 fw-bold text-dark text-truncate">{{ $adjustment->warehouse?->name ?? 'Main Depot' }}</div>
                        <div class="text-muted small font-monospace" style="font-size: 0.75rem;">
                            Code: {{ $adjustment->warehouse?->code ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Net Inventory Impact -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-metric-card {{ $totalNetDelta > 0 ? 'text-success' : ($totalNetDelta < 0 ? 'text-danger' : 'text-secondary') }}">
                    <div class="stat-metric-icon {{ $totalNetDelta > 0 ? 'bg-success-subtle text-success' : ($totalNetDelta < 0 ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary') }}">
                        @if($totalNetDelta > 0)
                            <i class="bi bi-arrow-up-circle-fill"></i>
                        @elseif($totalNetDelta < 0)
                            <i class="bi bi-arrow-down-circle-fill"></i>
                        @else
                            <i class="bi bi-dash-circle-fill"></i>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.04em;">Net Volume Impact</div>
                        <div class="h5 mb-0 fw-bold {{ $totalNetDelta > 0 ? 'text-success' : ($totalNetDelta < 0 ? 'text-danger' : 'text-dark') }}">
                            {{ $totalNetDelta > 0 ? '+'.$totalNetDelta : $totalNetDelta }} units
                        </div>
                        <div class="text-muted small font-monospace" style="font-size: 0.75rem;">
                            Previous: {{ $totalOldQty }} &rarr; New: {{ $totalNewQty }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Components Audited -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-metric-card text-purple" style="color: #8b5cf6;">
                    <div class="stat-metric-icon" style="background: rgba(139, 92, 246, 0.12); color: #8b5cf6;">
                        <i class="bi bi-cpu-fill"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.04em;">Hardware Scope</div>
                        <div class="h5 mb-0 fw-bold text-dark">{{ $totalItems }} Part{{ $totalItems === 1 ? '' : 's' }}</div>
                        <div class="text-muted small font-monospace" style="font-size: 0.75rem;">
                            {{ $totalSerials > 0 ? $totalSerials.' S/N tracked' : 'Quantity verified' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Left: Audit Intelligence & Metadata Panel -->
            <div class="col-12 col-lg-4">
                <div class="audit-card p-3 p-xl-4 mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-primary"><i class="bi bi-info-circle-fill"></i></span>
                            <h6 class="mb-0 fw-bold text-dark">Audit Intelligence</h6>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 10px;">ID #{{ $adjustment->id }}</span>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        <!-- Facility Info Box -->
                        <div class="p-3 rounded border" style="background: var(--audit-surface-subtle);">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-geo-alt-fill text-danger"></i>
                                <span class="small fw-bold text-dark">Audited Facility</span>
                            </div>
                            <div class="fw-bold text-body-emphasis" style="font-size: 0.95rem;">{{ $adjustment->warehouse?->name ?? 'Main Depot' }}</div>
                            <div class="text-muted small font-monospace mb-1">Depot Code: {{ $adjustment->warehouse?->code ?? 'N/A' }}</div>
                            @if($adjustment->warehouse?->address || $adjustment->warehouse?->city)
                                <div class="text-muted small">
                                    <i class="bi bi-pin-map me-1"></i>{{ $adjustment->warehouse?->address }}{{ $adjustment->warehouse?->city ? ', '.$adjustment->warehouse?->city : '' }}
                                </div>
                            @endif
                        </div>

                        <!-- Auditor & Personnel Box -->
                        <div class="p-3 rounded border" style="background: var(--audit-surface-subtle);">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-person-badge-fill text-primary"></i>
                                <span class="small fw-bold text-dark">Staff Auditor</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; font-size: 13px;">
                                    {{ strtoupper(substr($adjustment->user?->name ?? 'A', 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="fw-semibold text-dark text-truncate">{{ $adjustment->user?->name ?? 'Super Administrator' }}</div>
                                    <div class="text-muted small text-truncate" style="font-size: 11px;">{{ $adjustment->user?->email ?? 'admin@system.local' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Audit Parameters & Reason -->
                        <div class="p-3 rounded border" style="background: var(--audit-surface-subtle);">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="small text-muted">Adjustment Reason:</span>
                                <span class="badge bg-light text-dark border font-monospace text-capitalize">{{ str_replace('_', ' ', $adjustment->reason) }}</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="small text-muted">Ledger Impact:</span>
                                <span class="badge {{ $adjustment->type === 'addition' ? 'bg-success-subtle text-success' : ($adjustment->type === 'subtraction' ? 'bg-danger-subtle text-danger' : 'bg-info-subtle text-info') }} border">
                                    {{ ucfirst($adjustment->type) }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="small text-muted">Inventory Ledger:</span>
                                <span class="badge bg-success-subtle text-success border">
                                    <i class="bi bi-check2 me-1"></i>Synchronized
                                </span>
                            </div>
                        </div>

                        <!-- Auditor Remarks / Notes -->
                        <div class="p-3 rounded border" style="background: var(--audit-surface-subtle);">
                            <div class="d-flex align-items-center gap-1 mb-1">
                                <i class="bi bi-chat-left-quote text-secondary"></i>
                                <span class="small fw-bold text-dark">Audit Remarks / Notes</span>
                            </div>
                            @if($adjustment->notes)
                                <p class="small text-body mb-0 fst-italic bg-body p-2 rounded border">
                                    &ldquo;{{ $adjustment->notes }}&rdquo;
                                </p>
                            @else
                                <p class="small text-muted mb-0 fst-italic">
                                    No special audit notes recorded for this operation.
                                </p>
                            @endif
                        </div>

                        <!-- Official Barcode & Security Stamp -->
                        <div class="barcode-card">
                            <div class="simulated-barcode text-dark">
                                <div class="barcode-bar w-2"></div><div class="barcode-bar w-1"></div><div class="barcode-bar w-3"></div><div class="barcode-bar w-1"></div>
                                <div class="barcode-bar w-2"></div><div class="barcode-bar w-1"></div><div class="barcode-bar w-3"></div><div class="barcode-bar w-2"></div>
                                <div class="barcode-bar w-1"></div><div class="barcode-bar w-3"></div><div class="barcode-bar w-2"></div><div class="barcode-bar w-1"></div>
                                <div class="barcode-bar w-3"></div><div class="barcode-bar w-1"></div><div class="barcode-bar w-2"></div><div class="barcode-bar w-1"></div>
                                <div class="barcode-bar w-3"></div><div class="barcode-bar w-2"></div><div class="barcode-bar w-1"></div><div class="barcode-bar w-2"></div>
                            </div>
                            <div class="font-monospace small fw-bold text-dark letter-spacing-1">{{ $adjustment->reference_number }}</div>
                            <div class="text-muted text-uppercase mt-1" style="font-size: 9px; letter-spacing: 0.06em;">
                                Official Inventory Reconciliation Stamp &bull; JackSparrow ERP
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Line Items & Stock Delta Breakdown -->
            <div class="col-12 col-lg-8">
                <div class="audit-card p-3 p-xl-4 mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-primary"><i class="bi bi-boxes"></i></span>
                            <h5 class="mb-0 fw-bold text-dark">Adjusted Components &amp; Stock Movement</h5>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2">
                                {{ $totalItems }} Line Item{{ $totalItems === 1 ? '' : 's' }}
                            </span>
                        </div>
                        <div class="text-muted small font-monospace">
                            Net Delta: 
                            <strong class="{{ $totalNetDelta > 0 ? 'text-success' : ($totalNetDelta < 0 ? 'text-danger' : 'text-dark') }}">
                                {{ $totalNetDelta > 0 ? '+'.$totalNetDelta : $totalNetDelta }} units
                            </strong>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="audit-table-thead">
                                <tr>
                                    <th style="min-width: 240px;">Hardware Part</th>
                                    <th class="text-center" style="width: 120px;">Previous Stock</th>
                                    <th class="text-center" style="width: 140px;">Adjustment Delta</th>
                                    <th class="text-center" style="width: 120px;">New Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($adjustment->items as $item)
                                    @php
                                        $product = $item->product;
                                        $serials = is_array($item->serial_numbers) ? $item->serial_numbers : [];
                                    @endphp
                                    <tr class="audit-row">
                                        <!-- Hardware Part -->
                                        <td>
                                            <div class="d-flex align-items-start gap-3">
                                                <div class="audit-product-avatar">
                                                    @if($product?->category?->slug === 'processors' || str_contains(strtolower($product?->name ?? ''), 'processor') || str_contains(strtolower($product?->name ?? ''), 'ryzen') || str_contains(strtolower($product?->name ?? ''), 'intel'))
                                                        <i class="bi bi-cpu"></i>
                                                    @elseif($product?->category?->slug === 'graphics-cards' || str_contains(strtolower($product?->name ?? ''), 'geforce') || str_contains(strtolower($product?->name ?? ''), 'rtx') || str_contains(strtolower($product?->name ?? ''), 'radeon'))
                                                        <i class="bi bi-gpu-card"></i>
                                                    @elseif($product?->category?->slug === 'memory-ram' || str_contains(strtolower($product?->name ?? ''), 'ddr') || str_contains(strtolower($product?->name ?? ''), 'ram'))
                                                        <i class="bi bi-memory"></i>
                                                    @elseif($product?->category?->slug === 'motherboards' || str_contains(strtolower($product?->name ?? ''), 'motherboard'))
                                                        <i class="bi bi-motherboard"></i>
                                                    @else
                                                        <i class="bi bi-box-seam"></i>
                                                    @endif
                                                </div>
                                                <div class="min-w-0 flex-grow-1">
                                                    @if($product)
                                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="fw-bold text-dark text-decoration-none hover-primary d-block">
                                                            {{ $product->name }}
                                                        </a>
                                                    @else
                                                        <span class="fw-bold text-dark d-block">Component (ID: {{ $item->product_id }})</span>
                                                    @endif
                                                    <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                                        <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 10.5px;">
                                                            SKU: {{ $product?->sku ?? 'N/A' }}
                                                        </span>
                                                        @if($product?->category)
                                                            <span class="badge bg-light text-body border" style="font-size: 10.5px;">
                                                                {{ $product->category->name }}
                                                            </span>
                                                        @endif
                                                        @if($product?->requires_serial_tracking)
                                                            <span class="badge bg-info-subtle text-info border border-info-subtle font-monospace" style="font-size: 10px;">
                                                                <i class="bi bi-upc-scan me-1"></i>S/N Tracked
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <!-- Serial Numbers Drawer if present -->
                                                    @if(!empty($serials))
                                                        <div class="serial-drawer">
                                                            <div class="d-flex align-items-center justify-content-between mb-1 flex-wrap gap-1">
                                                                <span class="small fw-semibold text-dark" style="font-size: 11px;">
                                                                    <i class="bi bi-qr-code me-1 text-primary"></i> Adjusted Serial Numbers ({{ count($serials) }} units):
                                                                </span>
                                                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-primary" style="font-size: 11px;" onclick="copySerials('{{ implode(',', $serials) }}', this)">
                                                                    <i class="bi bi-copy me-1"></i>Copy All Serials
                                                                </button>
                                                            </div>
                                                            <div class="d-flex flex-wrap gap-1">
                                                                @foreach($serials as $sn)
                                                                    <span class="serial-badge" onclick="copyReference('{{ $sn }}', this)" title="Click to copy serial">
                                                                        <i class="bi bi-upc text-muted" style="font-size: 10px;"></i>
                                                                        {{ $sn }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Previous Stock -->
                                        <td class="text-center">
                                            <span class="badge bg-secondary-subtle text-secondary font-monospace px-3 py-2" style="font-size: 13px;">
                                                {{ $item->old_quantity }} units
                                            </span>
                                        </td>

                                        <!-- Adjustment Delta -->
                                        <td class="text-center">
                                            @if($item->adjusted_quantity > 0)
                                                <span class="badge bg-success text-white font-monospace px-3 py-2 shadow-xs" style="font-size: 13px;">
                                                    <i class="bi bi-arrow-up-right me-1"></i>+{{ $item->adjusted_quantity }} units
                                                </span>
                                            @elseif($item->adjusted_quantity < 0)
                                                <span class="badge bg-danger text-white font-monospace px-3 py-2 shadow-xs" style="font-size: 13px;">
                                                    <i class="bi bi-arrow-down-right me-1"></i>{{ $item->adjusted_quantity }} units
                                                </span>
                                            @else
                                                <span class="badge bg-secondary text-white font-monospace px-3 py-2" style="font-size: 13px;">
                                                    <i class="bi bi-dash me-1"></i>0 (No Change)
                                                </span>
                                            @endif
                                        </td>

                                        <!-- New Stock -->
                                        <td class="text-center">
                                            <span class="badge bg-primary text-white font-monospace px-3 py-2 shadow-xs fw-bold" style="font-size: 13.5px;">
                                                {{ $item->new_quantity }} units
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Visual Summary Delta Bar at Bottom -->
                    <div class="mt-4 p-3 rounded border d-flex align-items-center justify-content-between flex-wrap gap-3" style="background: var(--audit-surface-subtle);">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div class="d-flex align-items-center gap-1">
                                <span class="text-muted small">Previous Total:</span>
                                <span class="font-monospace fw-bold text-dark">{{ $totalOldQty }} units</span>
                            </div>
                            <i class="bi bi-arrow-right text-muted"></i>
                            <div class="d-flex align-items-center gap-1">
                                <span class="text-muted small">Net Impact:</span>
                                <span class="font-monospace fw-bold {{ $totalNetDelta > 0 ? 'text-success' : ($totalNetDelta < 0 ? 'text-danger' : 'text-dark') }}">
                                    {{ $totalNetDelta > 0 ? '+'.$totalNetDelta : $totalNetDelta }} units
                                </span>
                            </div>
                            <i class="bi bi-arrow-right text-muted"></i>
                            <div class="d-flex align-items-center gap-1">
                                <span class="text-muted small">New Total:</span>
                                <span class="font-monospace fw-bold text-primary">{{ $totalNewQty }} units</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                                <i class="bi bi-printer me-1"></i> Print Voucher
                            </button>
                            <a href="{{ route('admin.stock.adjustments.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i> New Adjustment
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 2. STRICT 1-PAGE PRINT VOUCHER (Visible ONLY when printing)     -->
    <!-- ============================================================== -->
    <div class="print-audit-wrapper d-none d-print-block">
        <!-- Header: Corporate Header -->
        <div style="border-bottom: 2.5px solid #111827; padding-bottom: 8px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div style="font-size: 17px; font-weight: 900; letter-spacing: 0.04em; text-transform: uppercase;">
                    JACKSPARROW LOGISTICS &bull; INVENTORY AUDIT VOUCHER
                </div>
                <div style="font-size: 11px; color: #4b5563; font-weight: 600; margin-top: 2px;">
                    HARDWARE INVENTORY RECONCILIATION &bull; PHYSICAL STOCK VERIFICATION MANIFEST
                </div>
            </div>
            <div style="text-align: right;">
                <div style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 14px; font-weight: 900;">
                    {{ $adjustment->reference_number }}
                </div>
                <div style="font-size: 10px; color: #4b5563; font-weight: 500;">
                    Date: {{ $adjustment->created_at->format('Y-m-d H:i:s') }}
                </div>
            </div>
        </div>

        <!-- 2-Box Overview Section -->
        <div style="display: flex; gap: 10px; margin-bottom: 12px;">
            <!-- Left: Warehouse & Depot -->
            <div class="print-box-card" style="flex: 1;">
                <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #4b5563; margin-bottom: 4px; border-bottom: 1px solid #d1d5db; padding-bottom: 2px;">
                    1. AUDITED FACILITY / DEPOT
                </div>
                <div style="font-size: 13px; font-weight: 800; margin-bottom: 2px;">
                    {{ $adjustment->warehouse?->name ?? 'Main Warehouse' }}
                </div>
                <div style="font-size: 10.5px; color: #374151;">
                    <strong>Depot Code:</strong> {{ $adjustment->warehouse?->code ?? 'N/A' }}
                </div>
                @if($adjustment->warehouse?->address || $adjustment->warehouse?->city)
                    <div style="font-size: 10px; color: #4b5563;">
                        <strong>Address:</strong> {{ $adjustment->warehouse?->address }}{{ $adjustment->warehouse?->city ? ', '.$adjustment->warehouse?->city : '' }}
                    </div>
                @endif
            </div>

            <!-- Right: Audit Parameters -->
            <div class="print-box-card" style="flex: 1;">
                <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #4b5563; margin-bottom: 4px; border-bottom: 1px solid #d1d5db; padding-bottom: 2px;">
                    2. AUDIT SPECIFICATIONS
                </div>
                <div style="font-size: 11px; margin-bottom: 2px;">
                    <strong>Operation Type:</strong> 
                    <span style="font-weight: 800; text-transform: uppercase;">{{ $adjustment->type }}</span>
                </div>
                <div style="font-size: 11px; margin-bottom: 2px;">
                    <strong>Audit Reason:</strong> {{ ucwords(str_replace('_', ' ', $adjustment->reason)) }}
                </div>
                <div style="font-size: 10.5px; color: #374151;">
                    <strong>Auditing Staff:</strong> {{ $adjustment->user?->name ?? 'Super Administrator' }} ({{ $adjustment->user?->email ?? 'admin' }})
                </div>
            </div>
        </div>

        <!-- Line Items Table -->
        <div style="margin-bottom: 12px;">
            <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">
                3. RECONCILED HARDWARE COMPONENTS ({{ $totalItems }} Item{{ $totalItems === 1 ? '' : 's' }})
            </div>
            <table class="print-table">
                <thead>
                    <tr>
                        <th style="width: 40%;">Hardware Part &amp; SKU</th>
                        <th style="width: 15%; text-align: center;">Category</th>
                        <th style="width: 15%; text-align: center;">Previous Count</th>
                        <th style="width: 15%; text-align: center;">Adjustment Delta</th>
                        <th style="width: 15%; text-align: center;">New Verified Count</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($adjustment->items as $item)
                        @php
                            $prod = $item->product;
                            $serials = is_array($item->serial_numbers) ? $item->serial_numbers : [];
                        @endphp
                        <tr>
                            <td>
                                <div style="font-weight: 800; font-size: 11.5px;">{{ $prod?->name ?? 'Product' }}</div>
                                <div style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 10px; color: #4b5563;">
                                    SKU: {{ $prod?->sku ?? 'N/A' }}
                                </div>
                                @if(!empty($serials))
                                    <div style="margin-top: 4px;">
                                        <div style="font-size: 9.5px; font-weight: 700; color: #1f2937;">SERIAL NUMBERS ({{ count($serials) }}):</div>
                                        <div>
                                            @foreach($serials as $s)
                                                <span class="print-serial-chip">{{ $s }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: center; font-size: 10.5px;">
                                {{ $prod?->category?->name ?? '-' }}
                            </td>
                            <td style="text-align: center; font-family: ui-monospace, monospace; font-weight: 700; font-size: 12px;">
                                {{ $item->old_quantity }}
                            </td>
                            <td style="text-align: center; font-family: ui-monospace, monospace; font-weight: 800; font-size: 12px;">
                                {{ $item->adjusted_quantity > 0 ? '+'.$item->adjusted_quantity : $item->adjusted_quantity }}
                            </td>
                            <td style="text-align: center; font-family: ui-monospace, monospace; font-weight: 800; font-size: 12.5px; background-color: #f3f4f6;">
                                {{ $item->new_quantity }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #f9fafb; font-weight: 800;">
                        <td colspan="2" style="text-align: right; padding: 6px 10px; font-size: 10.5px; text-transform: uppercase;">
                            Total Net Adjustment Impact:
                        </td>
                        <td style="text-align: center; font-family: ui-monospace, monospace; font-size: 11px;">
                            {{ $totalOldQty }}
                        </td>
                        <td style="text-align: center; font-family: ui-monospace, monospace; font-size: 11.5px;">
                            {{ $totalNetDelta > 0 ? '+'.$totalNetDelta : $totalNetDelta }}
                        </td>
                        <td style="text-align: center; font-family: ui-monospace, monospace; font-size: 11.5px; background-color: #e5e7eb;">
                            {{ $totalNewQty }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Notes Section if Present -->
        @if($adjustment->notes)
            <div style="border: 1px dashed #6b7280; padding: 6px 10px; margin-bottom: 12px; background-color: #f9fafb; border-radius: 4px;">
                <span style="font-weight: 800; font-size: 10px; text-transform: uppercase;">Auditor's Memo / Remarks:</span>
                <span style="font-size: 10.5px; font-style: italic;">{{ $adjustment->notes }}</span>
            </div>
        @endif

        <!-- Signatures & Authorization Box -->
        <div class="print-signatures-box" style="margin-top: 8px;">
            <div style="display: flex; justify-content: space-between; gap: 20px;">
                <div style="flex: 1; border-right: 1px dashed #9ca3af; padding-right: 15px;">
                    <div style="font-size: 9.5px; font-weight: 800; text-transform: uppercase; color: #4b5563; margin-bottom: 25px;">
                        Audited &amp; Reconciled By (Staff Auditor):
                    </div>
                    <div style="border-bottom: 1px solid #111827; margin-bottom: 4px;"></div>
                    <div style="display: flex; justify-content: space-between; font-size: 9.5px;">
                        <span>Name: {{ $adjustment->user?->name ?? 'Super Administrator' }}</span>
                        <span>Date: {{ $adjustment->created_at->format('Y-m-d') }}</span>
                    </div>
                </div>

                <div style="flex: 1; padding-left: 5px;">
                    <div style="font-size: 9.5px; font-weight: 800; text-transform: uppercase; color: #4b5563; margin-bottom: 25px;">
                        Verified &amp; Approved By (Warehouse Supervisor):
                    </div>
                    <div style="border-bottom: 1px solid #111827; margin-bottom: 4px;"></div>
                    <div style="display: flex; justify-content: space-between; font-size: 9.5px;">
                        <span>Signature: ______________________</span>
                        <span>Date: ____________</span>
                    </div>
                </div>
            </div>
            <div style="text-align: center; margin-top: 8px; font-size: 8.5px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">
                Official JackSparrow Logistics Document &bull; Digitally Recorded &amp; Committed to Inventory Ledger
            </div>
        </div>
    </div>

    <!-- Interactive Script for Copying & UX -->
    <script>
        function copyReference(text, btnEl) {
            if (!navigator.clipboard) {
                const el = document.createElement('textarea');
                el.value = text;
                document.body.appendChild(el);
                el.select();
                document.execCommand('copy');
                document.body.removeChild(el);
                showCopiedFeedback(btnEl);
                return;
            }

            navigator.clipboard.writeText(text).then(() => {
                showCopiedFeedback(btnEl);
            }).catch(err => {
                console.error('Clipboard copy error:', err);
            });
        }

        function copySerials(csvSerials, btnEl) {
            const serialsList = csvSerials.split(',').map(s => s.trim()).filter(Boolean).join('\n');
            if (navigator.clipboard) {
                navigator.clipboard.writeText(serialsList).then(() => {
                    const origHtml = btnEl.innerHTML;
                    btnEl.innerHTML = '<i class="bi bi-check2 me-1 text-success"></i>All Copied!';
                    setTimeout(() => { btnEl.innerHTML = origHtml; }, 2000);
                });
            }
        }

        function showCopiedFeedback(btnEl) {
            const origHtml = btnEl.innerHTML;
            btnEl.innerHTML = '<i class="bi bi-check2 text-success"></i>';
            setTimeout(() => {
                btnEl.innerHTML = origHtml;
            }, 1800);
        }
    </script>
</x-app-layout>


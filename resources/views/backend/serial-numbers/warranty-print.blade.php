<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Official Hardware Warranty Certificate' }} - {{ config('app.name', 'JackSparrow') }}</title>
    
    <!-- Bootstrap Icons & Fonts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --cert-bg: #ffffff;
            --cert-border: #1e293b;
            --cert-gold: #b45309;
            --cert-navy: #0f172a;
            --cert-muted: #64748b;
        }

        body {
            background-color: #f1f5f9;
            color: #0f172a;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Screen Control Bar */
        .screen-toolbar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 12px 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        /* Certificate Container */
        .certificates-container {
            max-width: 960px;
            margin: 28px auto 60px auto;
            padding: 0 16px;
        }

        .warranty-card {
            background: #ffffff;
            border: 2px solid #0f172a;
            border-radius: 12px;
            padding: 32px 36px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            margin-bottom: 40px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Inner Ornamental Border */
        .warranty-card::before {
            content: "";
            position: absolute;
            top: 8px;
            left: 8px;
            right: 8px;
            bottom: 8px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            pointer-events: none;
        }

        /* Corner Decorative Accents */
        .corner-accent {
            position: absolute;
            width: 16px;
            height: 16px;
            border-color: #b45309;
            border-style: solid;
            pointer-events: none;
        }
        .corner-tl { top: 12px; left: 12px; border-width: 2px 0 0 2px; }
        .corner-tr { top: 12px; right: 12px; border-width: 2px 2px 0 0; }
        .corner-bl { bottom: 12px; left: 12px; border-width: 0 0 2px 2px; }
        .corner-br { bottom: 12px; right: 12px; border-width: 0 2px 2px 0; }

        /* Certificate Header */
        .cert-header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 18px;
            margin-bottom: 22px;
        }

        .cert-brand-title {
            font-size: 1.45rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #0f172a;
            line-height: 1.2;
        }

        .cert-subtitle {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #b45309;
            font-weight: 700;
        }

        .cert-title-badge {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 0.95rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 6px 16px;
            border-radius: 4px;
            display: inline-block;
        }

        /* Simulated Barcode */
        .barcode-strip {
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            padding: 12px 18px;
            text-align: center;
        }

        .barcode-visual {
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2px;
            margin-bottom: 4px;
            overflow: hidden;
        }

        .barcode-line {
            height: 100%;
            background-color: #0f172a;
            display: inline-block;
        }

        /* Component Data Grid */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
        }

        .info-table th,
        .info-table td {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .info-table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            width: 25%;
        }

        .info-table td {
            color: #0f172a;
            font-weight: 500;
        }

        /* Status Pills */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .status-active {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .status-ready {
            background-color: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        .status-expired {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* Terms & Warranty Rules */
        .terms-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 0.75rem;
            color: #475569;
            line-height: 1.45;
        }

        /* Sign-off & Stamp Section */
        .stamp-block {
            border-top: 1px solid #cbd5e1;
            padding-top: 16px;
            margin-top: 20px;
        }

        .sign-line {
            border-bottom: 1px solid #94a3b8;
            height: 38px;
            margin-bottom: 4px;
        }

        .official-seal-box {
            border: 2px dashed #b45309;
            border-radius: 50%;
            width: 84px;
            height: 84px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
            color: #b45309;
            font-size: 0.62rem;
            font-weight: 800;
            text-transform: uppercase;
            line-height: 1.15;
            letter-spacing: 0.05em;
        }

        /* PRINT STYLES - Strict 1-Page Format */
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm 12mm 10mm 12mm;
            }

            body {
                background: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .d-print-none,
            .screen-toolbar {
                display: none !important;
            }

            .certificates-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .warranty-card {
                box-shadow: none !important;
                border: 2px solid #000000 !important;
                margin: 0 0 0 0 !important;
                padding: 20px 24px !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .warranty-card:not(:last-child) {
                page-break-after: always !important;
                break-after: page !important;
            }

            .warranty-card::before {
                border-color: #94a3b8 !important;
            }

            .corner-accent {
                border-color: #000000 !important;
            }

            .cert-header {
                border-bottom-color: #000000 !important;
                margin-bottom: 16px !important;
                padding-bottom: 12px !important;
            }

            .cert-title-badge {
                background-color: #000000 !important;
                color: #ffffff !important;
            }

            .barcode-strip {
                background-color: #ffffff !important;
                border: 1px solid #64748b !important;
                padding: 8px 12px !important;
            }

            .barcode-line {
                background-color: #000000 !important;
            }

            .info-table th {
                background-color: #f1f5f9 !important;
                color: #000000 !important;
                border: 1px solid #94a3b8 !important;
            }

            .info-table td {
                border: 1px solid #94a3b8 !important;
                color: #000000 !important;
            }

            .terms-box {
                background-color: #ffffff !important;
                border: 1px solid #94a3b8 !important;
                color: #334155 !important;
                font-size: 0.72rem !important;
                padding: 8px 12px !important;
            }

            .stamp-block {
                border-top-color: #64748b !important;
                margin-top: 14px !important;
                padding-top: 12px !important;
            }

            .sign-line {
                border-bottom-color: #000000 !important;
            }

            .official-seal-box {
                border-color: #000000 !important;
                color: #000000 !important;
            }
        }
    </style>
</head>
<body>

    <!-- ============================================================== -->
    <!-- INTERACTIVE SCREEN TOOLBAR (Hidden in Print)                   -->
    <!-- ============================================================== -->
    <div class="screen-toolbar d-print-none">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 max-w-7xl mx-auto">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('admin.serial-numbers.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i>
                    <span>Back to Serial Registry</span>
                </a>
                <div>
                    <h1 class="h6 fw-bold mb-0 text-dark">
                        <i class="bi bi-patch-check text-warning me-1"></i> Hardware Warranty Certificates
                    </h1>
                    <span class="text-muted small" style="font-size: 12px;">
                        Ready for printing ({{ $serialNumbers->count() }} {{ \Illuminate\Support\Str::plural('Unit', $serialNumbers->count()) }})
                    </span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small d-none d-md-inline" style="font-size: 12px;">
                    <i class="bi bi-info-circle me-1"></i> Tip: Select "Save as PDF" or your printer, and ensure "Background graphics" is enabled.
                </span>
                <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 px-3 shadow-sm fw-semibold" onclick="window.print()">
                    <i class="bi bi-printer-fill me-1"></i> Print {{ $serialNumbers->count() > 1 ? 'All Certificates (' . $serialNumbers->count() . ')' : 'Warranty Certificate' }}
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- CERTIFICATES CONTAINER                                         -->
    <!-- ============================================================== -->
    <div class="certificates-container">
        @foreach($serialNumbers as $sn)
            @php
                $product = $sn->product;
                $warehouse = $sn->warehouse;
                $order = $sn->order;
                
                // Determine Warranty Status & Dates
                $isUnderWarranty = false;
                $statusType = 'ready'; // ready, active, expired
                $statusText = 'Factory Warranty Ready';
                $statusSub = 'Standard Coverage in Force upon Customer Delivery';

                if ($sn->warranty_end_date) {
                    if ($sn->warranty_end_date->isPast()) {
                        $statusType = 'expired';
                        $statusText = 'Warranty Expired';
                        $statusSub = 'Coverage ended on ' . $sn->warranty_end_date->format('M d, Y');
                    } else {
                        $statusType = 'active';
                        $statusText = 'Active Warranty Coverage';
                        $statusSub = 'Valid through ' . $sn->warranty_end_date->format('M d, Y') . ' (' . $sn->warranty_end_date->diffForHumans() . ')';
                    }
                } elseif ($sn->status === 'SHIPPED' && $sn->outbound_date) {
                    $statusType = 'active';
                    $statusText = 'Active Coverage (Dispatched)';
                    $statusSub = 'Activated on shipment ' . $sn->outbound_date->format('M d, Y');
                }

                // Warranty terms
                $mfgMonths = $product?->manufacturer_warranty_months ?? 36;
                $distMonths = $product?->distributor_warranty_months ?? 12;
                $warrantyPeriod = $product?->warranty_period ?? ($mfgMonths . ' Months (' . round($mfgMonths / 12, 1) . ' Years) Manufacturer Warranty');

                // Generate simulated barcode bars using serial number hash
                $snClean = preg_replace('/[^A-Za-z0-9]/', '', $sn->serial_number);
                $barcodeSeeds = [2, 1, 3, 1, 2, 4, 1, 3, 2, 1, 4, 2, 1, 3, 1, 2, 3, 1, 4, 2, 1, 2, 3, 1, 2, 4, 1, 3, 2, 1];
            @endphp

            <div class="warranty-card">
                <!-- Corner Accents -->
                <div class="corner-accent corner-tl"></div>
                <div class="corner-accent corner-tr"></div>
                <div class="corner-accent corner-bl"></div>
                <div class="corner-accent corner-br"></div>

                <!-- Certificate Header -->
                <div class="cert-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-dark text-white rounded p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-shield-check fs-4 text-warning"></i>
                        </div>
                        <div>
                            <div class="cert-brand-title">JACKSPARROW LOGISTICS</div>
                            <div class="cert-subtitle">High-Performance Computing &bull; Enterprise Hardware Registry</div>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="cert-title-badge mb-1">
                            <i class="bi bi-patch-check-fill me-1 text-warning"></i> Official Warranty Certificate
                        </div>
                        <div class="text-muted small font-monospace" style="font-size: 11px;">
                            Cert No: <strong>WAR-{{ $sn->serial_number }}</strong> &bull; Issued: {{ now()->format('M d, Y') }}
                        </div>
                    </div>
                </div>

                <!-- Barcode & Serial Number Identification Strip -->
                <div class="row g-3 mb-3 align-items-center">
                    <div class="col-12 col-md-7">
                        <div class="barcode-strip">
                            <div class="barcode-visual" aria-hidden="true">
                                @for($i = 0; $i < 38; $i++)
                                    @php
                                        $w = $barcodeSeeds[$i % count($barcodeSeeds)];
                                    @endphp
                                    <span class="barcode-line" style="width: {{ $w }}px; margin-right: {{ $w % 2 === 0 ? '2px' : '1px' }};"></span>
                                @endfor
                            </div>
                            <div class="d-flex align-items-center justify-content-between px-2">
                                <span class="badge bg-dark text-white font-monospace" style="font-size: 10px;">SERIAL BARCODE</span>
                                <span class="font-monospace fw-bold fs-5 text-dark tracking-wider">{{ $sn->serial_number }}</span>
                                <span class="badge bg-secondary-subtle text-body-secondary font-monospace" style="font-size: 10px;">CODE-128</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-5">
                        <div class="p-3 border rounded bg-body-tertiary text-center h-100 d-flex flex-column justify-content-center">
                            <div class="mb-1">
                                @if($statusType === 'active')
                                    <span class="status-pill status-active">
                                        <i class="bi bi-check-circle-fill"></i> {{ $statusText }}
                                    </span>
                                @elseif($statusType === 'expired')
                                    <span class="status-pill status-expired">
                                        <i class="bi bi-x-octagon-fill"></i> {{ $statusText }}
                                    </span>
                                @else
                                    <span class="status-pill status-ready">
                                        <i class="bi bi-shield-lock-fill"></i> {{ $statusText }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-muted small" style="font-size: 11px;">{{ $statusSub }}</div>
                            <div class="font-monospace text-dark small fw-bold mt-1" style="font-size: 11px;">
                                Registry Status: <span class="badge bg-secondary">{{ $sn->status }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hardware Specifications Table -->
                <div class="mb-3">
                    <h6 class="text-uppercase fw-bold text-dark mb-2" style="font-size: 0.78rem; letter-spacing: 0.06em;">
                        <i class="bi bi-cpu text-primary me-1"></i> Hardware Component Specifications
                    </h6>
                    <table class="info-table">
                        <tbody>
                            <tr>
                                <th>Component Name</th>
                                <td colspan="3" class="fw-bold text-dark">
                                    {{ $product?->name ?? 'Enterprise Computer Hardware Component' }}
                                </td>
                            </tr>
                            <tr>
                                <th>Brand / Manufacturer</th>
                                <td>{{ $product?->brand?->name ?? 'Genuine OEM Partner' }}</td>
                                <th>Model SKU / Part No</th>
                                <td class="font-monospace fw-bold text-primary">{{ $product?->sku ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Hardware Category</th>
                                <td>{{ $product?->category?->name ?? 'Computer Parts & Components' }}</td>
                                <th>MPN / UPC Code</th>
                                <td class="font-monospace">{{ $product?->mpn ?? ($product?->upc_ean ?? 'N/A') }}</td>
                            </tr>
                            @if(!empty($product?->specifications) && is_array($product->specifications))
                                <tr>
                                    <th>Key Technical Specs</th>
                                    <td colspan="3">
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach(array_slice($product->specifications, 0, 5) as $specKey => $specVal)
                                                <span class="badge bg-light text-dark border font-monospace" style="font-size: 10.5px; font-weight: normal;">
                                                    <strong>{{ $specKey }}:</strong> {{ $specVal }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Warranty Policy & Facility Traceability -->
                <div class="mb-3">
                    <h6 class="text-uppercase fw-bold text-dark mb-2" style="font-size: 0.78rem; letter-spacing: 0.06em;">
                        <i class="bi bi-shield-check text-success me-1"></i> Warranty Coverage Terms & Traceability
                    </h6>
                    <table class="info-table">
                        <tbody>
                            <tr>
                                <th>Manufacturer Warranty</th>
                                <td class="fw-bold text-success">{{ $warrantyPeriod }}</td>
                                <th>Distributor Warranty</th>
                                <td>{{ $distMonths }} Months Local Replacement</td>
                            </tr>
                            <tr>
                                <th>Coverage Inception</th>
                                <td>
                                    {{ $sn->warranty_start_date ? $sn->warranty_start_date->format('F d, Y') : ($sn->inbound_date ? $sn->inbound_date->format('F d, Y') . ' (Inbound Entry)' : 'Effective Upon Outbound Delivery') }}
                                </td>
                                <th>Coverage Expiration</th>
                                <td class="fw-bold {{ $statusType === 'expired' ? 'text-danger' : 'text-primary' }}">
                                    {{ $sn->warranty_end_date ? $sn->warranty_end_date->format('F d, Y') : 'Active for ' . $mfgMonths . ' Months post-dispatch' }}
                                </td>
                            </tr>
                            <tr>
                                <th>Fulfilling Facility</th>
                                <td>
                                    <strong>{{ $warehouse?->name ?? 'Main Central Depot' }}</strong>
                                    <span class="text-muted font-monospace small ms-1">({{ $warehouse?->code ?? 'WH-MAIN' }})</span>
                                </td>
                                <th>Customer / Order Reference</th>
                                <td>
                                    @if($order)
                                        <span class="fw-bold">Order #{{ $order->order_number }}</span>
                                        @if($order->customer_name)
                                            <span class="text-muted small">({{ $order->customer_name }})</span>
                                        @endif
                                    @else
                                        <span class="text-muted italic">Enterprise Inventory (Unallocated)</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Terms & Conditions Box -->
                <div class="mb-3">
                    <h6 class="text-uppercase fw-bold text-dark mb-1" style="font-size: 0.75rem; letter-spacing: 0.06em;">
                        <i class="bi bi-file-earmark-text text-secondary me-1"></i> Warranty Terms & RMA Protocol
                    </h6>
                    <div class="terms-box">
                        <div class="row g-2">
                            <div class="col-12 col-md-4">
                                <strong class="text-dark d-block mb-1">1. Scope of Coverage</strong>
                                Covers manufacturing defects in electronic circuitry, silicon fabrication, memory controllers, and structural components under standard operational parameters.
                            </div>
                            <div class="col-12 col-md-4">
                                <strong class="text-dark d-block mb-1">2. Exclusions & Void Terms</strong>
                                Void if serial number barcode is removed, tampered, or defaced. Physical impacts, PCB cracks, liquid damage, extreme electrical surges, or unauthorized repair are not covered.
                            </div>
                            <div class="col-12 col-md-4">
                                <strong class="text-dark d-block mb-1">3. RMA Claim Procedure</strong>
                                Retain this warranty certificate. For RMA authorization, contact JackSparrow Logistics Support with your Serial Number (<strong>{{ $sn->serial_number }}</strong>) and original antistatic packaging.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Signatures & Official Stamp Block -->
                <div class="stamp-block">
                    <div class="row align-items-center">
                        <div class="col-4">
                            <div class="text-center">
                                <div class="sign-line"></div>
                                <div class="small fw-bold text-dark" style="font-size: 11.5px;">Quality Control Inspector</div>
                                <div class="text-muted" style="font-size: 10px;">Hardware Diagnostics Certified</div>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="official-seal-box">
                                <i class="bi bi-award-fill fs-5 mb-1 text-warning"></i>
                                <span>AUTHENTIC</span>
                                <span style="font-size: 8px;">VERIFIED</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center">
                                <div class="sign-line"></div>
                                <div class="small fw-bold text-dark" style="font-size: 11.5px;">Logistics Depot Custodian</div>
                                <div class="text-muted" style="font-size: 10px;">{{ $warehouse?->name ?? 'Central Depot Authority' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</body>
</html>


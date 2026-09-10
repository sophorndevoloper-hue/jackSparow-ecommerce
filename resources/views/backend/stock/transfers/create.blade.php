<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Logistics &amp; Movement</p>
                    <h1 class="h3 mb-1">New Stock Transfer</h1>
                    <p class="text-muted mb-0">Dispatch and shift hardware components from one warehouse facility to another.</p>
                </div>
            </div>
            <div class="heading-actions">
                <a href="{{ route('admin.stock.transfers.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Back to Transfers
                </a>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger mt-3">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.stock.transfers.store') }}" method="POST" class="mt-3">
            @csrf

            <div class="row g-3">
                <!-- Left: Route Details -->
                <div class="col-12 col-lg-4">
                    <div class="panel p-4 mb-3">
                        <h5 class="mb-3 text-dark fw-bold">Transfer Route</h5>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Source Warehouse (From) *</label>
                            <select name="from_warehouse_id" id="fromWarehouse" required class="form-select">
                                <option value="">-- Select Source Facility --</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ (string)$selectedFromId === (string)$wh->id ? 'selected' : '' }}>
                                        {{ $wh->name }} ({{ $wh->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Destination Warehouse (To) *</label>
                            <select name="to_warehouse_id" id="toWarehouse" required class="form-select">
                                <option value="">-- Select Destination Facility --</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ old('to_warehouse_id') == $wh->id ? 'selected' : '' }}>
                                        {{ $wh->name }} ({{ $wh->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Transfer Status *</label>
                            <select name="status" required class="form-select">
                                <option value="completed" {{ old('status', 'completed') === 'completed' ? 'selected' : '' }}>
                                    Completed (Shift stock immediately)
                                </option>
                                <option value="in_transit" {{ old('status') === 'in_transit' ? 'selected' : '' }}>
                                    In Transit (Deduct from source, pending arrival)
                                </option>
                                <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>
                                    Pending (Draft / Not yet dispatched)
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Transfer Date</label>
                            <input type="date" name="transfer_date" value="{{ old('transfer_date', date('Y-m-d')) }}" class="form-control">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small">Transfer Notes / Waybill #</label>
                            <textarea name="notes" rows="3" class="form-control" placeholder="Optional courier waybill or notes...">{{ old('notes') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                            <i class="bi bi-arrow-right-circle me-1"></i> Process Stock Transfer
                        </button>
                    </div>
                </div>

                <!-- Right: Product Items -->
                <div class="col-12 col-lg-8">
                    <div class="panel p-4 mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                            <div>
                                <h5 class="mb-0 text-dark fw-bold">Components to Transfer</h5>
                                <small class="text-muted">Select parts and specify the transfer quantities.</small>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addTransferProductRow()">
                                <i class="bi bi-plus-lg me-1"></i> Add Part
                            </button>
                        </div>

                        <div id="transferRowsContainer">
                            <!-- First Row -->
                            <div class="row g-2 align-items-end mb-3 transfer-row border p-2 rounded bg-light-subtle">
                                <div class="col-7">
                                    <label class="form-label small fw-bold text-dark">Hardware Part *</label>
                                    <select name="products[0][product_id]" class="form-select form-select-sm" required>
                                        <option value="">-- Select Product --</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }} (SKU: {{ $p->sku }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-4">
                                    <label class="form-label small fw-bold text-dark">Quantity (units) *</label>
                                    <input type="number" name="products[0][quantity]" value="1" min="1" required class="form-control form-control-sm font-monospace text-center">
                                </div>
                                <div class="col-1 text-end">
                                    <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="this.closest('.transfer-row').remove()" title="Remove">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-outline-secondary btn-sm w-100 mt-2 py-2" onclick="addTransferProductRow()">
                            <i class="bi bi-plus-circle me-1"></i> Add Another Component
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        let transferIndex = 1;
        const productsList = @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'sku' => $p->sku]));

        function addTransferProductRow() {
            const container = document.getElementById('transferRowsContainer');
            const row = document.createElement('div');
            row.className = 'row g-2 align-items-end mb-3 transfer-row border p-2 rounded bg-light-subtle';
            
            let options = '<option value="">-- Select Product --</option>';
            productsList.forEach(p => {
                options += `<option value="${p.id}">${p.name} (SKU: ${p.sku})</option>`;
            });

            row.innerHTML = `
                <div class="col-7">
                    <label class="form-label small fw-bold text-dark">Hardware Part *</label>
                    <select name="products[${transferIndex}][product_id]" class="form-select form-select-sm" required>
                        ${options}
                    </select>
                </div>
                <div class="col-4">
                    <label class="form-label small fw-bold text-dark">Quantity (units) *</label>
                    <input type="number" name="products[${transferIndex}][quantity]" value="1" min="1" required class="form-control form-control-sm font-monospace text-center">
                </div>
                <div class="col-1 text-end">
                    <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="this.closest('.transfer-row').remove()" title="Remove">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
            container.appendChild(row);
            transferIndex++;
        }
    </script>
</x-app-layout>


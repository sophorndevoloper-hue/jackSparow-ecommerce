<?php

namespace App\DataTables;

use App\Models\SerialNumber;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SerialNumberDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<SerialNumber>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('serial_number', function (SerialNumber $sn) {
                return '<span class="font-monospace fw-bold text-primary">'.e($sn->serial_number).'</span>';
            })
            ->addColumn('product', function (SerialNumber $sn) {
                return $sn->product ? '<div><span class="fw-bold text-body-emphasis">'.e($sn->product->name).'</span><div class="text-muted small font-monospace">SKU: '.e($sn->product->sku).'</div></div>' : '<span class="text-muted">-</span>';
            })
            ->addColumn('warehouse', fn (SerialNumber $sn) => $sn->warehouse ? e($sn->warehouse->name) : '<span class="text-muted">-</span>')
            ->editColumn('status', function (SerialNumber $sn) {
                $cls = match ($sn->status) {
                    SerialNumber::STATUS_IN_STOCK => 'bg-success-subtle text-success border border-success-subtle',
                    SerialNumber::STATUS_ALLOCATED => 'bg-info-subtle text-info border border-info-subtle',
                    SerialNumber::STATUS_SHIPPED => 'bg-primary-subtle text-primary border border-primary-subtle',
                    SerialNumber::STATUS_RETURNED_RMA => 'bg-warning-subtle text-warning border border-warning-subtle',
                    SerialNumber::STATUS_DEFECTIVE_SCRAP => 'bg-danger-subtle text-danger border border-danger-subtle',
                    default => 'bg-secondary',
                };

                return '<span class="badge '.$cls.'">'.e(str_replace('_', ' ', $sn->status)).'</span>';
            })
            ->editColumn('warranty_end_date', fn (SerialNumber $sn) => $sn->warranty_end_date ? $sn->warranty_end_date->format('M d, Y') : '<span class="text-muted">-</span>')
            ->addColumn('action', function (SerialNumber $sn) {
                $editUrl = route('admin.serial-numbers.edit', $sn->id);

                return '
                    <div class="btn-group btn-group-sm">
                        <a href="'.$editUrl.'" class="btn btn-outline-secondary" title="Edit Serial">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </div>
                ';
            })
            ->editColumn('updated_at', fn (SerialNumber $s) => $s->updated_at?->format('Y-m-d H:i') ?? '-')
            ->rawColumns(['serial_number', 'product', 'warehouse', 'status', 'warranty_end_date', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<SerialNumber>
     */
    public function query(SerialNumber $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['product', 'warehouse', 'supplier'])->latest('updated_at');

        if (request()->filled('product_id')) {
            $query->where('product_id', request()->input('product_id'));
        }

        if (request()->filled('warehouse_id')) {
            $query->where('warehouse_id', request()->input('warehouse_id'));
        }

        if (request()->filled('status')) {
            $query->where('status', request()->input('status'));
        }

        return $query;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('serialnumber-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(6, 'desc')
            ->parameters([
                'responsive' => true,
                'autoWidth' => false,
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('serial_number')->title('Serial Number'),
            Column::make('product')->title('Hardware Product'),
            Column::make('warehouse')->title('Warehouse'),
            Column::make('status')->title('Status'),
            Column::make('inbound_date')->title('Inbound Date')->attributes(['data-default-sort' => 'desc']),
            Column::make('warranty_end_date')->title('Warranty Expiry'),
            Column::computed('action')->title('Manage')->addClass('text-end no-sort'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'SerialNumber_'.date('YmdHis');
    }
}

<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UserDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<User>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', function (User $u) {
                return '
                    <div class="d-flex align-items-center gap-2">
                        <img src="'.e($u->avatar_url).'" alt="'.e($u->name).'" class="rounded-circle object-fit-cover" style="width: 36px; height: 36px;">
                        <div>
                            <span class="fw-bold text-body-emphasis">'.e($u->name).'</span>
                            <div class="text-muted small">'.e($u->email).'</div>
                        </div>
                    </div>
                ';
            })
            ->addColumn('roles', function (User $u) {
                $badges = $u->roles->map(fn ($r) => '<span class="badge bg-primary-subtle text-primary border border-primary-subtle me-1">'.e(ucfirst($r->name)).'</span>')->implode('');

                return $badges ?: '<span class="text-muted small">No roles</span>';
            })
            ->editColumn('is_approved', fn (User $u) => $u->is_approved ? '<span class="badge bg-success-subtle text-success border border-success-subtle">Approved</span>' : '<span class="badge bg-warning-subtle text-warning border border-warning-subtle">Pending Approval</span>')
            ->editColumn('created_at', fn (User $u) => $u->created_at ? $u->created_at->format('M d, Y') : '-')
            ->addColumn('action', function (User $u) {
                $editUrl = route('admin.settings.users.edit', $u->id);
                $deleteUrl = route('admin.settings.users.destroy', $u->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                return '
                    <div class="btn-group btn-group-sm">
                        <a href="'.$editUrl.'" class="btn btn-outline-secondary" title="Edit Staff User">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="'.$deleteUrl.'" method="POST" class="d-inline" onsubmit="return confirm(\'Delete this user account?\');">
                            '.$csrf.'
                            '.$method.'
                            <button type="submit" class="btn btn-outline-danger" title="Delete User">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                ';
            })
            ->editColumn('updated_at', fn (User $u) => $u->updated_at?->format('Y-m-d H:i') ?? '-')
            ->rawColumns(['name', 'roles', 'is_approved', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<User>
     */
    public function query(User $model): QueryBuilder
    {
        return $model->newQuery()->with(['roles', 'profile'])->latest('updated_at');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('user-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(5, 'desc')
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
            Column::make('name')->title('User Profile'),
            Column::make('email')->title('Email Address'),
            Column::make('is_approved')->title('Approval Status'),
            Column::make('roles')->title('Assigned Roles')->orderable(false),
            Column::make('permissions')->title('Direct Permissions')->orderable(false),
            Column::make('orders_count')->title('Orders Placed'),
            Column::computed('action')->title('Actions')->addClass('text-end no-sort'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'User_'.date('YmdHis');
    }
}

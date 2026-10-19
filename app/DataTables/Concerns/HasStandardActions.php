<?php

namespace App\DataTables\Concerns;

trait HasStandardActions
{
    /**
     * Render standard Edit and Delete action buttons for Yajra DataTable rows.
     */
    public function renderActionButtons(
        string $editUrl,
        string $deleteUrl,
        string $resourceTitle = 'record',
        ?string $confirmMessage = null
    ): string {
        $confirm = e($confirmMessage ?? "Delete this {$resourceTitle}?");
        $csrf = csrf_field();
        $method = method_field('DELETE');

        return '
            <div class="btn-group btn-group-sm">
                <a href="'.$editUrl.'" class="btn btn-outline-secondary" title="Edit '.e($resourceTitle).'">
                    <i class="bi bi-pencil"></i>
                </a>
                <form action="'.$deleteUrl.'" method="POST" class="d-inline" onsubmit="return confirm(\''.$confirm.'\');">
                    '.$csrf.'
                    '.$method.'
                    <button type="submit" class="btn btn-outline-danger" title="Delete '.e($resourceTitle).'">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        ';
    }
}

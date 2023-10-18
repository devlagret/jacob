<?php

namespace App\DataTables;

use App\Models\SystemUserGroup;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class SystemUserGroupDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', 'content.SystemUserGroup.List._action-menu');
    }

    public function query(SystemUserGroup $model)
    {
        return $model->newQuery()
        ->where('data_state', 0)
        ->where('user_group_status', 0);
    }

    public function html()
    {
        return $this->builder()
                    ->setTableId('user-group-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->stateSave(true)
                    ->orderBy(0, 'asc')
                    ->responsive()
                    ->autoWidth(true)
                    ->parameters(['scrollX' => true])
                    ->addTableClass('align-middle table-row-dashed fs-6 gy-5');
    }

    protected function getColumns()
    {
        return [
            Column::make('user_group_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('user_group_name')->title(__('Nama')),
            Column::make('user_group_level')->title(__('Level')),
            Column::make('system_user_group.user_group_name')->title(__('Jabatan'))->data('user_group_name'),
            Column::computed('action')
                    ->title(__('Aksi'))
                    ->exportable(false)
                    ->printable(false)
                    ->width(300)
                    ->addClass('text-center'),
        ];
    }

    protected function filename()
    {
        return 'UserGroup_' . date('YmdHis');
    }
}

<?php

namespace App\DataTables;

use App\Models\User;
use App\Models\SystemUserGroup;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class SystemUserDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            // ->editColumn('user_group_id', function (User $model) {
            //     $usergroup = SystemUserGroup::select('user_group_name')
            //     ->where('user_group_id', $model->user_group_id)
            //     ->first();

            //     return $usergroup['user_group_name'];
            // })
            ->addIndexColumn()
            ->addColumn('action', 'content.SystemUser.List._action-menu');
    }

    public function query(User $model)
    {
        return $model->newQuery()
        ->join('system_user_group', 'system_user_group.user_group_id', 'system_user.user_group_id')
        ->where('system_user.data_state', 0)
        ->where('user_level',0);
    }

    public function html()
    {
        return $this->builder()
                    ->setTableId('users-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->stateSave(true)
                    // ->dom('lfrtip')
                    // ->dom('frtip')
                    ->orderBy(0, 'asc')
                    ->responsive()
                    ->autoWidth(true)
                    ->parameters(['scrollX' => true])
                    ->addTableClass('align-middle table-row-dashed fs-6 gy-5');
    }

    protected function getColumns()
    {
        return [
            Column::make('user_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('username')->title(__('Username')),
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
        return 'Users_' . date('YmdHis');
    }
}

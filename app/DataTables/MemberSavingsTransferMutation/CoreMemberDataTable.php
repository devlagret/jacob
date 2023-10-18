<?php

namespace App\DataTables\MemberSavingsTransferMutation;

use App\Models\CoreMember;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class CoreMemberDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', 'content.MemberSavingsTransferMutation.Add.CoreMemberModal._action-menu');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\MemberSavingsTransferMutation/CoreMemberDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(CoreMember $model)
    {
        return $model->newQuery()
        ->select('member_no','member_name','member_address','member_id')
        ->where('data_state', 0);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('membersavingstransfermutation-corememberdatatable-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->stateSave(true)
                    ->orderBy(0, 'asc')
                    ->responsive()
                    ->autoWidth(true)
                    ->parameters(['scrollX' => true])
                    ->addTableClass('align-middle table-row-dashed fs-6 gy-5');
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            Column::make('member_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('member_no')->title(__('No. Anggota')),
            Column::make('member_name')->title(__('Nama Anggota')),
            Column::make('member_address')->title(__('Alamat')),
            Column::computed('action') 
                    ->title(__('Aksi'))
                    ->exportable(false)
                    ->printable(false)
                    ->width(150)
                    ->addClass('text-center'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'MemberSavingsTransferMutation/CoreMember_' . date('YmdHis');
    }
}

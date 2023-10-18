<?php

namespace App\DataTables;

use App\Models\CoreBranch;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class CoreBranchDataTable extends DataTable
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
            ->addColumn('action', 'content.CoreBranch.List._action-menu');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\CoreBranchDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(CoreBranch $model)
    {
        return $model->newQuery()
        ->select('branch_id', 'branch_code', 'branch_name', 'branch_city', 'branch_address', 'branch_manager','branch_contact_person', 'branch_email', 'branch_phone1')
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
                    ->setTableId('branch-table')
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
            Column::make('branch_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('branch_code')->title(__('Kode')),
            Column::make('branch_name')->title(__('Nama')),
            Column::make('branch_city')->title(__('Kota')),
            Column::make('branch_address')->title(__('Alamat')),
            Column::make('branch_manager')->title(__('Kepala Manager')),
            Column::make('branch_contact_person')->title(__('Orang yang dapat dihubungi')),
            Column::make('branch_email')->title(__('Email')),
            Column::make('branch_phone1')->title(__('No. Telp')),
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
        return 'CoreBranch_' . date('YmdHis');
    }
}

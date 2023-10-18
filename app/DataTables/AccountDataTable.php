<?php

namespace App\DataTables;

use App\Models\AcctAccount;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use App\Helpers\Configuration;

class AccountDataTable extends DataTable
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
            ->editColumn('account_type_id', function (AcctAccount $model) {

                return Configuration::KelompokPerkiraan()[$model->account_type_id];

            })
            ->editColumn('account_status', function (AcctAccount $model) {

                return Configuration::AccountStatus()[$model->account_status];

            })
            ->addIndexColumn()
            ->addColumn('action', 'content.Account.List._action-menu');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\AccountDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AcctAccount $model)
    {
        return $model->newQuery()
        ->select('account_id', 'account_type_id', 'account_code', 'account_name', 'account_group', 'account_status')
        ->where('data_state', 0)
        ->orderBy('account_code', 'ASC');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('accountdatatable-table')
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
            Column::make('account_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('account_code')->title(__('No. Perkiraan')),
            Column::make('account_name')->title(__('Nama Perkiraan')),
            Column::make('account_group')->title(__('Golongan Perkiraan')),
            Column::make('account_type_id')->title(__('Kelompok Perkiraan')),
            Column::make('account_status')->title(__('D/K')),
            Column::computed('action')
                    ->title(__('Aksi'))
                    ->exportable(false)
                    ->printable(false)
                    ->width(300)
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
        return 'Account_' . date('YmdHis');
    }
}

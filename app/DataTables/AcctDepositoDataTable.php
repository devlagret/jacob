<?php

namespace App\DataTables;

use App\Models\AcctDeposito;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Facades\DB;

class AcctDepositoDataTable extends DataTable
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
            ->addColumn('action', 'content.AcctDeposito.List._action-menu');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\AcctDepositoDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AcctDeposito $model)
    {
        return $model->newQuery()
        ->join('acct_account as first_account','first_account.account_id','=','acct_deposito.account_id')
        ->join('acct_account as end_account','end_account.account_id','=','acct_deposito.account_basil_id')
        ->select(DB::raw("CONCAT(first_account.account_code,' - ',first_account.account_name) AS full_account"), 
        DB::raw("CONCAT(end_account.account_code,' - ',end_account.account_name) AS full_basil_account"),'acct_deposito.deposito_id', 'acct_deposito.deposito_code', 'acct_deposito.deposito_name', 'acct_deposito.deposito_period', 'acct_deposito.deposito_interest_rate','first_account.account_name','first_account.account_code', 'end_account.account_name','end_account.account_code')
        ->where('acct_deposito.data_state', 0);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('deposito-table')
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
            Column::make('deposito_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('deposito_code')->title(__('Kode Simpanan Berjangka')),
            Column::make('deposito_name')->title(__('Nama')),
            Column::make('first_account.account_name','first_account.account_code')->title(__('No. Perkiraan'))->data('full_account'),
            Column::make('end_account.account_name','end_account.account_code')->title(__('Bunga'))->data('full_basil_account'),
            Column::make('deposito_period')->title(__('Jangka Waktu')),
            Column::make('deposito_interest_rate')->title(__('Bunga/th')),
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
        return 'AcctDeposito_' . date('YmdHis');
    }
}
